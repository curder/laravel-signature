<?php

namespace Hypocenter\LaravelSignature\Tests\Unit\Middlewares;

use Mockery as m;
use Illuminate\Http\Request;
use PHPUnit\Framework\TestCase;
use Hypocenter\LaravelSignature\Payload\Payload;
use Hypocenter\LaravelSignature\Contracts\Factory;
use Hypocenter\LaravelSignature\Signature\Context;
use Hypocenter\LaravelSignature\Signature\Signature;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Hypocenter\LaravelSignature\Exceptions\VerifyException;
use Hypocenter\LaravelSignature\Middlewares\SignatureMiddleware;
use Hypocenter\LaravelSignature\Exceptions\InvalidArgumentException;

class SignatureMiddlewareTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    public function testHandle(): void
    {
        $py = m::mock(Payload::class);

        $signature = m::mock(Signature::class);
        $signature->shouldReceive('resolve')->andReturn($py);
        $signature->shouldReceive('verify');

        $manager = m::mock(Factory::class);
        $manager->shouldReceive('get')->andReturn($signature);

        $next = static function ($request) {
            return 'called';
        };

        $middleware = new SignatureMiddleware($manager);
        $res = $middleware->handle(m::spy(Request::class), $next);

        $this->assertEquals('called', $res);
    }

    public function testInvalidExceptionToHttpException(): void
    {
        $py = m::mock(Payload::class);

        $signature = m::mock(Signature::class);
        $signature->shouldReceive('resolve')->andReturn($py);
        $signature->shouldReceive('verify')->andThrow(InvalidArgumentException::class);

        $manager = m::mock(Factory::class);
        $manager->shouldReceive('get')->andReturn($signature);

        $next = static function ($request) {
            return 'called';
        };

        $this->expectException(HttpException::class);

        $middleware = new SignatureMiddleware($manager);
        $res = $middleware->handle(m::spy(Request::class), $next);

        $this->assertEquals('called', $res);
    }

    public function testVerifyExceptionHttpException(): void
    {
        $py = m::mock(Payload::class);

        $signature = m::mock(Signature::class);
        $signature->shouldReceive('resolve')->andReturn($py);
        $signature->shouldReceive('verify')->andThrow(new VerifyException('', m::spy(Context::class)));

        $manager = m::mock(Factory::class);
        $manager->shouldReceive('get')->andReturn($signature);

        $next = static function ($request) {
            return 'called';
        };

        $this->expectException(HttpException::class);

        $middleware = new SignatureMiddleware($manager);
        $res = $middleware->handle(m::spy(Request::class), $next);

        $this->assertEquals('called', $res);
    }
}
