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
     * CRM_Wrapi_Router constructor
     *
     * @param CRM_Wrapi_Processor_Base $processor
     */
    public function __construct(CRM_Wrapi_Processor_Base $processor)
    {
        $this->processor = $processor;
    }

    /**
     * Routing
     *
     * @param string $action Request action parameter
     *
     * @return mixed
     *
     * @throws CRM_Core_Exception
     */
    public function route(string $action)
    {
        $routing_table = $this->loadRoutingTable();

        $route = $this->searchRoute($action, $routing_table);

        if (empty($route)) {
            $this->processor->error('Unknown action');
        }

        return $route['handler'] ?? "";
    }

    /**
     * Load routing table from DB
     *
     * @throws CRM_Core_Exception
     */
    protected function loadRoutingTable()
    {
        $config = CRM_Wrapi_ConfigManager::loadConfig();

        return $config['routing_table'] ?? [];
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
