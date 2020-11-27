<?php

/**
 * Router
 *
 * @package  wrapi
 * @author   Sandor Semsey <sandor@es-progress.hu>
 * @license  AGPL-3.0
 */
class CRM_Wrapi_Router
{
    /**
     * IO processor
     *
     * @var CRM_Wrapi_Processor_Base
     */
    protected $processor;

    /**
     * Debug mode
     *
     * @var bool
     */
    protected $debugMode;

    /**
     * Routing table
     *
     * @var array
     */
    protected $routingTable;

    /**
     * Data associated to selected route
     *
     * @var array
     */
    protected $selectedRoute;

    /**
     * CRM_Wrapi_Router constructor
     *
     * @param CRM_Wrapi_Processor_Base $processor
     * @param bool $debug_mode
     * @param array $routing_table
     */
    public function __construct(CRM_Wrapi_Processor_Base $processor, bool $debug_mode, array $routing_table)
    {
        $this->processor = $processor;
        $this->debugMode = $debug_mode;
        $this->routingTable = $routing_table;
        $this->selectedRoute = [];
    }

    /**
     * Routing
     *
     * @param string $selector Request selector parameter
     *
     * @return mixed
     */
    public function route(string $selector)
    {
        $this->selectedRoute = $this->searchRoute($selector);

        // Check route is present
        if (empty($this->selectedRoute)) {
            $this->processor->error('Unknown selector');
        }

        // Check route is enabled
        $enabled = $this->selectedRoute['enabled'] ?? false;
        if (!$enabled) {
            // Verbose error msg in debug mode
            if ($this->debugMode) {
                $message = sprintf('%s selector not enabled', $selector);
            } else {
                $message = 'Unknown selector';
            }
            $this->processor->error($message);
        }

        return $route['handler'] ?? "";
    }

    /**
     * Search route
     *
     * @param string $selector
     *
     * @return array
     */
    protected function searchRoute(string $selector): array
    {
        if (empty($this->routingTable)) {
            $this->processor->error('Empty routing table');
        }

        // Search route
        foreach ($this->routingTable as $id => $route_data) {

            // Check route data
            if (!is_array($route_data)) {
                $this->processor->error(sprintf('Not valid data at route ID: %s', $id));
            }
            if (!isset($route_data['action'])) {
                $this->processor->error(sprintf('Action missing at route ID: %s', $id));
            }

            // Route found --> return route data
            if ($route_data['selector'] == $selector) {
                $route_data['id'] = $id;

                return $route_data;
            }
        }

        return [];
    }

    /**
     * Get Handler class for selected route
     *
     * @return string
     *
     * @throws CRM_Core_Exception
     */
    public function getRouteHandler(): string
    {
        $handler = $this->selectedRoute['handler'] ?? "";

        if (!is_string($handler) || empty($handler)) {
            throw new CRM_Core_Exception('Not valid handler');
        }

        return $handler;
    }

    /**
     * Get Logging Level for selected route
     *
     * @return int
     *
     * @throws CRM_Core_Exception
     */
    public function getRouteLogLevel(): int
    {
        $log_level = $this->selectedRoute['log'] ?? PEAR_LOG_ERR;

        if (!is_int($log_level)) {
            throw new CRM_Core_Exception('Not valid logging level');
        }

        return $log_level;
    }
}
