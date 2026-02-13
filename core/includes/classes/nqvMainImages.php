<?php

class nqvMainImages {
    protected const DEFAULT_THUMBNAIL_SIZES = [
        ['size' => [90, 90], 'crop' => null],
        ['size' => [320, 128], 'crop' => ['center', 'center']],
        ['size' => [320, 256], 'crop' => ['center', 'center']]
    ];

    protected nqvDbTable $table;
    protected array $fields;
    protected array $thumbnailsSizes;

    protected ?int $id = null;
    protected ?string $name = null;
    protected ?string $slug = null;
    protected string $tablename = 'mainimages';
    protected ?int $element_id = null;
    protected ?string $created_at = null;
    protected ?int $created_by = null;
    protected ?string $modified_at = null;
    protected ?string $error = null;

    protected const VARIANT_SIZES = [
        'banner' => [
            'size' => [3040, 1020],
            'mode' => 'cover',   // rellena todo el canvas, recortando si hace falta
        ],
        'square' => [
            'size' => [512, 512],
            'mode' => 'cover', // mantiene proporción, no amplía, puede dejar espacio vacío
        ],
    ];


    public function getVariantPath(string $variant): string {

        $base = $this->getBaseFilepath();
        $info = pathinfo($base);

        return $info['dirname'] . '/' . $info['filename'] . '-' . $variant . '.' . $info['extension'];
    }

    public function createVariants(): int {
        $count = 0;
        $ext = $this->get_extension(true);

        foreach (self::VARIANT_SIZES as $variant => $cfg) {
            $variantPath = dirname($this->getBaseFilepath()) . '/' .
                pathinfo($this->getBaseFilepath(), PATHINFO_FILENAME) . '-' . $variant . '.' . $ext;

            if (nqvImageProcessor::createVariant(
                $this->getBaseFilepath(),
                $variantPath,
                $cfg['size'],
                $cfg['mode'],
                $ext
            )) {
                $count++;
            }
        }

        return $count;
    }


    public function __construct($input=[]) {
        $this->table = new nqvDbTable($this->tablename);
        $this->fields = $this->table->getTableFields();
        $this->thumbnailsSizes = self::DEFAULT_THUMBNAIL_SIZES;

        if (is_string($input)) {
            $input = ['name' => $input];
        }
        
        $this->parseData($input);
    }

    public static function getFields(): array {
        try {
            $dbFields = [];
            $table = new nqvDbTable('mainimages');
            $fields = $table->getTableFields();
            foreach($fields as $field) {
                $dbFields[$field['Field']] = new nqvDbField($field,'mainimages');
            }
            return $dbFields;
        } catch (Exception $e) {
            return [];
        }
    }

    protected function parseData(array $input): self {
        foreach ($input as $k => $v) {
            if ($k === 'slug') {
                $this->set_slug();
            } elseif (property_exists($this, $k)) {
                $this->$k = $v;
            }
        }
        return $this;
    }

    public function get(string $k): mixed {
        return property_exists($this, $k) ? $this->$k : null;
    }

    public function set(string $k, mixed $v): self {
        if (property_exists($this, $k)) {
            $this->$k = $v;
        }
        return $this;
    }

    public function exists(): bool {
        return !empty($this->id);
    }

    public function save(?array $data = []): int|bool {
        $this->slug = createSlug(pathinfo($this->get('name'),PATHINFO_FILENAME),'-','mainimages',$this->slug);
        $slug = $this->get_slug();

        $types = 's';
        $vars = [$slug];
        $fields = ['slug = ?'];

        foreach ($this->fields as $k => $fieldInfo) {
            if (array_key_exists($k, $data)) $this->$k = $data[$k];
            if (in_array($k, ['id', 'slug'])) continue;

            if ($k === 'created_at' && empty($this->$k)) {
                $this->$k = date('Y-m-d H:i:s');
            } elseif ($k === 'created_by' && empty($this->$k)) {
                $this->$k = nqv::getCurrentUserId();
            } elseif ($k === 'name') {
                $this->$k = pathinfo($this->$k, PATHINFO_BASENAME);
            }

            $fields[] = "$k = ?";
            $field = new nqvDbField($fieldInfo, $this->tablename);
            $types .= $field->getTypeLetter();
            $vars[] = $this->$k;
        }


        $sql = ($this->exists() ? 'UPDATE' : 'INSERT INTO') . ' `mainimages` SET ' . implode(',', $fields);
        if ($this->exists()) {
            $sql .= ' WHERE id = ?';
            $types .= 'i';
            $vars[] = $this->id;
        }

        $stmt = nqvDB::prepare($sql);
        $stmt->bind_param($types, ...$vars);
        $stmt->execute();

        if (!$this->exists()) {
            $this->id = $stmt->insert_id;
            return $this->id;
        }

        return empty($stmt->error);
    }

    public function get_extension(bool $strtolower = true): ?string {
        $ext = pathinfo($this->get('name'), PATHINFO_EXTENSION);
        return $strtolower ? strtolower($ext) : $ext;
    }

    public function getBaseFilepath(): string {
        $this->set_slug();
        return sprintf(
            '%simages/%s/%d/%s.%s',
            UPLOADS_PATH,
            $this->tablename,
            $this->element_id,
            $this->slug,
            $this->get_extension()
        );
    }

    public function set_slug(): self {
        $this->slug = createImageSlug($this->get('name'));
        return $this;
    }

    public function get_slug(): ?string {
        return $this->slug;
    }

    public function getThumbnailSrc(?array $size, ?array $crop) {
        return $this->getSrc($this->getThumbnailPath($size, $crop));
    }

