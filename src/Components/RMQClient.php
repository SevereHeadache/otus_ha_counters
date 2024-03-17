<?php

namespace SevereHeadache\OtusHaCounters\Components;

use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

class RMQClient
{
    const EXCHANGE = 'saga';
    const QUEUE_SEND = 'msg-inc';
    const QUEUE_RECIEVE = 'msg-new';

    private AMQPStreamConnection $connection;
    private AMQPChannel $channel;

    public function __construct()
    {
        $this->connection = new AMQPStreamConnection(
            env('RMQ_HOST'),
            env('RMQ_PORT'),
            'guest',
            'guest',
            connection_timeout: 3.0,
            read_write_timeout: 3.0,
        );
        $this->channel = $this->connection->channel();
    }

    public function push($queue, $payload) 
    {
        $this->channel->exchange_declare(
            self::EXCHANGE,
            'direct',
            false,
            true,
            false,
            false,
            false,
        );

        $this->channel->queue_declare($queue, false, true, false, false);
        $this->channel->queue_bind($queue, self::EXCHANGE, self::QUEUE_SEND);

        $message = new AMQPMessage(is_string($payload) ? $payload : json_encode($payload), ['content_type' => 'application/json']);
        $this->channel->basic_publish($message, self::EXCHANGE, self::QUEUE_SEND);
    }

    public function pull($queue, $handler)
    {
        $this->channel->queue_declare($queue, false, true, false, false);

        $this->channel->basic_qos(null, 1, null);
        $this->channel->basic_consume($queue, '', false, false, false, false, $handler);

        while ($this->channel->is_open()) {
            $this->channel->wait();
        }
    }

    public function __destruct()
    {
        $this->connection->close();
        $this->channel->close();
    }
}
