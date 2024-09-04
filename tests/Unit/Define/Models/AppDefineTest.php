<?php

namespace Hypocenter\LaravelSignature\Tests\Unit\Define\Models;

use PHPUnit\Framework\TestCase;
use Hypocenter\LaravelSignature\Define\Models\AppDefine;

class AppDefineTest extends TestCase
{
    public function testIntoSignatureDefine(): void
    {
        $m = new AppDefine;
        $m->id = 1;
        $m->name = 'name';
        $m->secret = 'secret';
        $m->config = ['a' => 1];

        $def = $m->intoSignatureDefine();

        $this->assertEquals(1, $def->getId());
        $this->assertEquals('name', $def->getName());
        $this->assertEquals('secret', $def->getSecret());
        $this->assertEquals(['a' => 1], $def->getConfig());
    }
}
