<?php

require __DIR__.'/../bootstrap.php';

use Klein\Response;
use Klein\Request;
use SevereHeadache\OtusHaCounters\Components\RedisClient;

$klein = new \Klein\Klein();

$klein->respond('GET', '/unreadMessages/[:user_id]', function (Request $request, Response $response) {
    $id = $request->paramsNamed()->get('user_id');
    $redis = new RedisClient();
    $count = $redis->get("msg-unread-$id");

    return $response->json((int) $count);
});

$klein->respond('POST', '/markAsRead/[:user_id]', function (Request $request, Response $response) {
    $id = $request->paramsNamed()->get('user_id');
    $redis = new RedisClient();
    $redis->decr("msg-unread-$id");

    return $response->json([]);
});

$klein->dispatch();
