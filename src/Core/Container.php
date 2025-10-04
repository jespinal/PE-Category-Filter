<?php

namespace PavelEspinal\WpPlugins\PECategoryFilter\Core;

/**
 * Service Container for dependency injection
 *
 * @package PE Category Filter
 * @since 2.0.0
 */
class Container
{
    /**
     * Registered services
     *
     * @var array<string, callable|string>
     */
    private array $services = [];

    /**
     * Resolved service instances
     *
     * @var array<string, mixed>
     */
    private array $instances = [];

    /**
     * Bind a service to the container
     *
     * @param string $abstract The abstract identifier
     * @param callable|string $concrete The concrete implementation
     * @return void
     */
    public function bind(string $abstract, callable|string $concrete): void
    {
        $this->services[$abstract] = $concrete;
    }

    /**
     * Bind a singleton service to the container
     *
     * @param string $abstract The abstract identifier
     * @param callable|string $concrete The concrete implementation
     * @return void
     */
    public function singleton(string $abstract, callable|string $concrete): void
    {
        $this->bind($abstract, $concrete);
    }

    /**
     * Resolve a service from the container
     *
     * @param string $abstract The abstract identifier
     * @return mixed The resolved service instance
     * @throws \InvalidArgumentException If service is not found
     */
    public function make(string $abstract): mixed
    {
        // Return existing instance if it's a singleton
        if (isset($this->instances[$abstract])) {
            return $this->instances[$abstract];
        }

        // Check if service is registered
        if (!isset($this->services[$abstract])) {
            throw new \InvalidArgumentException("Service '{$abstract}' is not registered in the container.");
        }

        $concrete = $this->services[$abstract];

        // Resolve the service
        if (is_callable($concrete)) {
            $instance = $concrete($this);
        } else {
            $instance = new $concrete();
        }

        // Store instance for singletons
        $this->instances[$abstract] = $instance;

        return $instance;
    }

    /**
     * Check if a service is registered
     *
     * @param string $abstract The abstract identifier
     * @return bool True if service is registered
     */
    public function has(string $abstract): bool
    {
        return isset($this->services[$abstract]);
    }

    /**
     * Get all registered services
     *
     * @return array<string, callable|string> Registered services
     */
    public function getServices(): array
    {
        return $this->services;
    }

    /**
     * Clear all instances (useful for testing)
     *
     * @return void
     */
    public function clear(): void
    {
        $this->instances = [];
    }
}
