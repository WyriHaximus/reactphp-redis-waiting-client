<?php declare(strict_types=1);

namespace WyriHaximus\React\Tests\Redis\WaitingClient;

use Clue\React\Redis\Client;
use Evenement\EventEmitterTrait;
use React\Promise\PromiseInterface;

class RedisClient implements Client
{
    use EventEmitterTrait;

    public function __call($name, $args): void
    {
        // TODO: Implement __call() method.
    }

    public function incr(...$args): PromiseInterface
    {
    }

    public function end(): void
    {
        // TODO: Implement end() method.
    }

    public function close(): void
    {
        // TODO: Implement close() method.
    }
}
