<?php
    class nqvUsers extends nqvDbObject {
        protected ?string $name = '';
        protected ?string  $lastname = '';
        protected ?string  $password = '';
        protected ?string  $email = '';
        protected ?int $session_types_id;
        protected ?object  $session_type;
        protected ?string  $status;
        protected ?string  $token;
        protected ?string  $home_widgets;
        protected ?string  $database_popup_items;
        protected ?string $created_at;
        protected int $created_by;
        
        protected static string $tablename = 'users';
        protected static $main_field = 'email';

        public static function getForcedValue(string $name, $val) {
            $values = null;
            if ($name === 'session_types_id') {
                $ws = nqv::get('session_type');
                $values = parent::parseCrossSelectValues($ws, 'session_type');
            }
            return $values;
        }
        
        public function isLogged(): bool {
            $sessUid = nqv::getSession()->getUserId();
            return !empty($sessUid) && $this->id === $sessUid;
        }
        
        private function setPasswordValue($pass): string {
            return set_password_value($pass);
        }
        
        public function set_password(string $pass): void {
            $this->password = $pass;
        }
        
        public function save(?array $data = []): int {
            if(!is_encrypted($this->password)) $this->set_password($this->setPasswordValue($this->password));
            if($this->email !== $this->data['email']) {
                $ref = new nqvUsers(['email' => $this->email]);
                if($ref->exists()) return nqvNotifications::add('El mail <strong>' . $this->email . '</strong> ya está en uso', 'error');
            }
            if(!is_null($data)) $this->parseData($data);
            return parent::save();
        }

        public function savePassword($password) {
            $this->set_password($password);
            return $this->save();
        }
        
        public function check_password($pass) {
            return check_pass($pass, $this->getData('password'));
        }
        
        public function exists(): bool {
            return !empty($this->name) && !empty($this->lastname) && !empty($this->email) && !empty($this->password);
        }
        
        public function get_name() {
            return $this->name;
        }

        public function get_fullname() {
            return $this->name . ' ' . $this->lastname;
        }

        public function get_email() {
            return $this->email;
        }
        
        public function getSessionType(): nqvSession_types {
            if (empty($this->session_type)) {
                $this->session_type = new nqvSession_types(['id' => $this->session_types_id]);
            }
            return $this->session_type;
        }
        
        public function getType($k = null) {
            $type = $this->getSessionType();
            if (empty($k)) return $type;
            else {
                return $type->getData($k);
            }
        }
        
        public function getPasswordTypeInput(string $name, string $label): nqvDbField {
            $atts = [
                'Field' => $name,
                'Default' => '',
                'Null' => 'NO',
                'Type' => 'varchar(119)'
            ];
            $rp = new nqvDbField($atts, 'users');
            $rp->setLabel($label);
            $rp->setHtmlInputType('password');
            return $rp;
        }
        
        public function checkFields(array $input) {
            $fs = parent::getTableFieldsObjects();
            foreach ($fs as $k => $v) {
                if (!$this->id && $k === 'id') continue;
                if ($this->id && $k === 'password') continue;
                $v->setValue(@$input[$k]);
                if (!$v->checkValue()) return false;
            }
            return true;
        }

        public static function getLabel($k){
            if($k === 'session_types_id') return nqv::translate('type');
            else return nqv::translate($k);
        }

        public function delete(): bool {
            nqv::cleanView();
            $c = self::get_called_class();
            try {
                nqvDB::delete($this->tbl_name, $this->get_id());
                $output = true;
            } catch (Exception $e) {
                nqvNotifications::add($c.': '.$e->getMessage(),'error');
                $output = false;
            }
            return $output;
        }

        public function sendMail(string $template, string $subject, array $ops) {
            $options = array_merge([
                'template' => $template,
                'to' => [['address' => $this->email,'name' => $this->get_fullname()]],
                'fronturl' => URL,
                'subject' => $subject
            ],$ops);
            try {
                if(!$this->exists()) throw new Exception('El usuario ' . $this->email . ' no existe.',403);
                $mail = new nqvMail($options);
                return $mail->send();
            } catch(Exception $e) {
                throw new Exception($e->getMessage(),$e->getCode());
                return null;
            }
        }

        public static function getNewToken() {
            return bin2hex(random_bytes(32));
        }

        public function sendResetPasswordConfirmMail() {
            try {
                if(!$this->save(['token' => self::getNewToken()])) return false;
                return $this->sendMail('password-reset-confirm','Confirmar solicitud en ' . DOMAIN,['token' => $this->token]);
            } catch(Exception $e) {
                throw $e;
            }
        }
    }
