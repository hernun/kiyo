<?php

use PHPMailer\PHPMailer\PHPMailer;

class nqvMail {
    protected array $config;
    private $manager;
    private $ErrorInfo;
    private $options = [];
    private $defaults = [
        'from' => ['address'=> ADMIN_EMAIL, 'name' => APP_NAME],
        'reply' => ['address'=> ADMIN_EMAIL_SENDER, 'name' => 'No responder'],
        'isHtml' => true,
        'template' => '',
        'subject' => 'Mail de ' . APP_NAME,
        'template_mode' => 'relative'
    ];
    private $templatePath;

    private string $renderedBody;

    public function __construct(array $options = [], ?PHPMailer $mailer = null) {
        $this->setConfig();
        $this->options = array_merge($this->defaults, $options);
        $this->manager = $mailer ?? $this->createMailer();
    }

    protected function createMailer(): PHPMailer {
        $mailer = new PHPMailer(true);
        $mailer->isSMTP();
        $mailer->CharSet  = 'UTF-8';
        $mailer->Encoding = 'base64';
        return $mailer;
    }

    private function initMailer(): void {
        $this->manager = new PHPMailer(true);
        $this->manager->isSMTP();
        $this->manager->CharSet  = 'UTF-8';
        $this->manager->Encoding = 'base64';
    }

    protected function setConfig() {
        $this->config = nqv::getConfig('mail-settings');
    }

    protected function getConfig() {
        if(empty($this->config)) $this->setConfig();
        return $this->config;
    }

    protected function getDriver() {
        if(empty($this->config['active'])) throw new Exception('Error de configuración en Mail Settings: falta driver activo');
        if(empty($this->config['drivers'])) throw new Exception('Error de configuración en Mail Settings: faltan los drivers');
        $active = $this->config['active'];
        if(empty($this->config['drivers'][$active])) throw new Exception("El driver activo '{$active}' no existe en Mail Settings");
        return $this->config['drivers'][$active];
    }

    private function option(string $key, mixed $default = null): mixed {
        return $this->options[$key] ?? $default;
    }

    private function setOption(string $key, mixed $value): self {
        $this->options[$key] = $value;
        return $this;
    }

    private function resolveTemplate() {
        if(empty($this->option('fronturl'))) $this->setOption('fronturl', url(ROOT_PATH));
        
        $templateMode = $this->option('template_mode');
        $template     = $this->option('template');

        if($templateMode === 'absolute') {
            $this->templatePath = $template;
        } else {
            $this->templatePath = TEMPLATES_PATH . 'mails' . DIRECTORY_SEPARATOR . $template . '.php';
        }

        if(!is_file($this->templatePath)) {
            $msg = !DEBUG
                ? 'No se pudo crear la cuenta.'
                : 'La plantilla ' . $this->templatePath . ' no existe.';
            throw new Exception($msg);
        }

        if(!is_array($this->options['to'])) throw new Exception('La opción "TO" debe ser un array de arrays con direcciones de correo');
        ob_start();
        $ops = $this->options;
        include $this->templatePath;
        $this->renderedBody = ob_get_clean();
    }


    private function configureTransport(){

        $driver = $this->getDriver();

        if(in_array($driver['type'], ['smtp','gmail'])) {

            $this->manager->Host       = $driver['host'] ?? '';
            $this->manager->Port       = $driver['port'] ?? 587;
            $this->manager->SMTPAuth   = $driver['smtp_auth'] ?? true;

            if(!empty($driver['smtp_secure'])) {
                $this->manager->SMTPSecure = $driver['smtp_secure'];
            }

            if(isset($driver['smtp_auto_tls'])) {
                $this->manager->SMTPAutoTLS = $driver['smtp_auto_tls'];
            }

            if(!empty($driver['username'])) {
                $this->manager->Username = $driver['username'];
            }

            if(!empty($driver['password'])) {
                $this->manager->Password = $driver['password'];
            }

        } elseif($driver['type'] === 'ses') {

            $this->manager->Host = $driver['endpoint'] ?? '';
            $this->manager->Port = 587;
            $this->manager->SMTPAuth = true;
        }

        // From del driver
        if(!empty($driver['from'])) {
            $this->manager->setFrom(
                $driver['from']['address'],
                $driver['from']['name']
            );
        }
    }

    private function htmlToText(string $html): string {

        // Convertir saltos estructurales antes de eliminar etiquetas
        $html = preg_replace('/<\s*br\s*\/?>/i', "\n", $html);
        $html = preg_replace('/<\/p>/i', "\n\n", $html);
        $html = preg_replace('/<\/div>/i', "\n", $html);
        $html = preg_replace('/<\/h[1-6]>/i', "\n\n", $html);
        $html = preg_replace('/<\/li>/i', "\n", $html);

        // Eliminar el resto de etiquetas
        $text = strip_tags($html);

        // Decodificar entidades HTML
        $text = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');

        // Normalizar múltiples líneas vacías
        $text = preg_replace("/\n{3,}/", "\n\n", $text);
        
        return trim($text);
    }

    private function applyMessage() {
        if(empty($this->option('to'))) {
            throw new Exception('El destinatario no es válido.');
        }

        $this->manager->isHTML($this->option('isHtml'));

        // Reply-To
        if(!empty($this->option('reply'))) {
            $reply = $this->option('reply');
            $this->manager->addReplyTo(
                $reply['address'],
                $reply['name']
            );
        }

        // Destinatarios
        foreach($this->option('to') as $mail) {
            if(!(is_array($mail))) throw new Exception('Cada elemento de la opción "TO" debe ser un array con direcciones de correo');
            $this->manager->addAddress($mail['address'], $mail['name']);
        }

        // Subject
        $this->manager->Subject = $this->option('subject');

        $isHtml = $this->option('isHtml');

        // Generamos texto plano una sola vez
        $plainText = $this->htmlToText($this->renderedBody);

        $this->manager->isHTML($isHtml);

        if ($isHtml) {
            $this->manager->Body    = $this->renderedBody;
            $this->manager->AltBody = $this->option('AltBody') ?? $plainText;
        } else {
            $this->manager->WordWrap = 78;
            $this->manager->Body = $plainText;
        }

        // Attachments
        if(!empty($this->option('attachments'))) {
            foreach($this->option('attachments') as $attachment) {
                $this->manager->addAttachment($attachment);
            }
        }
    }


    private function resetMessage(): void {
        $this->manager->clearAddresses();
        $this->manager->clearReplyTos();
        $this->manager->clearAttachments();
    }

    private function compose(): void {
        $this->resetMessage();
        $this->resolveTemplate();
        $this->configureTransport();
        $this->applyMessage();
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

    public function send() {
        try {
            $this->compose();
            return $this->manager->send();
        } catch (Exception $e) {
            $this->ErrorInfo = $e->getMessage();
            _log($this->ErrorInfo, 'mail-error');
            throw $e;
        }
    }

    public function getError() {
        return $this->ErrorInfo;
    }

    public function test() {
        $this->resolveTemplate();
        return $this->renderedBody;
    }

}