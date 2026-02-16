<?php
namespace OVO\Core;

use OVO\Http\Request;
use OVO\Http\Response;

class App {
    public static function handle(Request $request): Response {
        // --- Legacy routing ---
        [$mm, $template] = array_values(self::parseMainHttoQuery());
        $mainClass = ['ovo'];
        // --- Layout state ---
        if (hasHeader()) $mainClass[] = 'headered';
        if (hasFooter()) $mainClass[] = 'footered';

        // --- Compartimos variables al render ---
        $data = [
            'mm'         => $mm,
            'template'   => $template,
            'mainClass'  => $mainClass,
        ];

        return new Response($data);
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
                        $test = \nqv::translate((string) $template,'es','slug',true);
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
