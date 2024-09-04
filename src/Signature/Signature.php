<?php

namespace Hypocenter\LaravelSignature\Signature;

use Exception;
use Hypocenter\LaravelSignature\Payload\Payload;
use Hypocenter\LaravelSignature\Exceptions\VerifyException;

interface Signature
{
    /**
     * 从请求中获取待校验数据
     */
    public function resolve(): Payload;

    /**
     * 签名
     *
     * @throw InvalidArgumentException
     */
    public function sign(Payload $payload): Context;

    /**
     * 校验
     *
     * @throws Exception|VerifyException
     */
    public function verify(Payload $payload): Context;
}
