<?php
namespace OVO\Http;

class Response {
    protected array $data;

    public function __construct(array $data = []) {
        $this->data = $data;
    }

    public function isPage() {
        return ($this->data['template'] ?? null) === 'page';
    }

    public function send(): void {
        extract($this->data);
        // Esto reemplaza al index legacy, SIN mover archivos
        require $_SERVER['DOCUMENT_ROOT'] . '/core/views/layout.php';
    }

    public function getDescription(): ?string {
        if (!$this->isPage()) return null;
        return !empty($this->data['page']['description']) ? $this->data['page']['description']:APP_DESCRIPTION;
    }

    public function getAlternates() {
        return $this->data['alternates'];
    }

    public function getTitle() {
        return $this->isPage() ? $this->data['page']['title'] . ' | ' . APP_TITLE:APP_TITLE;
    }

    public function getUrl() {
        return URL . $_SERVER['REQUEST_URI'];
    }
}

