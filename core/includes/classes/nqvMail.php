<?php

use PHPMailer\PHPMailer\PHPMailer;

class nqvMail {
    private $manager;
    private $ErrorInfo;
    private $options = [];
    private $isGmail = false;
    private $defaults = [
        'from' => ['address'=> ADMIN_EMAIL, 'name' => APP_NAME],
        'reply' => ['address'=> ADMIN_EMAIL_SENDER, 'name' => 'No responder'],
        'isHtml' => true,
        // 'gmailAccess' => ['username'=>'info@resonar.ar', 'appPassword'=>'ylqqnlckwsybvota'],
        'template' => '',
        'subject' => 'Mail de ' . APP_NAME,
        'template_mode' => 'relative'
    ];
    private $templatePath;

    public function __construct($options = []){
        $this->options = array_merge($this->defaults, $options);
        $this->manager = new PHPMailer(true);
        $this->manager->IsSMTP();
        $this->manager->CharSet = "UTF-8"; 
        $this->manager->Encoding = 'base64';
        try {
            $this->parse_options();
        } catch(Exception $e) {
            throw new Exception($e->getMessage(), $e->getCode());
        }
    }

    public function get_options() {
        return $this->options;
    }

    public function parse_options(){
        try {
            if(empty($this->options['fronturl'])) $this->options['fronturl'] = url(ROOT_PATH);
            if($this->options['template_mode'] === 'absolute') $this->templatePath = $this->options['template'];
            else $this->templatePath = TEMPLATES_PATH .'mails' . DIRECTORY_SEPARATOR . $this->options['template'] . '.php';
            if(!is_file($this->templatePath)) {
                $msg = !DEBUG ? 'No se pudo crear la cuenta.':'La plantilla '.$this->templatePath.' no existe.';
                throw new Exception($msg);
            }
            if(empty($this->options['to'])) throw new Exception('El destinatario no es válido.');
            $this->manager->isHTML($this->options['isHtml']); 
            ob_start();
            $ops = $this->options;
            include($this->templatePath);
            //Configuracion servidor mail
            if($this->isGmail) {
                $this->manager->SMTPAuth = true;
                $this->manager->SMTPSecure = 'tls'; //seguridad
                $this->manager->Host = "smtp.gmail.com"; // servidor smtp
                $this->manager->Port = 587; //puerto
                $this->manager->Username = $this->options['gmailAccess']['username']; //nombre usuario
                $this->manager->Password = $this->options['gmailAccess']['appPassword']; //contraseña
            } elseif(isDev()) {
                $this->manager->isSMTP();
                $this->manager->Host = 'localhost';
                $this->manager->Port = 1025;
                $this->manager->SMTPAuth = false;
            } else {
                $this->manager->isSMTP();
                $this->manager->Host = 'mail.resonar.ar'; // o 127.0.0.1
                $this->manager->Port = 587;          // o el puerto que use tu servidor local (puede ser 587 o 465 si es con TLS/SSL)
                $this->manager->SMTPAuth = true;   // Desactiva autenticación si no es requerida
                $this->manager->Username = ADMIN_EMAIL_SENDER;
                $this->manager->Password = ADMIN_EMAIL_SENDER_PASSWORD;
                $this->manager->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            }
            $this->manager->AddReplyTo($this->options['reply']['address'], $this->options['reply']['name']);
            foreach($this->options['to'] as $mail) $this->manager->addAddress($mail['address'], $mail['name']);
            $this->manager->SetFrom($this->options['from']['address'], $this->options['from']['name']);
            $this->manager->Subject = !empty($subject) ? $subject:$this->options['subject'];
            if(!empty($this->options['AltBody'])) $this->manager->AltBody = $this->options['AltBody'];
            elseif(!empty($altBody)) $this->manager->AltBody = $altBody;
            $m = ob_get_clean();
            if($this->options['isHtml']) $this->manager->MsgHTML($m);
            if(!empty($this->options['attachments'])) {
                foreach($this->options['attachments'] as $attachment) $this->manager->AddAttachment($attachment);// attachment PATH
            }
            return true;
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
            return false;
        }
    }

    public function setFrom(string $address, string $name) {
        return $this->manager->setFrom($address, $name);
    }

    public function addAddress(string $address, string $name) {
        return $this->manager->addAddress($address, $name);
    }

    public function addReplyTo(string $address, string $name) {
        return $this->manager->addReplyTo($address, $name);
    }

    public function send(){
        try {
            return $this->manager->send();
        } catch (Exception $e) {
            $this->ErrorInfo = $e->getMessage();
            _log($this->ErrorInfo,'mail-error');
            throw new Exception($e->getMessage());
            return false;
        }
    }

    public function getError() {
        return $this->ErrorInfo;
    }

    public function test() {
        ob_start();
        try {
            if(is_file($this->templatePath)) include($this->templatePath);
            else throw new Exception($this->templatePath . ' no existe.');
        } catch(Exception $e) {
            throw new Exception($e->getMessage(),$e->getCode());
        }
        return ob_get_clean();
    }
}