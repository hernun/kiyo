<?php
namespace OVO\Core;

use OVO\Http\Request;
use OVO\Http\Response;

class App {
    public static function handle(Request $request): Response {
        self::cors();

        // --- Legacy routing ---
        [$mm, $template] = array_values(self::parseMainHttoQuery());
        $mainClass = ['ovo'];
        $page = null;
        $alternates = [];

        // --- Layout state ---
        if (hasHeader()) $mainClass[] = 'headered';
        if (hasFooter()) $mainClass[] = 'footered';

        if ($template === 'page') {
            $slug = \nqv::getVars(0);
            $pages = \nqv::get('pages', [
                'slug' => $slug,
                'lang' => $_SESSION['CURRENT_LANGUAGE']
            ]);

            $page = $pages[0] ?? null;
        }

        if($page) {
            $alternates = \nqv::get('pages',['slug' => $page['slug'],'lang%not' => $_SESSION['CURRENT_LANGUAGE']]);
        }

        // --- Compartimos variables al render ---
        $data = [
            'mm'         => $mm,
            'template'   => $template,
            'mainClass'  => $mainClass,
            'page'  => $page ?? null,
            'alternates' => $alternates
        ];

        return new Response($data);
    }

    protected static function cors() {
        $allowedOrigins = [
            'https://kiyo.ar'
        ];

        $origin = $_SERVER['HTTP_ORIGIN'] ?? null;

        if ($origin) {
            header('Vary: Origin');

            $allowed = in_array($origin, $allowedOrigins, true);

            // permitir *.nqv y *.nqv.ar
            if (!$allowed && preg_match('#^https://[a-z0-9\-]+\.(nqv|nqv\.ar)$#i', $origin)) {
                $allowed = true;
            }

            if (!$allowed) {
                http_response_code(403);
                exit('Origin not allowed');
            }

            header("Access-Control-Allow-Origin: $origin");
        }

        header('Access-Control-Allow-Methods: GET, POST, PUT, PATCH, DELETE, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With, Accept, Origin');
        header('Access-Control-Max-Age: 86400');

        if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
            http_response_code(204);
            exit;
        }
    }

    public static function parseMainHttoQuery() {
        #_log([@$_SERVER['REQUEST_URI'],@$_SERVER['HTTP_REFERER'],@$_SERVER['REMOTE_ADDR']]);
        $mm = \nqv::getConfig('maintenance-mode');
        if(!user_is_logged() && isAdmin()) {
            if(\nqv::getVars(0) === 'password-reset') $template = 'password-reset';
            else $template = 'login';
        } else {
            if($mm && !user_is_logged()) {
                if(is_template_enabled_on_maintenance(\nqv::getVars(0))) $template = \nqv::getVars(0);
                else $template = \nqv::getConfig('maintenance-template');
            } else {
                $template = \nqv::getVars(0);
                if(!empty($template)) {
                    if(!isTemplate($template)) {
                        $test = \nqv::translate((string) $template,'ES','slug',true);
                        if(isAdmin()) {
                            if(\nqvDB::isTable($test)) {
                                \nqv::addVar('database',0);
                                $template = 'database';
                            }
                        } elseif(isFront()) {
                            if(isCategory($template)) {
                                header('location:/category/' . $template);
                                exit;
                            }
                            if(isPage($template)) {
                                $template = 'page';
                            }
                        }
                    } elseif($template === 'images') {
                        include_template($template);
                        exit;
                    }
                }
            }
            if(empty($template)) $template = 'home';
        }
        \nqv::setReferer($template);
        return [
            'maintenanceMode' => $mm,
            'template' => $template
        ];
    }
}
