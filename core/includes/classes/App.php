<?php
namespace OVO\Core;

use OVO\Http\Request;
use OVO\Http\Response;

class App
{
    public static function handle(Request $request): Response
    {
        // --- Legacy routing ---
        [$mm, $template] = array_values(\nqv::parseMainHttoQuery());

        // --- Layout state ---
        $mainClass = ['px-4'];

        if (hasHeader()) {
            $mainClass[] = 'headered';
        }

        if (hasFooter()) {
            $mainClass[] = 'footered';
        }

        // --- Compartimos variables al render ---
        $data = [
            'mm'         => $mm,
            'template'   => $template,
            'mainClass'  => $mainClass,
        ];

        return new Response($data);
    }
}
