<?php
namespace OVO\Http;

class Response {
    protected array $data;

    public function __construct(array $data = []) {
        $this->data = $data;
    }

    public function send(): void {
        extract($this->data);
        // Esto reemplaza al index legacy, SIN mover archivos
        require $_SERVER['DOCUMENT_ROOT'] . '/core/views/layout.php';
    }
}

