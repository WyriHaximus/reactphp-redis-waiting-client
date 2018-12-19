<?php declare(strict_types=1);

namespace WyriHaximus\React\Tests\Redis\WaitingClient;

use ApiClients\Tools\TestUtilities\TestCase;
use Clue\React\Redis\Client;
use Clue\React\Redis\Factory;
use Prophecy\Argument;
use Psr\Log\LoggerInterface;
use React\EventLoop\LoopInterface;
use React\Promise\Deferred;
use WyriHaximus\React\Redis\WaitingClient\WaitingClient;
use function React\Promise\resolve;

/**
 * @internal
 */
final class WaitingClientTest extends TestCase
{
    private const DSN = 'redis://::1/13';

    public function testCreate(): void
    {
        $loop = $this->prophesize(LoopInterface::class);

        $logger = $this->prophesize(LoggerInterface::class);
        $logger->debug('Connecting')->shouldBeCalled();
        $logger->debug('Connected')->shouldNotBeCalled();
        $logger->debug('Executing 0 waiting call(s)')->shouldNotBeCalled();
        $logger->debug('Executed all waiting calls, any new calls will be send to Redis directly')->shouldNotBeCalled();

        WaitingClient::create($loop->reveal(), self::DSN, $logger->reveal());
    }

    public function testLogging(): void
    {
        $logger = $this->prophesize(LoggerInterface::class);
        $logger->debug('Connecting')->shouldBeCalled();
        $logger->debug('Connected')->shouldBeCalled();
        $logger->debug('Executing 0 waiting call(s)')->shouldBeCalled();
        $logger->debug('Executed all waiting calls, any new calls will be send to Redis directly')->shouldBeCalled();

        $redisFactory = $this->prophesize(Factory::class);
        $redisFactory->createClient(self::DSN)->shouldbeCalled()->willReturn(resolve($this->prophesize(Client::class)->reveal()));

        new WaitingClient($redisFactory->reveal(), self::DSN, $logger->reveal());
    }

    public function testCalls(): void
    {
        $logger = $this->prophesize(LoggerInterface::class);
        $logger->debug(Argument::type('string'))->shouldBeCalled();
        $logger->debug('Executing 1 waiting call(s)')->shouldBeCalled();

        $deferred = new Deferred();

        $redis = $this->prophesize(RedisClient::class);
        $redis->incr('key')->shouldBeCalled()->willReturn(resolve(true));
        $redis->incr('sleutel')->shouldBeCalled()->willReturn(resolve(true));

        $redisFactory = $this->prophesize(Factory::class);
        $redisFactory->createClient(self::DSN)->shouldbeCalled()->willReturn($deferred->promise());

        $waitingClient = new WaitingClient($redisFactory->reveal(), self::DSN, $logger->reveal());

        $waitingClient->incr('key');

        $deferred->resolve($redis->reveal());

        $waitingClient->incr('sleutel');
    }
}
