<?php
    
    class  nqvSession {
        protected static string $tablename = 'session';
        protected static $instance = null;
        
        protected $data;
        protected $world;
        protected $token = '';
        protected $users_id;
        protected $types_id;
        protected int $id;
        protected $device = 'unknown';
        protected $ip = '000.000.000';
        protected $init_on;
        
        protected function __construct() {
            $this->open();
        }

        public static function getInstance() {
            if(empty(self::$instance)) self::$instance = new nqvSession();
            return self::$instance;
        }
        
        public function open() {
            try {
                if(session_status() !== PHP_SESSION_ACTIVE) session_start();
            } catch(Exception $e) {
                throw new Exception($e->getMessage());
            }
        }

        public function isActive() {
            return session_status() === PHP_SESSION_ACTIVE;
        }

        public function create($user) {
            try {
                $this->open();
                $_SESSION['user'] = $user;
                $_SESSION['auth'] = 1;
                //$this->token = $_SESSION['sessToken'];
            } catch(Exception $e) {
                throw new Exception($e->getMessage());
            }
        }

        public function getUser(): ?nqvUsers {
            try {
                $user = new nqvUsers(['id' => $_SESSION['user']['id']]);
                return $user;
            } catch(Exception $e) {
                $this->destroy($e->getMessage());
                return null;
            }
        }
        
        public function getUserId(): ? Int {
            if(empty($_SESSION['user'])) return null;
            $user = is_array($_SESSION['user']) ? new nqvUsers($_SESSION['user']):$_SESSION['user'];
            return !empty($user) ? intval($user->get('id')):0;
        }
        
        public function getCookie($k = 'nqv') {
            return (@$_COOKIE[$k]);
        }

        public function resetLogin() {
            $_SESSION['login'] = ['error' => 0, 'msg' => ''];
        }
        
        public function destroy($msg = null, ?string $redirection='/'): void {
            session_unset();
            session_destroy();
            session_write_close();
            if($msg) nqvNotifications::add($msg,'error');
            else nqv::redirect($redirection);
        }

        public function isAuth() {
            return !empty($_SESSION['auth']);
        }
        
        public function getSessVar($k) {
            return @$_SESSION[$k];
        }
        
        public function getSessVars($index = null) {
            if (!is_null($index)) return @$_SESSION[$index];
            else return $_SESSION;
        }
        
        public function setSessVar($k, $v): void {
            $_SESSION[$k] = $v;
        }
        
        public function cleanSessVar($k): void {
            unset($_SESSION[$k]);
        }
        
        public function getAndCleanSessVar($k) {
            $o = $this->getSessVar($k);
            $this->cleanSessVar($k);
            return $o;
        }

        public static function getType(): int {
            return intval(@$_SESSION['type']);
        }

        public function setType(int $id): nqvSession {
            $_SESSION['type'] = $id;
            return $this;
        }
    }