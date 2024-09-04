<?php

namespace Hypocenter\LaravelSignature\Payload;

use Hypocenter\LaravelSignature\Payload\Builder\SignBuilder;
use Hypocenter\LaravelSignature\Payload\Builder\VerifyBuilder;

class Payload
{
    private function __construct(
        private ?array $data = null,
        private ?string $appId = null,
        private ?string $sign = null,
        private ?int $timestamp = null,
        private ?string $nonce = null,
        private ?string $path = null,
        private ?string $method = null,
    ) {}

    public static function forSign(): SignBuilder
    {
        return new SignBuilder(new self);
    }

    public static function forVerify(): VerifyBuilder
    {
        return new VerifyBuilder(new self);
    }

    public function getData(): ?array
    {
        return $this->data;
    }

    public function setData(?array $data): Payload
    {
        $this->data = $data;

        return $this;
    }

    public function getAppId(): ?string
    {
        return $this->appId;
    }

    public function setAppId(?string $appId): Payload
    {
        $this->appId = $appId;

        return $this;
    }

    public function getSign(): ?string
    {
        return $this->sign;
    }

    public function setSign(?string $sign): Payload
    {
        $this->sign = $sign;

        return $this;
    }

    public function getTimestamp(): ?int
    {
        return $this->timestamp;
    }

    public function setTimestamp(?int $timestamp): Payload
    {
        $this->timestamp = $timestamp;

        return $this;
    }

    public function getNonce(): ?string
    {
        return $this->nonce;
    }

    public function setNonce(?string $nonce): Payload
    {
        $this->nonce = $nonce;

        return $this;
    }

    public function getPath(): ?string
    {
        return $this->path;
    }

    public function setPath(?string $path): Payload
    {
        $this->path = $path;

        return $this;
    }

    public function getMethod(): ?string
    {
        return $this->method;
    }

    public function setMethod(?string $method): Payload
    {
        $this->method = $method;

        return $this;
    }
}
