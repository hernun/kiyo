<?php

class nqvApi {
    public static function listGoogleFonts(?bool $force = false): ?string {
        $cache = TMP_PATH.'google-fonts.json';
        $ttl = 86400 * 7; // 7 días

        if(!$force && file_exists($cache) && time() - filemtime($cache) < $ttl) return file_get_contents($cache);

        $apiKey = $_ENV['GOOGLE_FONTS_KEY'];

        if(!empty($apiKey)) {
            $url = "https://www.googleapis.com/webfonts/v1/webfonts?sort=popularity&key={$apiKey}";
            $json = file_get_contents($url);

            if(!$json) throw new \Exception('Error obteniendo Google Fonts');

            // minificar
            $minified = json_encode(json_decode($json), JSON_UNESCAPED_UNICODE);

            file_put_contents($cache, $minified);

            return $minified;
        }

        return null;
    }
}