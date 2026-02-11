<?php
declare(strict_types=1);

namespace OVO\Http;

class Request
{
    protected string $method;
    protected string $uri;
    protected array $query;
    protected array $post;
    protected array $files;
    protected array $server;
    protected array $cookies;

    protected function __construct(
        string $method,
        string $uri,
        array $query,
        array $post,
        array $files,
        array $server,
        array $cookies
    ) {
        $this->method  = $method;
        $this->uri     = $uri;
        $this->query   = $query;
        $this->post    = $post;
        $this->files   = $files;
        $this->server  = $server;
        $this->cookies = $cookies;
    }

    public static function fromGlobals(): self
    {
        return new self(
            $_SERVER['REQUEST_METHOD'] ?? 'GET',
            $_SERVER['REQUEST_URI'] ?? '/',
            $_GET ?? [],
            $_POST ?? [],
            $_FILES ?? [],
            $_SERVER ?? [],
            $_COOKIE ?? []
        );
    }

    /* ---- getters simples ---- */

    public function method(): string
    {
        return $this->method;
    }

    public function uri(): string
    {
        return $this->uri;
    }

    public function query(): array
    {
        return $this->query;
    }

    public function post(): array
    {
        return $this->post;
    }

    public function files(): array
    {
        return $this->files;
    }

    public function server(): array
    {
        return $this->server;
    }

    public function cookies(): array
    {
        return $this->cookies;
    }
}
