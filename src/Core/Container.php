<?php
/**
 * Dependency Injection Container
 *
 * @package PE Category Filter
 * @since 2.0.0
 */

namespace PavelEspinal\WpPlugins\PECategoryFilter\Core;

use InvalidArgumentException;

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
	private array $services = array();

	/**
	 * Resolved service instances
	 *
	 * @var array<string, mixed>
	 */
	private array $instances = array();

	/**
	 * Services marked as singletons
	 *
	 * @var array<string, bool>
	 */
	private array $singletons = array();

	/**
	 * Bind a service to the container
	 *
	 * @param string          $abstract_identifier The abstract identifier.
	 * @param callable|string $concrete_identifier The concrete implementation.
	 * @return void
	 */
	public function bind( string $abstract_identifier, callable|string $concrete_identifier ): void {
		$this->services[ $abstract_identifier ] = $concrete_identifier;
	}

	/**
	 * Register a singleton service
	 *
	 * @param string          $abstract_identifier The abstract identifier.
	 * @param callable|string $concrete_identifier The concrete implementation.
	 * @return void
	 */
	public function singleton( string $abstract_identifier, callable|string $concrete_identifier ): void {
		$this->bind( $abstract_identifier, $concrete_identifier );
		$this->singletons[ $abstract_identifier ] = true;
	}

	/**
	 * Resolve a service from the container
	 *
	 * @param string $abstract_identifier The abstract identifier.
	 * @return mixed The resolved service instance
	 * @throws InvalidArgumentException If service is not registered.
	 */
	public function make( string $abstract_identifier ): mixed {
		// Return existing instance if it's a singleton.
		if ( isset( $this->instances[ $abstract_identifier ] ) ) {
			return $this->instances[ $abstract_identifier ];
		}

		// Check if service is registered.
		if ( ! isset( $this->services[ $abstract_identifier ] ) ) {
			throw new InvalidArgumentException( "Service '{$abstract_identifier}' is not registered in the container." ); // phpcs:ignore
		}

		$concrete_identifier = $this->services[ $abstract_identifier ];

		// Resolve the service.
		if ( is_callable( $concrete_identifier ) ) {
			$instance = $concrete_identifier( $this );
		} else {
			$instance = new $concrete_identifier();
		}

		// Store instance if it's a singleton.
		if ( isset( $this->singletons[ $abstract_identifier ] ) ) {
			$this->instances[ $abstract_identifier ] = $instance;
		}

		return $instance;
	}

	/**
	 * Clear resolved instances from the container.
	 *
	 * Calling clear will remove any cached singleton instances so subsequent
	 * make() calls will create fresh instances.
	 *
	 * @return void
	 */
	public function clear(): void {
		$this->instances = array();
	}

	/**
	 * Check if a service is registered
	 *
	 * @param string $abstract_identifier The abstract identifier.
	 * @return bool True if service is registered
	 */
	public function has( string $abstract_identifier ): bool {
		return isset( $this->services[ $abstract_identifier ] );
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
