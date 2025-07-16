<?php

namespace App\Policies\Composables;

use Closure;

abstract class ComposablePolicy
{
    /** @var array<string, array<Closure>> */
    protected array $gateChains = [];

    public function __construct()
    {
        $this->bootTraits();
    }

    public function register(string $action, Closure $callback): void
    {
        $this->gateChains[$action][] = $callback;
    }

    /**
     * @param array<int, mixed> $args
     */ 
    protected function runChain(string $action, array ...$args): bool
    {
        if (!isset($this->gateChains[$action])) {
            throw new \RuntimeException("No gatechain registered for '$action'");
        }

        foreach ($this->gateChains[$action] as $callback) {
            if (!$callback(...$args)) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param array<int, mixed> $arguments
     */
    public function __call(string $method, array $arguments): bool
    {
        return $this->runChain($method, ...$arguments);
    }

    protected function bootTraits(): void
    {
        foreach (class_uses_recursive(static::class) as $trait) {
            $method = 'boot' . class_basename($trait);
            if (method_exists($this, $method)) {
                $this->$method();
            }
        }
    }
}
