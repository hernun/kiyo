<?php
class nqvMailConfig {

    private array $config;

    public function __construct() {
        $this->config = nqv::getConfig('mail-settings');
    }

    public function getActive(): string {
        return $this->config['active'] ?? '';
    }

    public function setActive(string $driver): void {
        if (isset($this->config['drivers'][$driver])) {
            $this->config['active'] = $driver;
        }
    }

    public function getDrivers(): array {
        return $this->config['drivers'] ?? [];
    }

    public function getDriver(string $name): array {
        return $this->config['drivers'][$name] ?? [];
    }

    public function updateDriver(string $name, array $data): void {

        if (!isset($this->config['drivers'][$name])) {
            return;
        }

        foreach ($data as $key => $value) {

            if (!array_key_exists($key, $this->config['drivers'][$name])) {
                continue;
            }

            $this->config['drivers'][$name][$key] = $this->sanitizeValue($key, $value);
        }
    }

    public function updateNested(string $driver, string $parent, array $data): void {

        if (!isset($this->config['drivers'][$driver][$parent])) {
            return;
        }

        foreach ($data as $key => $value) {

            if (!array_key_exists($key, $this->config['drivers'][$driver][$parent])) {
                continue;
            }

            $this->config['drivers'][$driver][$parent][$key] = $this->sanitizeValue($key, $value);
        }
    }

    private function sanitizeValue(string $key, mixed $value): mixed {
        switch ($key) {

            case 'host':
            case 'endpoint':
            case 'region':
                return trim((string)$value);

            case 'port':
                $port = (int)$value;
                return ($port >= 0 && $port <= 65535) ? $port : 0;

            case 'smtp_auth':
            case 'smtp_auto_tls':
                return (bool)$value;

            case 'smtp_secure':
                $allowed = ['', 'ssl', 'tls'];
                return in_array($value, $allowed, true) ? $value : '';

            case 'address':
                return filter_var($value, FILTER_VALIDATE_EMAIL) ?: '';

            case 'name':
                $value = trim((string)$value);
                return $value !== '' ? $value : APP_TITLE;

            default:
                return is_string($value) ? trim($value) : $value;
        }
    }

    public function toJson(): string {
        return json_encode($this->config, JSON_PRETTY_PRINT);
    }

    public function save() {
        nqv::setConfig('mail-settings',$this->toJson());
    }
}
