<?php

namespace Hypocenter\LaravelSignature\Define;

class Define
{
    public function __construct(
        private readonly string $id,
        private readonly string $name,
        private readonly string $secret,
        private readonly ?array $config,
    ) {}

    public function getName(): string
    {
        return $this->name;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getSecret(): string
    {
        return $this->secret;
    }

    public function getConfig(): ?array
    {
        return $this->config;
    }
}
