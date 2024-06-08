<?php
require 'vendor/autoload.php';

use Predis\Client as Predis;
use Slim\Http\Request;
use Slim\Http\Response;

$app = new \Slim\App;

$redis = new Predis(array(
    "scheme" => "tcp",
    "host" => "127.0.0.1",
    "port" => 6379,
    "database" => 1,  // 选择使用的数据库编号
));

// Set headers for cross-domain request
$app->add(function ($req, $res, $next) {
    $response = $next($req, $res);
    return $response
        ->withHeader('Access-Control-Allow-Origin', '*')
        ->withHeader('Access-Control-Allow-Headers', 'X-Requested-With, Content-Type, Accept, Origin, Authorization')
        ->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, PATCH, OPTIONS');
});

$rdsKey = 'custom:' . date('md');

$app->get("/", function (Request $request, Response $response) {
    return $response->withStatus(302)->withHeader('Location', '/view/home.html');
});

// 保存接口->保存到redis
$app->post('/save', function (Request $request, Response $response) use ($redis, $rdsKey) {
    $params = $request->getParsedBody();
    $data = array(
        'data1' => $params['data1'],
        'data2' => $params['data2'],
        'data3' => $params['data3'],
        'create_at' => date('Y-m-d H:i:s'),
        'ip' => getUserIpAddr(),
    );
    $redis->rpush($rdsKey, json_encode($data));
    $redis->expire($rdsKey, 3600 * 24); // 2天过期时间
    return $response->withJson(message([], "提交成功！"));
});

// 读取数据
$app->get('/lists', function (Request $request, Response $response) use ($redis, $rdsKey) {
    $strings = $redis->lrange($rdsKey, 0, -1);
    return $response->withJson(message(array_reverse($strings)));
});

$app->run();


/**
 * 获取用户IP
 * @return mixed
 */
function getUserIpAddr()
{
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
        return $_SERVER['HTTP_CLIENT_IP'];
    } else if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        return $_SERVER['HTTP_X_FORWARDED_FOR'];
    } else {
        return $_SERVER['REMOTE_ADDR'];
    }
}

/**
 * 返回消息
 * @param $data
 * @param $msg
 * @param $code
 * @return array
 */
function message($data, $msg = "成功", $code = 200)
{
    return ["data" => $data, "code" => $code, "msg" => $msg];
}
