<?php

namespace Hypocenter\LaravelSignature\Signature;

use Hypocenter\LaravelSignature\Define\Define;
use Hypocenter\LaravelSignature\Payload\Payload;

class Context
{
    private ?string $sign;

    private ?string $raw;

    public function __construct(private Payload $payload, private ?Define $define = null)
    {
        $this->payload = $payload;
        $this->define = $define;
    }

    public function getPayload(): Payload
    {
        return $this->payload;
    }

    public function getSign(): ?string
    {
        return $this->sign;
    }

    public function setSign(?string $sign): Context
    {
        $this->sign = $sign;

        return $this;
    }

    public function getRaw(): ?string
    {
        return $this->raw;
    }

    public function setRaw(?string $raw): Context
    {
        $this->raw = $raw;

        return $this;
    }

    public function getDefine(): ?Define
    {
        return $this->define;
    }

    public function setDefine(Define $define): Context
    {
        $this->define = $define;

        return $this;
    }
}
