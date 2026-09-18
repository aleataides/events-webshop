<?php

declare(strict_types=1);

namespace Tests\Support;

use Predis\ClientInterface;

/**
 * Minimal in-memory Predis\ClientInterface fake — only the commands this
 * app actually issues (get/setex/incr/expire), routed via __call.
 */
final class FakeRedis implements ClientInterface
{
    /** @var array<string, string> */
    private array $store = [];

    public function getCommandFactory()
    {
        throw new \BadMethodCallException(__METHOD__ . ' not supported by FakeRedis.');
    }

    public function getOptions()
    {
        throw new \BadMethodCallException(__METHOD__ . ' not supported by FakeRedis.');
    }

    public function connect()
    {
        throw new \BadMethodCallException(__METHOD__ . ' not supported by FakeRedis.');
    }

    public function disconnect()
    {
        throw new \BadMethodCallException(__METHOD__ . ' not supported by FakeRedis.');
    }

    public function getConnection()
    {
        throw new \BadMethodCallException(__METHOD__ . ' not supported by FakeRedis.');
    }

    public function createCommand($method, $arguments = [])
    {
        throw new \BadMethodCallException(__METHOD__ . ' not supported by FakeRedis.');
    }

    public function executeCommand($command)
    {
        throw new \BadMethodCallException(__METHOD__ . ' not supported by FakeRedis.');
    }

    public function __call($method, $arguments)
    {
        return match ($method) {
            'get' => $this->store[$arguments[0]] ?? null,
            'setex' => $this->store[$arguments[0]] = (string) $arguments[2],
            'incr' => $this->store[$arguments[0]] = (string) (((int) ($this->store[$arguments[0]] ?? 0)) + 1),
            'expire' => 1,
            default => throw new \BadMethodCallException("FakeRedis does not support '{$method}'."),
        };
    }
}
