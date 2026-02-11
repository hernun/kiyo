<?php
    
    class nqvNotifications {
        protected static $html = [];
        protected static $htmlString = '';
        
        public static function flush() {
            if (empty($_SESSION['notifications'])) return '';
            self::parse();
            $html = self::getHtml();
            echo self::getHtmlString();
            self::clean();
            return $html;
        }
        
        protected static function parse() {
            if (empty($_SESSION['notifications'])) return '';
            foreach ($_SESSION['notifications'] as $n) {
                if($n['type'] === 'error') {
                    $type = 'alert-danger';
                    $icon = '<svg class="bi me-2" viewBox="0 0 16 16" xmlns="http://www.w3.org/2000/svg"><path d="M8.982 1.566a1.13 1.13 0 0 0-1.96 0L.165 13.233c-.457.778.091 1.767.98 1.767h13.713c.889 0 1.438-.99.98-1.767L8.982 1.566zM8 5c.535 0 .954.462.9.995l-.35 3.507a.552.552 0 0 1-1.1 0L7.1 5.995A.905.905 0 0 1 8 5zm.002 6a1 1 0 1 1 0 2 1 1 0 0 1 0-2z"/></svg>';
                }
                elseif($n['type'] === 'success') {
                    $type = 'alert-success';
                    $icon = '<svg class="bi me-2" viewBox="0 0 16 16" xmlns="http://www.w3.org/2000/svg"><path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0m-3.97-3.03a.75.75 0 0 0-1.08.022L7.477 9.417 5.384 7.323a.75.75 0 0 0-1.06 1.06L6.97 11.03a.75.75 0 0 0 1.079-.02l3.992-4.99a.75.75 0 0 0-.01-1.05z"/></svg>';
                }
                elseif($n['type'] === 'warning') {
                    $type = 'alert-warning';
                    $icon = '<svg class="bi me-2" viewBox="0 0 16 16" xmlns="http://www.w3.org/2000/svg"><path d="M8.982 1.566a1.13 1.13 0 0 0-1.96 0L.165 13.233c-.457.778.091 1.767.98 1.767h13.713c.889 0 1.438-.99.98-1.767L8.982 1.566zM8 5c.535 0 .954.462.9.995l-.35 3.507a.552.552 0 0 1-1.1 0L7.1 5.995A.905.905 0 0 1 8 5zm.002 6a1 1 0 1 1 0 2 1 1 0 0 1 0-2z"/></svg>';
                }
                else $type = 'alert-primary';
                self::$html[] = '<div class="alert ' . $type . ' d-flex align-items-center" role="alert">' . $icon .'<div>' . $n['text'] . '</div></div>';
            }
            self::$htmlString = '<div class="notifications-container">' . implode('', self::$html) . '</div>';
        }
        
        public static function getHtml(): array {
            return self::$html;
        }
        
        public static function getHtmlString(): string {
            return self::$htmlString;
        }
        
        public static function clean() {
            self::$html = [];
            self::$htmlString = '';
            $_SESSION['notifications'] = [];
        }
        
        public static function add($text, $type): bool {
            if (is_array($text)) array_walk_recursive($text, function (&$v) {
                if (is_string($v)) $v = chr(9) . htmlentities($v);
            });
            elseif (is_string($text)) $text = htmlentities($text);
            
            $ref = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2);
            $logtext = [$text, $ref[1]];
            // _log($logtext, 'notifications-' . $type,3);
            if (is_array($text)) $text = implode('<br />' , $text);
            if(session_status() !== PHP_SESSION_ACTIVE) session_start();
            $_SESSION['notifications'][] = ['text' => $text, 'type' => $type];
            return ($type !== 'error');
        }
        
        public static function print($text, $type): void {
            self::add($text, $type);
            self::flush();
        }
    }