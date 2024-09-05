### 签名计算

步骤:

1. 组装请求数据字符串, 获得`DATA`
2. 按参数顺序,用中竖线“|”分割不同的参数(头尾无需中竖线), 获得`RAW_STR`
3. 执行 `HMAC_HASH('sha1', '{RAW_STR}', '{SECRET}')`
4. 输出摘要的小写16进制字符串形式作为签名

签名参数及顺序:

1. APPID: 应用 ID
2. SECRET: 密钥
3. TIME: 签名UNIX时间戳(秒)
4. METHOD: 请求方法, 如: get、post
5. PATH: 请求路径(不含主机名、端口号), 如: api/users
6. DATA: 数据(无需任何URL转义)
7. NONCE: 随机数

请求数据字符串:

* 按 KEY 的 ASCII 码正序排序
* 遍历排序的 KEY 数组, 拼接为 KEY:VALUE
* 如果 VALUE 是 Map, 递归生成 VALUE 的请求字符串, 拼接为: KEY:[VALUE]
* 如果 VALUE 是 Array, 视为 KEY 是整数的 Map, 执行 Map 的逻辑
* KEY:VALUE 组之间用分号“;”分割
* 例如
    * 输入: `b=1&c=2&a[]=3&a[]=4&d[a]=5&d[b]=6` 或 `{"b": 1, "c": 2, "a": [3,4], "d": {"a":4, "b":5 }}`
    * 输出: `a:[0:3;1:4];b:1;c:2;d:[a:5;d:6]`

PHP 示例代码:

```php
<?php

function sign($appId, $secret, $timestamp, $method, $path, $nonce, $data): string
{
    $signArr = [
        $appId,
        $secret,
        $timestamp,
        strtolower($method),
        strtolower($path),
        arr2str($data),
        $nonce,
    ];

    $raw = implode('|', $signArr);

    return hash_hmac('sha1', $raw, $secret);
}

function arr2str(?array $data): string
{
    if (empty($data)) {
        return '';
    }

    ksort($data);

    $result = [];

    foreach ($data as $key => $value) {
        if (is_array($value)) {
            // 递归处理嵌套数组
            $result[] = "{$key}:[" . arr2str($value) . "]";
        } else {
            // 直接附加非数组值
            $result[] = "{$key}:{$value}";
        }
    }


    return implode(';', $result);
}

$s = sign(
    appId: 'tFVzAUy07VIj2p8v',
    secret: 'u4JsCDCwCUakBCVn',
    timestamp: '1574661278',
    method: 'GET',
    path: 'api/users',
    nonce: '7o2jpms6l8ep',
    data: [
        'b' => 1,
        'c' => 2,
        'a' => [3, 4],
        'd' => ['a' => 5, 'b' => 6],
    ]);

assert("ddf8d0d008a12fc20a7c8713707886c2d814a7f7" === $s);
```

### 请求接口

请求参数 (Header):

* X-SIGN-APP-ID: APP ID
* X-SIGN-TIMESTAMP: 签名UNIX时间戳(秒)
* X-SIGN-NONCE: 签名随机数
* X-SIGN: 签名

PHP 示例代码

```php
$client = new \GuzzleHttp\Client(['base_uri' => env('RPC_SERVER')]);

$appId = 'your app ID';
$secret = 'app secret';
$timestamp = time();
$query = ['page' => 1, 'page_size' => 20];
$method = 'GET';
$path = 'api/users';
$nonce = 'l9i7a4sh9';

$sign = sign($appId, $secret, $timestamp, $method, $path, $nonce, arr2str($query));

$res = $client->request($method, $path . '?' . http_build_query($query), [
    'headers' => [
        'Accept'        => "application/json",
        'X-SIGN-APP-ID' => $appId,
        'X-SIGN'        => $sign,
        'X-SIGN-TIMESTAMP'   => $timestamp,
        'X-SIGN-NONCE'  => $nonce,
    ]
]);
```

### Python 示例代码

