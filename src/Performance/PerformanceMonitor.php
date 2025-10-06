<?php
/**
 * Performance Monitor
 *
 * This file contains the PerformanceMonitor class which tracks and monitors
 * plugin performance metrics including execution time, memory usage, and
 * database query optimization.
 *
 * @package PE Category Filter
 * @since   2.0.0
 */

namespace PavelEspinal\WpPlugins\PECategoryFilter\Performance;

/**
 * Performance Monitor
 *
 * Tracks and monitors plugin performance metrics
 *
 * @package PE Category Filter
 * @since 2.0.0
 */
class PerformanceMonitor {

	/**
	 * Performance metrics
	 *
	 * @var array<string, mixed>
	 */
	private array $metrics = array();

	/**
	 * Start time for timing operations
	 *
	 * @var float
	 */
	private float $startTime;

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->startTime = microtime( true );
	}

	/**
	 * Start timing an operation
	 *
	 * @param string $operation Operation name.
	 * @return void
	 */
	public function startTimer( string $operation ): void {
		$this->metrics[ $operation ] = array(
			'start_time'   => microtime( true ),
			'memory_start' => memory_get_usage( true ),
		);
	}

	/**
	 * End timing an operation
	 *
	 * @param string $operation Operation name.
	 * @return array<string, mixed> Performance data
	 */
	public function endTimer( string $operation ): array {
		if ( ! isset( $this->metrics[ $operation ] ) ) {
			return array();
		}

		$endTime   = microtime( true );
		$endMemory = memory_get_usage( true );

		$data = array(
			'operation'      => $operation,
			'execution_time' => $endTime - $this->metrics[ $operation ]['start_time'],
			'memory_used'    => $endMemory - $this->metrics[ $operation ]['memory_start'],
			'peak_memory'    => memory_get_peak_usage( true ),
		);

		$this->metrics[ $operation ] = $data;
		return $data;
	}

	/**
	 * Get all performance metrics
	 *
	 * @return array<string, mixed> All metrics
	 */
	public function getMetrics(): array {
		return $this->metrics;
	}

	/**
	 * Get total execution time
	 *
	 * @return float Total execution time in seconds
	 */
	public function getTotalExecutionTime(): float {
		return microtime( true ) - $this->startTime;
	}

	/**
	 * Get peak memory usage
	 *
	 * @return int Peak memory usage in bytes
	 */
	public function getPeakMemoryUsage(): int {
		return memory_get_peak_usage( true );
	}

	/**
	 * Log performance data
	 *
	 * @param string               $operation Operation name.
	 * @param array<string, mixed> $data Performance data.
	 * @return void
	 */
	public function logPerformance( string $operation, array $data ): void {
		if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
			// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log -- Performance debugging is conditional on WP_DEBUG
			error_log(
				sprintf(
					'PECF Performance - %s: %s seconds, %s bytes memory',
					$operation,
					number_format( $data['execution_time'], 4 ),
					number_format( $data['memory_used'] )
				)
			);
		}
	}

	/**
	 * Get database query count
	 *
	 * @return int Number of database queries
	 */
	public function getQueryCount(): int {
		global $wpdb;
		return $wpdb->num_queries;
	}

	/**
	 * Get cache hit rate
	 *
	 * @return float Cache hit rate percentage
	 */
	public function getCacheHitRate(): float {
		$cacheHits   = wp_cache_get( 'pecf_cache_hits', 'pecf' ) ?? 0;
		$cacheMisses = wp_cache_get( 'pecf_cache_misses', 'pecf' ) ?? 0;
		$total       = $cacheHits + $cacheMisses;

		if ( 0 === $total ) {
			return 0.0;
		}

		return ( $cacheHits / $total ) * 100;
	}

	/**
	 * Record cache hit
	 *
	 * @return void
	 */
	public function recordCacheHit(): void {
		$hits = wp_cache_get( 'pecf_cache_hits', 'pecf' ) ?? 0;
		wp_cache_set( 'pecf_cache_hits', $hits + 1, 'pecf', HOUR_IN_SECONDS );
	}

	/**
	 * Record cache miss
	 *
	 * @return void
	 */
	public function recordCacheMiss(): void {
		$misses = wp_cache_get( 'pecf_cache_misses', 'pecf' ) ?? 0;
		wp_cache_set( 'pecf_cache_misses', $misses + 1, 'pecf', HOUR_IN_SECONDS );
	}

	/**
	 * Generate performance report
	 *
	 * @return array<string, mixed> Performance report
	 */
	public function generateReport(): array {
		return array(
			'total_execution_time' => $this->getTotalExecutionTime(),
			'peak_memory_usage'    => $this->getPeakMemoryUsage(),
			'database_queries'     => $this->getQueryCount(),
			'cache_hit_rate'       => $this->getCacheHitRate(),
			'operations'           => $this->getMetrics(),
		);
	}
}
