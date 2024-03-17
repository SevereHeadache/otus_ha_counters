<?php

require __DIR__.'/../bootstrap.php';

use PhpAmqpLib\Message\AMQPMessage;
use SevereHeadache\OtusHaCounters\Components\RedisClient;
use SevereHeadache\OtusHaCounters\Components\RMQClient;

$client = new RMQClient();
try {
    $client->pull(RMQClient::QUEUE_RECIEVE, function (AMQPMessage $msg) use ($client) {
        $body = json_decode($msg->body, true);
        $messageId = $body['messageId'];
        $userId = $body['userId'];
        $redis = new RedisClient();
        $redis->incr("msg-unread-$userId");
        $msg->ack();

        $client->push(RMQClient::QUEUE_SEND, [
            'messageId' => $messageId,
            'action' => 'accept',
        ]);
    });
} catch (\Throwable $th) {
    $client->push(RMQClient::QUEUE_SEND, [
        'messageId' => $messageId,
        'action' => 'reject',
    ]);
}
