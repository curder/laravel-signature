# laravel-signature

Signature helper for Laravel

[三方接入文档](./INTERGRATION.md)

## 特性

* 对请求参数进行签名验证, 以保证数据的完整性
* 每次签名只能使用一次
* 支持 Laravel 11.x

## 安装

```bash
composer require hypocenter/laravel-signature
```

## 配置

```
php artisan vendor:publish --provider="Hypocenter\LaravelSignature\SignatureServiceProvider"
```

执行命令后会生成配置文件 app/config/signature.php

```php
<?php

return [
    // 默认的驱动
    'default'      => 'default',

    // 支持多个签名器配置
    'signatures'   => [
        'default' => [
            'resolver'       => 'header',
            'repository'     => 'array',
            'nonce_length'   => 16,
            'cache_driver'   => 'file',
            'cache_name'     => 'laravel-signature',
            'time_tolerance' => 5 * 60,
            'default_app_id' => 'tFVzAUy07VIj2p8v',
        ]
    ],

    // 数据获取器定义，支持从不同来源获取
    'resolvers'    => [
        'header' => [
            'class'         => Hypocenter\LaravelSignature\Payload\Resolvers\HeaderResolver::class,
            'key_app_id'    => 'X-SIGN-APP-ID',
            'key_sign'      => 'X-SIGN',
            'key_timestamp' => 'X-SIGN-TIME',
            'key_nonce'     => 'X-SIGN-NONCE',
        ],
        'query'  => [
            'class'         => Hypocenter\LaravelSignature\Payload\Resolvers\QueryResolver::class,
            'key_app_id'    => '_appid',
            'key_sign'      => '_sign',
            'key_timestamp' => '_time',
            'key_nonce'     => '_nonce',
        ]
    ],

    // App 定义数据仓库，支持从不同来源获取
    'repositories' => [
        // 从数据库中读取
        'model' => [
            'class' => Hypocenter\LaravelSignature\Define\Repositories\ModelRepository::class,
            'model' => Hypocenter\LaravelSignature\Define\Models\AppDefine::class,
        ],
        // 从配置文件中读取
        'array' => [
            'class'   => Hypocenter\LaravelSignature\Define\Repositories\ArrayRepository::class,
            'defines' => [
                // Add more defines here.
                [
                    'id'     => 'tFVzAUy07VIj2p8v',
                    'name'   => 'RPC',
                    'secret' => 'u4JsCDCwCUakBCVn',
                    'config' => null
                ],
            ],
        ],
    ],
];
```

## 驱动

可以配置多个驱动以应对不同场景的应用配置

驱动需要使用下面配置的`Repository`和`Resolver`

### Repository

定义如何获取应用配置。

* `ArrayRepository`: 应用 AppID 和 Secret 配置在 PHP 数组中, 适合简单固定的使用场景。
* `ModelRepository`: 应用 AppID 和 Secret 配置在数据库中,适合 App 较多的使用场景, 默认提供 `AppDefine` 模型来处理数据库操作.
  可继承 `AppDefine` 类, 自定义模型。

### Resolver

定义如何从请求中获取相关校验参数。

* `HeaderResolver`: 从 HTTP 请求头中获取
* `QueryResolver`: 从 GET 参数中获取

## 签名

如果作为客户端,单独使用签名可无需 `Resolver`, 但 `Repositroy` 必须配置

```php
$client = new \GuzzleHttp\Client(['base_uri' => env('RPC_SERVER')]);

$payload = new Payload::forSign()
  ->setAppId('your app ID') // 如果设置了 default_app_id 可省略
  ->setMethod('GET')
  ->setPath('api/users')
  ->setData(['page' => 1, 'page_size' => 20])
  ->build();

$driver = app('signature')->get();
$driver->sign($payload);

$res = $client->request($payload->getMethod(), $payload->getPath() . '?'. http_build_query($payload->getData()), [
    'headers' => [
        'Accept'        => "application/json",
        'X-SIGN-APP-ID' => $payload->getAppId(),
        'X-SIGN'        => $payload->getSign(),
        'X-SIGN-TIME'   => $payload->getTimestamp(),
        'X-SIGN-NONCE'  => $payload->getNonce()
    ]
]);
```

## 中间件

- 配置

    ```diff
    // bootstrap/app.php
    + use Hypocenter\LaravelSignature\Middlewares\SignatureMiddleware;
  
    return Application::configure(basePath: dirname(__DIR__))
        // ...
        ->withMiddleware(function (Middleware $middleware) {
    +       $middleware->alias(['signature' => SignatureMiddleware::class]);
        })
        // ...
    ;
    ```

- 使用

    ```php
    Route::get('sign', 'SignController')->middleware('signature'); // 使用默认驱动
    Route::get('no-sign', 'SignController')->middleware('signature:custom'); // 使用其他驱动
    ```

