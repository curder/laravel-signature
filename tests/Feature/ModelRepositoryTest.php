<?php

namespace Hypocenter\LaravelSignature\Tests\Feature;

use Hypocenter\LaravelSignature\Payload\Payload;
use Hypocenter\LaravelSignature\Contracts\Factory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Hypocenter\LaravelSignature\Define\Models\AppDefine;

class ModelRepositoryTest extends SignatureTestCase
{
    use RefreshDatabase;

    protected function getEnvironmentSetUp($app): void
    {
        parent::getEnvironmentSetUp($app);
        $app['config']->set('signature.signatures.default.repository', 'model');
    }

    public function testDefaultSignAndVerify(): void
    {
        $this->withoutExceptionHandling();

        /** @var AppDefine $app */
        $config = $this->app->get('config')->get('signature');
        $app_id = data_get($config, 'signatures.default.default_app_id');

        AppDefine::factory()->create(['id' => $app_id]);

        $this->setUpRoute();
        $py = Payload::forSign()
            ->setMethod('post')
            ->setPath('/default/foo')
            ->setData(['a' => 1])
            ->build();

        $ctx = $this->app->make(Factory::class)->get()->sign($py);

        $this->assertEquals($app_id, $ctx->getDefine()->getId());
        $this->assertEquals(40, strlen($ctx->getSign()));

        $res = $this->post('/default/foo', ['a' => 1], [
            'X-SIGN-APP-ID' => $ctx->getPayload()->getAppId(),
            'X-SIGN' => $ctx->getPayload()->getSign(),
            'X-SIGN-TIME' => $ctx->getPayload()->getTimestamp(),
            'X-SIGN-NONCE' => $ctx->getPayload()->getNonce(),
        ]);

        $res->assertOk();
        $res->assertSee('bar');
    }
}
