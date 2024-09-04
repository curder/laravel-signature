<?php

namespace Hypocenter\LaravelSignature\Middlewares;

use Closure;
use Exception;
use Illuminate\Http\Request;
use Hypocenter\LaravelSignature\Contracts\Factory;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Hypocenter\LaravelSignature\Exceptions\VerifyException;
use Hypocenter\LaravelSignature\Exceptions\InvalidArgumentException;

class SignatureMiddleware
{
    private Factory $signatureManager;

    public function __construct(Factory $signatureManager)
    {
        $this->signatureManager = $signatureManager;
    }

    /**
     * @param  null  $signatureName
     * @return mixed
     *
     * @throws Exception
     */
    public function handle(Request $request, Closure $next, $signatureName = null)
    {
        try {
            $signature = $this->signatureManager->get($signatureName);
            $payload = $signature->resolve();
            $signature->verify($payload);
        } catch (VerifyException|InvalidArgumentException $e) {
            throw new HttpException(400, $e->getMessage(), $e);
        }

        return $next($request);
    }
}
