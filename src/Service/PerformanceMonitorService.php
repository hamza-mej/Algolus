<?php

namespace App\Service;

use Psr\Log\LoggerInterface;

class PerformanceMonitorService
{
    private array $timers = [];
    private array $metrics = [];

    public function __construct(private LoggerInterface $logger) {}

    /**
     * Start performance timer
     */
    public function start(string $name): void
    {
        $this->timers[$name] = [
            'start' => microtime(true),
            'memory_start' => memory_get_usage(true),
        ];
    }

    /**
     * Stop timer and record metric
     */
    public function stop(string $name): float
    {
        if (!isset($this->timers[$name])) {
            return 0;
        }

        $duration = microtime(true) - $this->timers[$name]['start'];
        $memory = memory_get_usage(true) - $this->timers[$name]['memory_start'];

        $this->metrics[$name] = [
            'duration' => $duration,
            'memory' => $memory,
            'timestamp' => new \DateTimeImmutable(),
        ];

        unset($this->timers[$name]);

        // Log slow operations (> 1 second)
        if ($duration > 1.0) {
            $this->logger->warning("Slow operation: {$name} took {$duration}s", [
                'operation' => $name,
                'duration' => $duration,
                'memory' => $memory,
            ]);
        }

        return $duration;
    }

    /**
     * Get all metrics
     */
    public function getMetrics(): array
    {
        return $this->metrics;
    }

    /**
     * Get metric for specific operation
     */
    public function getMetric(string $name): ?array
    {
        return $this->metrics[$name] ?? null;
    }

    /**
     * Reset metrics
     */
    public function reset(): void
    {
        $this->metrics = [];
        $this->timers = [];
    }

    /**
     * Get average duration
     */
    public function getAverageDuration(string $name): float
    {
        $durations = array_filter($this->metrics, fn($m) => $m['name'] === $name);
        if (empty($durations)) {
            return 0;
        }

        $total = array_sum(array_column($durations, 'duration'));
        return $total / count($durations);
    }

    /**
     * Log database query
     */
    public function logQuery(string $query, array $params, float $duration): void
    {
        if ($duration > 0.5) {
            $this->logger->warning('Slow database query', [
                'query' => $query,
                'params' => $params,
                'duration' => $duration,
            ]);
        }
    }

    /**
     * Log API call
     */
    public function logApiCall(string $method, string $path, int $statusCode, float $duration): void
    {
        $level = $statusCode >= 400 ? 'error' : 'info';
        $this->logger->log($level, "API {$method} {$path} - {$statusCode}", [
            'method' => $method,
            'path' => $path,
            'status' => $statusCode,
            'duration' => $duration,
        ]);
    }

    /**
     * Get performance report
     */
    public function getReport(): array
    {
        $metrics = $this->metrics;
        $slowestOps = [];

        foreach ($metrics as $name => $data) {
            $slowestOps[] = [
                'name' => $name,
                'duration' => $data['duration'],
                'memory' => $data['memory'],
            ];
        }

        usort($slowestOps, fn($a, $b) => $b['duration'] <=> $a['duration']);

        return [
            'totalMetrics' => count($metrics),
            'averageDuration' => array_sum(array_column($metrics, 'duration')) / max(1, count($metrics)),
            'totalMemory' => array_sum(array_column($metrics, 'memory')),
            'slowestOperations' => array_slice($slowestOps, 0, 10),
        ];
    }
}