    public function getThumbnailPath(?array $size, ?array $crop): string {
        return self::getThumbnailFromPath($this->getBaseFilepath(), $size, $crop);
    }

    public function getThumbnailsPaths(): array {
        $paths = [];
        foreach ($this->thumbnailsSizes as $thSize) {
            $paths[] = $this->getThumbnailPath($thSize['size'], $thSize['crop']);
        }
        return $paths;
    }

    public function getSrc(?string $sufix = null, ?string $path = null, string $glue = '-'): ?string {
        $path = is_null($path) ? $this->getBaseFilepath():$path;
        $info = pathinfo($path);
        if($sufix) $path = $info['dirname'].'/'.$info['filename'].$glue.$sufix.'.'.$info['extension'];
        if(!is_file($path)) return null;
        return url($path) . '?v=' . filemtime($path);
    }

    public static function getImageById(int $id): ?self {
        $stmt = nqvDB::prepare('SELECT * FROM `mainimages` WHERE `id` = ? LIMIT 1');
        $stmt->bind_param('i', $id);
        $result = nqvDB::parseSelect($stmt);
        return !empty($result) ? new self((array) $result[0]) : null;
    }

    public static function getByElementId(string $tablename, int $id): ?self {
        $stmt = nqvDB::prepare('SELECT * FROM `mainimages` WHERE `element_id` = ? AND `tablename` = ? LIMIT 1');
        $stmt->bind_param('is', $id, $tablename);
        $result = nqvDB::parseSelect($stmt);
        return !empty($result) ? new self((array) $result[0]) : null;
    }

    public static function getThumbnailFromPath(string $filepath, ?array $size, ?array $crop): string {
        $size = $size ?? [90, 90];
        $pathinfo = pathinfo($filepath);
        $suffix = implode('x', $size) . (is_null($crop) ? '' : '_' . implode('-', $crop));
        return $pathinfo['dirname'] . '/' . $pathinfo['filename'] . '_' . $suffix . '.' . $pathinfo['extension'];
    }

    public function delete(): bool {
        $filepath = $this->getBaseFilepath();
        if (is_file($filepath)) unlink($filepath);

        $stmt = nqvDB::prepare('DELETE FROM `mainimages` WHERE id = ?');
        $stmt->bind_param('i', $this->id);
        $stmt->execute();
        $this->error = $stmt->error;
        return empty($this->error);
    }

    public function getData(): array {
        return array_diff_key(get_object_vars($this), array_flip(['fields', 'table']));
    }

    public function hasJpegExtension(): bool {
        return $this->get_extension(true) === 'jpeg';
    }

    public function fixJpegExtension(): ?int {
        if (!$this->hasJpegExtension()) return null;

        $pathinfo = pathinfo($this->getBaseFilepath());
        $newpath = $pathinfo['dirname'] . '/' . $pathinfo['filename'] . '.jpg';

        foreach ($this->getThumbnailsPaths() as $thumb) {
            if (is_file($thumb)) unlink($thumb);
        }

        rename($this->getBaseFilepath(), $newpath);
        $this->set('filepath', $newpath);
        $this->save();
        return $this->createThumbnails();
    }

    public function createThumbnails(): int {
        $count = 0;
        foreach ($this->thumbnailsSizes as $thSize) {
            if ($this->createThumbnail($thSize['size'], $thSize['crop'])) $count++;
        }
        return $count;
    }

    protected function createThumbnail(?array $size, ?array $crop): bool {
        try {
            $ext = $this->get_extension(true);
            if ($ext === 'jpg') {
                return nqvImageProcessor::createThumbnailJpg($this->getBaseFilepath(), $this->getThumbnailPath($size, $crop), $size, $crop);
            } elseif ($ext === 'png') {
                return nqvImageProcessor::createThumbnailPng($this->getBaseFilepath(), $this->getThumbnailPath($size, $crop), $size, $crop);
            }
        } catch (Exception $e) {
            _log('Error: ' . $e->getMessage(), 'thumbnail-error');
        }
        return false;
    }

    public function getSize() {
        $filepath = $this->getBaseFilepath();
        if(!is_file($filepath)) return null;
        return getimagesize($filepath);
    }

    public function isFile() {
        return is_file($this->getBaseFilepath());
    }

    public function upload(string $tmp_name, ?string $fileToDelete = null, bool $crop = true): bool {
        $filepath = $this->getBaseFilepath();
        $dirname = dirname($filepath);

        if (!is_dir($dirname)) mkdir($dirname, 0775, true);

        if (!move_uploaded_file($tmp_name, $filepath)) {
            _log("No se pudo subir $tmp_name como $filepath", 'main-image-upload-error');
            return false;
        }

        if ($fileToDelete) {
            $this->deleteOldFile($fileToDelete);
        }

        if (is_file($filepath)) {
            $size = getimagesize($filepath);
            if ($size[0] / $size[1] !== 1.0 && $crop) {
                $this->crop();
            }
            $this->createVariants();
        }

        return true;
    }

    protected function deleteOldFile(string $fileToDelete): void {
        $fpathToDelete = $fileToDelete;
        if (is_file($fpathToDelete)) unlink($fpathToDelete);

        foreach ($this->thumbnailsSizes as $thSize) {
            $thumb = self::getThumbnailFromPath($fileToDelete, $thSize['size'], $thSize['crop']);
            if (is_file($thumb)) unlink($thumb);
        }
    }

    protected function crop(): void {
        $ext = $this->get_extension(true);
        if ($ext === 'jpg') nqvImageProcessor::cropJPG($this->getBaseFilepath());
        elseif ($ext === 'png') nqvImageProcessor::cropPNG($this->getBaseFilepath());
    }

}
