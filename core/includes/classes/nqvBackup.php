<?php

class nqvBackup {

    public const CATEGORY_DATABASE = 'database';
    public const CATEGORY_BACKUP = 'backup';
    public const CATEGORY_CODE = 'code';

    public static function exportUploads(): string {

        if(!is_dir(UPLOADS_PATH)) throw new Exception('El directorio de uploads no existe.');

        $filename = self::buildFilename(self::CATEGORY_BACKUP, null, 'zip');
        $zipPath = BKP_PATH . $filename;

        $zip = new ZipArchive();

        if($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true)
            throw new Exception('No se pudo crear el archivo ZIP.');

        $files = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator(UPLOADS_PATH, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::SELF_FIRST
        );

        foreach($files as $file) {

            $filePath = $file->getRealPath();
            $relativePath = substr($filePath, strlen(UPLOADS_PATH));

            if($file->isDir())
                $zip->addEmptyDir($relativePath);
            else
                $zip->addFile($filePath, $relativePath);
        }

        $zip->close();
        return $zipPath;
    }

    public static function importUploads(string $zipPath, string $mode = 'replace'): void {

        if(!file_exists($zipPath))
            throw new Exception('El archivo ZIP no existe.');

        if(!in_array($mode,['replace','append']))
            throw new Exception('Modo inválido.');

        $zip = new ZipArchive();

        if($zip->open($zipPath) !== true)
            throw new Exception('No se pudo abrir el ZIP.');

        if($mode === 'replace')
            self::clearUploadsDirectory();

        $zip->extractTo(UPLOADS_PATH);

        $zip->close();
    }

    private static function clearUploadsDirectory(): void {

        if(!is_dir(UPLOADS_PATH)) return;

        $files = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator(UPLOADS_PATH, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::CHILD_FIRST
        );

        foreach($files as $file) {

            if($file->isDir())
                rmdir($file->getRealPath());
            else
                unlink($file->getRealPath());
        }
    }


    public static function exportFullBackup( array $excludeTables = [], string $type = 'full'): string {
        $dbFile = nqvDB::exportDatabase($excludeTables,$type);
        if (!$dbFile || !file_exists($dbFile)) throw new Exception('Error generando el dump de base de datos.');

        $filename = self::buildFilename(self::CATEGORY_BACKUP, $type, 'zip');
        $zipPath = BKP_PATH . $filename;

        $zip = new ZipArchive();
        if($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) throw new Exception('No se pudo crear el archivo ZIP.');

        $zip->addFile($dbFile,'database.sql');

        $files = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator(UPLOADS_PATH, RecursiveDirectoryIterator::SKIP_DOTS)
        );

        foreach($files as $file) {

            if(!$file->isFile()) continue;

            $filePath = $file->getRealPath();
            $relative = 'uploads/' . substr($filePath, strlen(UPLOADS_PATH));

            $zip->addFile($filePath,$relative);
        }

        $zip->close();

        unlink($dbFile);

        return $zipPath;
    }

    public static function importFullBackup(string $zipPath, string $mode = 'replace'): void {

        if(!file_exists($zipPath))
            throw new Exception('El archivo no existe.');

        $tmpDir = sys_get_temp_dir() . '/ovo_import_' . uniqid();
        if(!mkdir($tmpDir, 0755, true)) throw new Exception('No se pudo crear directorio temporal.');

        $zip = new ZipArchive();

        if($zip->open($zipPath) !== true)
            throw new Exception('No se pudo abrir el ZIP.');

        $zip->extractTo($tmpDir);
        $zip->close();

        $dbFile = $tmpDir . '/database.sql';

        if(file_exists($dbFile))
            nqvDB::importDatabase($dbFile,$mode);
                
        if(is_dir($tmpDir . '/uploads')) self::importUploadsFromDirectory($tmpDir . '/uploads',$mode);


        self::deleteDirectory($tmpDir);
    }

    private static function importUploadsFromDirectory(string $sourceDir, string $mode): void {

        if($mode === 'replace')
            self::clearUploadsDirectory();

        $files = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($sourceDir, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::SELF_FIRST
        );

        foreach($files as $file) {

            $targetPath = UPLOADS_PATH . substr($file->getRealPath(), strlen($sourceDir) + 1);

            if($file->isDir()) {

                if(!is_dir($targetPath)) mkdir($targetPath,0755,true);

            } else {

                copy($file->getRealPath(), $targetPath);
            }
        }
    }


    private static function deleteDirectory(string $dir): void {

        if(!is_dir($dir)) return;

        $files = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::CHILD_FIRST
        );

        foreach($files as $file) {

            if($file->isDir())
                rmdir($file->getRealPath());
            else
                unlink($file->getRealPath());
        }

        rmdir($dir);
    }

    public static function buildFilename(string $category, ?string $type = null, string $extension = 'zip'): string {
        $project = defined('APP_NAME') ? APP_NAME : 'ovo';
        $timestamp = date('Ymd_His');

        $parts = [$project, $category];

        if($type !== null && $type !== '') $parts[] = $type;
        $parts[] = $timestamp;

        return implode('-', $parts) . '.' . $extension;
    }

    public static function getBackups(string $category): array {
        $files = [];
        if(!is_dir(BKP_PATH)) return $files;

        $project = defined('APP_NAME') ? APP_NAME : 'ovo';

        foreach(scandir(BKP_PATH) as $file) {

            if(!is_file(BKP_PATH . $file)) continue;
            $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));

            if(!in_array($ext, ['sql','zip'])) continue;

            $parts = explode('-', pathinfo($file, PATHINFO_FILENAME));
            if(count($parts) < 3) continue;
            if($parts[0] !== $project) continue;
            if($parts[1] === $category) $files[] = $file;
        }

        sort($files);

        return $files;
    }
}