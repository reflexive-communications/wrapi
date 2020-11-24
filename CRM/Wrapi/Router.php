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
    protected CRM_Wrapi_Processor_Base $processor;

    /**
     * Debug mode
     *
     * @var bool
     */
    protected bool $debugMode;

    /**
     * Routing table
     *
     * @var array
     */
    protected array $routingTable;

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
    }

    /**
     * Routing
     *
     * @param string $action Request action parameter
     *
     * @return mixed
     */
    public function route(string $action)
    {
        $route = $this->searchRoute($action, $this->routingTable);

        // Check route is present
        if (empty($route)) {
            $this->processor->error('Unknown action');
        }

        // Check route is enabled
        $enabled = $route['enabled'] ?? false;
        if (!$enabled) {
            // Verbose error msg in debug mode
            if ($this->debugMode) {
                $message = sprintf('%s action not enabled', $action);
            } else {
                $message = 'Unknown action';
            }
            $this->processor->error($message);
        }

        return $route['handler'] ?? "";
    }

    /**
     * Search route
     *
     * @param string $action
     * @param array $routing_table
     *
     * @return array
     */
    protected function searchRoute(string $action, array $routing_table): array
    {
        if (empty($routing_table)) {
            $this->processor->error('Empty routing table');
        }

        // Search route
        foreach ($routing_table as $id => $route_data) {

            // Check route data
            if (!is_array($route_data)) {
                $this->processor->error(sprintf('Not valid data at route ID: %s', $id));
            }
            if (!isset($route_data['action'])) {
                $this->processor->error(sprintf('Action missing at route ID: %s', $id));
            }

            // Route found --> return route data
            if ($route_data['action'] == $action) {
                $route_data['id'] = $id;

                return $route_data;
            }
        }

        return [];
    }
}
