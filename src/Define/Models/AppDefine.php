<?php

namespace Hypocenter\LaravelSignature\Define\Models;

use Illuminate\Database\Eloquent\Model;
use Hypocenter\LaravelSignature\Define\Define;
use Hypocenter\LaravelSignature\Define\IntoDefine;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Hypocenter\LaravelSignature\Database\Factories\AppDefineFactory;

/**
 * 默认的基于 Laravel ORM 的模型，用于使用数据库存储 App 定义
 * 也可以自定义模型，只需实现 IntoDefine 接口即可
 *
 * @property string $name
 * @property string $id
 * @property string $secret
 * @property array $config
 *
 * @see ../../../database/migrations/0001_01_01_000003_signature_create_app_defines_table.php
 */
class AppDefine extends Model implements IntoDefine
{
    use HasFactory;

    protected $casts = [
        'config' => 'json',
    ];

    public $incrementing = false;

    protected $keyType = 'string';

    public function intoSignatureDefine(): Define
    {
        return new Define(
            $this->id,
            $this->name,
            $this->secret,
            $this->config
        );
    }

    protected static function newFactory(): AppDefineFactory
    {
        return new AppDefineFactory;
    }
}
