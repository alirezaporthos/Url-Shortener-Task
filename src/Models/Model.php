<?php

namespace UrlShortener\Models;

abstract class Model {
    protected $attributes = [];
    protected $errors = [];

    public function __construct(array $attributes = []) {
        $this->fill($attributes);
    }

    public function fill(array $attributes): void {
        foreach ($attributes as $key => $value) {
            if (property_exists($this, $key)) {
                $this->$key = $value;
            }
            $this->attributes[$key] = $value;
        }
    }

    public function getAttribute(string $key) {
        return $this->attributes[$key] ?? null;
    }

    public function getAttributes(): array {
        return $this->attributes;
    }

    public function addError(string $field, string $message): void {
        $this->errors[$field][] = $message;
    }

    public function getErrors(): array {
        return $this->errors;
    }

    public function hasErrors(): bool {
        return !empty($this->errors);
    }

    abstract public function validate(): bool;
} 