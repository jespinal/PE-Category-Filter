<?php
/**
 * Dependency Injection Container
 *
 * @package PE Category Filter
 * @since 2.0.0
 */

namespace PavelEspinal\WpPlugins\PECategoryFilter\Core;

/**
 * Dependency Injection Container
 *
 * Manages service registration and resolution with support for singletons
 * and transient services. Inspired by Symfony's service container.
 */
class Container {
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
     * Services marked as singletons
     *
     * @var array<string, bool>
     */
    private array $singletons = [];

    /**
     * Bind a service to the container
     *
     * @param string $abstract The abstract identifier
     * @param callable|string $concrete The concrete implementation
     * @return void
     */
    public function bind(string $abstract, callable|string $concrete): void {
        $this->services[$abstract] = $concrete;
    }

    /**
     * Register a singleton service
     *
     * @param string $abstract The abstract identifier
     * @param callable|string $concrete The concrete implementation
     * @return void
     */
    public function singleton(string $abstract, callable|string $concrete): void {
        $this->bind($abstract, $concrete);
        $this->singletons[$abstract] = true;
    }

    /**
     * Resolve a service from the container
     *
     * @param string $abstract The abstract identifier
     * @return mixed The resolved service instance
     * @throws \InvalidArgumentException If service is not registered
     */
    public function make(string $abstract): mixed {
        // Return existing instance if it's a singleton
        if (isset($this->instances[$abstract])) {
            return $this->instances[$abstract];
        }

        // Check if service is registered
        if (!isset($this->services[$abstract])) {
            throw new \InvalidArgumentException("Service '{$abstract}' not registered");
        }

        $concrete = $this->services[$abstract];

        // Resolve the service
        if (is_callable($concrete)) {
            $instance = $concrete($this);
        } else {
            $instance = new $concrete();
        }

        // Store instance if it's a singleton
        if (isset($this->singletons[$abstract])) {
            $this->instances[$abstract] = $instance;
        }

        return $instance;
    }

    /**
     * Check if a service is registered
     *
     * @param string $abstract The abstract identifier
     * @return bool True if service is registered
     */
    public function has(string $abstract): bool {
        return isset($this->services[$abstract]);
    }

    /**
     * Get all registered services
     *
     * @return array<string, callable|string>
     */
    public function getServices(): array {
        return $this->services;
    }

    /**
     * Get all singleton services
     *
     * @return array<string, bool>
     */
    public function getSingletons(): array {
        return $this->singletons;
    }
}