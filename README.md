# laravel-signature

Signature helper for Laravel

<img width="1096" alt="image" src="https://github.com/user-attachments/assets/d5af990b-1068-47d3-a9eb-e27b5e606601">

[三方接入文档](./INTERGRATION.md)

## 特性

* 对请求参数进行签名验证, 以保证数据的完整性
* 每次签名只能使用一次
* 支持 Laravel 11.x

## 安装

```bash
composer config repositories.curder/laravel-signature github https://github.com/curder/laravel-signature.git
composer require hypocenter/laravel-signature:dev-master
```

## 配置

```
php artisan vendor:publish --provider="Hypocenter\LaravelSignature\SignatureServiceProvider"
```

执行命令后会生成配置文件 `config/signature.php`，内容如下：

```php
<?php

use Hypocenter\LaravelSignature\Define;
use Hypocenter\LaravelSignature\Payload\Resolvers;

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
            'class'         => Resolvers\HeaderResolver::class,
            'key_app_id'    => 'X-SIGN-APP-ID',
            'key_sign'      => 'X-SIGN',
            'key_timestamp' => 'X-SIGN-TIMESTAMP',
            'key_nonce'     => 'X-SIGN-NONCE',
        ],
        'query'  => [
            'class'         => Resolvers\QueryResolver::class,
            'key_app_id'    => '_appid',
            'key_sign'      => '_sign',
            'key_timestamp' => '_timestamp',
            'key_nonce'     => '_nonce',
        ]
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
            'class'   => Define\Repositories\ArrayRepository::class,
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

### 配置其他驱动

```diff
return [
    // 默认的驱动
    'default' => 'custom',                                 # 修改默认驱动为 `custom`

    // 支持多个签名器配置
    'signatures' => [
        'default' => [
            'resolver' => 'header',
            'repository' => 'array',
            'nonce_length' => 16,
            'cache_driver' => 'file',
            'cache_name' => 'laravel-signature',
            'time_tolerance' => 5 * 60,
            'default_app_id' => 'tFVzAUy07VIj2p8v2',
        ],
+        'custom' => [
+            'resolver' => 'header',                         # 使用 header 传递参数 
+            'repository' => 'model',                        # 使用数据库驱动
+            'nonce_length' => 24,                           # 修改随机数的长度
+            'cache_driver' => 'redis',                      # 使用 Redis 做缓存，防止请求重放
+            'cache_name' => 'laravel-custom-signature',     # 缓存名称
+            'time_tolerance' => 5 * 60,                     # 时间容忍度，单位秒
+            'default_app_id' => 'Zv3DCb1TGJt3ASYte78Pxl7g', # 数据库存在且默认
+        ],
    ],
    // ...
];
```

## 驱动

可以配置多个驱动以应对不同场景的应用配置，驱动需要使用下面配置的`Repository`和`Resolver`。

### Repository

定义如何获取应用配置。

* `ArrayRepository`: 应用 `AppID` 和 `Secret` 配置在 PHP 数组中, 适合简单固定的使用场景。
* `ModelRepository`: 应用 `AppID` 和 `Secret` 配置在数据库中,适合 App 较多的使用场景, 默认提供 `AppDefine` 模型来处理数据库操作.
  可继承 `AppDefine` 类, 自定义模型。

### Resolver

定义如何从请求中获取相关校验参数。

* `HeaderResolver`: 从 `HTTP` 请求头中获取
* `QueryResolver`: 从 `GET` 参数中获取

## 签名

如果作为客户端,单独使用签名可无需 `Resolver`, 但 `Repositroy` 必须配置

- **GET 请求**
  ```php
  $payload = Payload::forSign()
      ->setAppId(config('signature.signatures.default.default_app_id')) // 如果设置了 default_app_id 可省略
      ->setMethod('GET')
      ->setPath('test')
      ->setData(['foo' => 'bar'])
      ->build();

  $driver = app('signature')->get();
  $driver->sign($payload);
  //    dd($payload->getAppId(), $payload->getSign(), $payload->getTimestamp(), $payload->getNonce(), $payload->getMethod(), $payload->getPath());

  return \Illuminate\Support\Facades\Http::withoutVerifying()
      ->withHeaders([
          'Accept' => 'application/json',
          'X-SIGN-APP-ID' => $payload->getAppId(),
          'X-SIGN' => $payload->getSign(),
          'X-SIGN-TIMESTAMP' => $payload->getTimestamp(),
          'X-SIGN-NONCE' => $payload->getNonce(),
      ])
      ->baseUrl('https://laravel11-demo.test')
      ->send($payload->getMethod(), $payload->getPath().'?'.http_build_query($payload->getData())API 接口参数签名)
      ->body();
  ```

- **POST 请求**

  ```diff
  $payload = Payload::forSign()
      ->setAppId(config('signature.signatures.default.default_app_id')) // 如果设置了 default_app_id 可省略
  -    ->setMethod('GET')
  +    ->setMethod('POST')
      ->setPath('test')
      ->setData(['foo' => 'bar'])
      ->build();

  $driver = app('signature')->get();
  $driver->sign($payload);
  //    dd($payload->getAppId(), $payload->getSign(), $payload->getTimestamp(), $payload->getNonce(), $payload->getMethod(), $payload->getPath());

  return \Illuminate\Support\Facades\Http::withoutVerifying()
      ->withHeaders([
          'Accept' => 'application/json',
          'X-SIGN-APP-ID' => $payload->getAppId(),
          'X-SIGN' => $payload->getSign(),
          'X-SIGN-TIMESTAMP' => $payload->getTimestamp(),
          'X-SIGN-NONCE' => $payload->getNonce(),
      ])
      ->baseUrl('https://laravel11-demo.test')
  -    ->send($payload->getMethod(), $payload->getPath().'?'.http_build_query($payload->getData()))
  +    ->send($payload->getMethod(), $payload->getPath(), ['form_params' => $payload->getData()])
      ->body();
  ```

## 中间件

- `bootstrap/app.php` 配置

    ```diff
    + use Hypocenter\LaravelSignature\Middlewares\SignatureMiddleware;
  
    return Application::configure(basePath: dirname(__DIR__))
        // ...
        ->withMiddleware(function (Middleware $middleware) {
    +       $middleware->alias(['signature' => SignatureMiddleware::class]);
        })
        // ...
    ;
    ```

- 路由中使用

    ```php
    Route::get('sign', 'SignController')->middleware('signature'); // 使用默认驱动
    Route::get('no-sign', 'SignController')->middleware('signature:custom'); // 使用其他驱动
    ```

## 参考

- [API 接口参数签名](https://sa-token.cc/doc.html#/plugin/api-sign)
- [API接口参数签名 跨系统接口调用安全方案(sa-token) | Bilibili ](https://www.bilibili.com/video/BV17oeKeZEHo)
- [larva/laravel-auth-signature-guard](https://github.com/larvatecn/laravel-auth-signature-guard)