```python
import hashlib
import hmac
import requests
import time
import random
import string
from urllib.parse import urljoin


# 工具函数：生成当前秒级时间戳
def current_seconds():
    return str(int(time.time()))


# 工具函数：生成随机字符串
def random_string(length=24):
    return ''.join(random.choices(string.ascii_letters + string.digits, k=length))


# 工具函数：生成签名
def arr2str(params):
    if not params:
        return ''
    # 对参数按 key 排序，并生成 "key:value" 的字符串
    sorted_params = sorted(params.items())
    result = []
    for key, values in sorted_params:
        if isinstance(values, dict):
            # 递归处理嵌套字典
            param_value = f'[{arr2str(values)}]'
        elif isinstance(values, list):
            # 处理列表
            param_value = '[' + ';'.join(
                f'{str(index)}:{str(values[index])}' for index in range(len(values))) + ']'
        else:
            # 处理单个值
            param_value = str(values)
        result.append(f'{key}:{param_value}')
    return ';'.join(result)


# 工具函数：生成参数 map，递归处理嵌套字典和列表，并按 key 排序
def get_map(parameters):
    p_map = {}

    def process_value(value):
        if isinstance(value, dict):
            # 递归处理字典，并对键排序
            return {k: process_value(v) for k, v in sorted(value.items())}
        elif isinstance(value, list):
            # 处理列表，将列表中的元素转为字符串并连接
            return '[' + ';'.join(f'{str(index)}:{str(value[index])}' for index in range(len(value))) + ']'
        else:
            # 其他类型直接转为字符串
            return str(value)

    # 对顶级键进行排序
    for key, value in sorted(parameters.items()):
        p_map[key] = process_value(value)

    return p_map


# 工具函数：生成 HMAC 签名
def generate_hmac(secret, message):
    return hmac.new(secret.encode(), message.encode(), hashlib.sha1).hexdigest()


def generate_sign(app_id, secret, timestamp, method, uri, parameters, nonce):
    # 签名参数组装
    sign_arr = [
        app_id,
        secret,
        timestamp,
        method,
        uri.lstrip('/').lower(),
        arr2str(parameters),
        nonce
    ]

    # 使用 "|" 连接签名参数
    raw_string = '|'.join(sign_arr)

    print(f"Signature String: {raw_string}")

    # 生成 HMAC-SHA1 签名
    return generate_hmac(secret, raw_string)


# 请求函数
def request(method, uri, parameters, app_id, secret, base_url):
    # 请求方法
    method = method.lower()
    # 时间戳
    timestamp = current_seconds()
    # 随机字符串
    nonce = random_string()
    # 请求路径
    path = urljoin(base_url, uri)
    # 获取请求参数
    p_map = get_map(parameters)
    # 获取签名
    sign = generate_sign(app_id=app_id, secret=secret, timestamp=timestamp, method=method, uri=uri,
                         parameters=p_map, nonce=nonce)

    # 发起 HTTP 请求
    headers = {
        'Content-Type': 'application/json',
        "X-SIGN-APP-ID": app_id,
        "X-SIGN": sign,
        "X-SIGN-TIMESTAMP": timestamp,
        "X-SIGN-NONCE": nonce
    }

    # 打印调试信息（在生产环境中可以通过日志记录）
    print(f"Headers: {headers}")
    print(f"Request Parameters: {p_map}")

    # 根据方法发起不同的请求
    if method == 'get':
        response = requests.get(path, params=p_map, headers=headers, verify=False)
    elif method == 'post':
        response = requests.post(path, json=p_map, headers=headers, verify=False)
    else:
        raise ValueError("Unsupported HTTP method")

    return response


if __name__ == '__main__':
    # Example Usage:
    http_method = 'POST'
    request_uri = '/test'
    parameter_map = {'b': 1, 'c': 2, 'a': [6, 3, 4], 'd': {'a': 5, 'b': 6}}
    app_id = 'Zv3DCb1TGJt3ASYte78Pxl7g'
    secret = 'pcRBsBBC9ErduMGw5wUWMtKY'
    base_url = 'https://laravel11-demo.test'

    response = request(http_method, request_uri, parameter_map, app_id, secret, base_url)
    print(f"Response: {response.status_code}")
    print(f"Response Content: {response.content.decode()}")
```

