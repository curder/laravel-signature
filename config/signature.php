<?php

use Hypocenter\LaravelSignature\Define;
use Hypocenter\LaravelSignature\Payload\Resolvers;

return [
    // 默认的驱动
    'default' => 'default',

    // 支持多个签名器配置
    'signatures' => [
        'default' => [
            'resolver' => 'header',
            'repository' => 'array',
            'nonce_length' => 16,
            'cache_driver' => 'file',
            'cache_name' => 'laravel-signature',
            'time_tolerance' => 5 * 60,
            'default_app_id' => 'tFVzAUy07VIj2p8v',
        ],
    ],

    // 数据获取器定义，支持从不同来源获取
    'resolvers' => [
        'header' => [
            'class' => Resolvers\HeaderResolver::class,
            'key_app_id' => 'X-SIGN-APP-ID',
            'key_sign' => 'X-SIGN',
            'key_timestamp' => 'X-SIGN-TIMESTAMP',
            'key_nonce' => 'X-SIGN-NONCE',
        ],
        'query' => [
            'class' => Resolvers\QueryResolver::class,
            'key_app_id' => '_appid',
            'key_sign' => '_sign',
            'key_timestamp' => '_timestamp',
            'key_nonce' => '_nonce',
        ],
    ],

    // App 定义数据仓库，支持从不同来源获取
    'repositories' => [
        // 从数据库中读取
        'model' => [
            'class' => Define\Repositories\ModelRepository::class,
            'model' => Define\Models\AppDefine::class,
        ],
        // 从配置文件中读取
        'array' => [
            'class' => Define\Repositories\ArrayRepository::class,
            'defines' => [
                // Add more defines here.
                [
                    'id' => 'tFVzAUy07VIj2p8v',
                    'name' => 'RPC',
                    'secret' => 'u4JsCDCwCUakBCVn',
                    'config' => null,
                ],
            ],
        ],
    ],
];
