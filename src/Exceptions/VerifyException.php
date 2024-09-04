<?php

namespace Hypocenter\LaravelSignature\Exceptions;

use Throwable;
use RuntimeException;
use Hypocenter\LaravelSignature\Payload\Payload;
use Hypocenter\LaravelSignature\Signature\Context;

class VerifyException extends RuntimeException
{
    private Context $context;

    public function __construct($message, Context $context, $code = 0, ?Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
        $this->context = $context;
    }

    public function getPayload(): Payload
    {
        return $this->context->getPayload();
    }

    public function getContext(): Context
    {
        return $this->context;
    }
}
