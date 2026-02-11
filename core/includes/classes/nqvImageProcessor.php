<?php

class nqvImageProcessor {
    public static function cropPNG(string $filepath, int $size = 500): bool {
        [$width, $height] = getimagesize($filepath);
        $src = imagecreatefrompng($filepath);

        $cropX = max(0, ($width - min($width, $height)) / 2);
        $cropY = max(0, ($height - min($width, $height)) / 2);
        $cropSize = min($width, $height);

        $cropped = imagecrop($src, [
            'x' => $cropX,
            'y' => $cropY,
            'width' => $cropSize,
            'height' => $cropSize
        ]);

        $resized = imagescale($cropped, $size, $size);
        imagealphablending($resized, false);
        imagesavealpha($resized, true);
        imagepng($resized, $filepath);

        unset($src);
        unset($cropped);
        unset($resized);

        return true;
    }

    public static function cropJPG(string $filepath, int $size = 500): bool {
        [$width, $height] = getimagesize($filepath);
        $src = imagecreatefromjpeg($filepath);

        $cropX = max(0, ($width - min($width, $height)) / 2);
        $cropY = max(0, ($height - min($width, $height)) / 2);
        $cropSize = min($width, $height);

        $cropped = imagecrop($src, [
            'x' => $cropX,
            'y' => $cropY,
            'width' => $cropSize,
            'height' => $cropSize
        ]);

        $resized = imagescale($cropped, $size, $size);
        imagejpeg($resized, $filepath);

        unset($src);
        unset($cropped);
        unset($resized);

        return true;
    }

    public static function createThumbnailJpg(string $srcPath, string $destPath, ?array $size, ?array $crop): bool {
        return self::createThumbnailGeneric('jpg', $srcPath, $destPath, $size, $crop);
    }

    public static function createThumbnailPng(string $srcPath, string $destPath, ?array $size, ?array $crop): bool {
        return self::createThumbnailGeneric('png', $srcPath, $destPath, $size, $crop);
    }

    protected static function createThumbnailGeneric(
    string $type,
    string $srcPath,
    string $destPath,
    ?array $size,
    ?array $crop
    ): bool {
        if (empty($size)) $size = [90, 90];

        // Si el primer elemento es null o no está definido, usamos valor por defecto
        $size[0] = $size[0] ?? 90;

        $createFn = $type === 'png' ? 'imagecreatefrompng' : 'imagecreatefromjpeg';
        $saveFn   = $type === 'png' ? 'imagepng' : 'imagejpeg';
        $quality  = $type === 'png' ? null : 90;

        // Creamos temporalmente la imagen para obtener dimensiones
        $srcTemp = $createFn($srcPath);
        $srcW = imagesx($srcTemp);
        $srcH = imagesy($srcTemp);

        // Calculamos tamaño proporcional si $size tiene solo un valor
        if (count($size) === 1) {
            $targetW = $size[0];
            $targetH = (int)($srcH * ($targetW / $srcW));
        } else {
            $targetW = $size[0] ?? 90;
            $targetH = $size[1] ?? 90;
        }

        unset($srcTemp); // Liberamos recurso temporal

        // Creamos imagen destino
        $src = $createFn($srcPath);
        $dest = imagecreatetruecolor($targetW, $targetH);

        if ($type === 'png') {
            imagealphablending($dest, false);
            imagesavealpha($dest, true);
            $transparent = imagecolorallocatealpha($dest, 0, 0, 0, 127);
            imagefill($dest, 0, 0, $transparent);
        }

        if (is_null($crop)) {
            imagecopyresampled($dest, $src, 0, 0, 0, 0, $targetW, $targetH, $srcW, $srcH);
        } else {
            $srcRatio = $srcW / $srcH;
            $destRatio = $targetW / $targetH;

            if ($srcRatio > $destRatio) {
                $cropW = (int)($srcH * $destRatio);
                $cropH = $srcH;
                $srcX = self::getCropCoordinate($crop[0] ?? 'center', $srcW, $cropW);
                $srcY = 0;
            } else {
                $cropW = $srcW;
                $cropH = (int)($srcW / $destRatio);
                $srcX = 0;
                $srcY = self::getCropCoordinate($crop[1] ?? 'center', $srcH, $cropH);
            }

            imagecopyresampled(
                $dest,
                $src,
                0, 0,
                $srcX, $srcY,
                $targetW, $targetH,
                $cropW, $cropH
            );
        }

        $saveFn($dest, $destPath, $quality);

        unset($src, $dest); // liberamos recursos de imagen

        return true;
    }

    protected static function getCropCoordinate(string $alignment, int $srcSize, int $cropSize): int {
        return match ($alignment) {
            'left', 'top' => 0,
            'right', 'bottom' => $srcSize - $cropSize,
            default => (int)(($srcSize - $cropSize) / 2),
        };
    }
}
