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
     * Routing table
     *
     * @var array
     */
    protected $routingTable;

    /**
     * Debug mode
     *
     * @var bool
     */
    protected $debugMode;

    /**
     * Data associated to selected route
     *
     * @var array
     */
    protected $selectedRoute;

    /**
     * CRM_Wrapi_Router constructor
     *
     * @param array $routing_table Routing table
     * @param bool $debug_mode Debug mode
     */
    public function __construct(array $routing_table, bool $debug_mode)
    {
        $this->routingTable = $routing_table;
        $this->debugMode = $debug_mode;
        $this->selectedRoute = [];
    }

    /**
     * Routing
     *
     * @param string $selector Request selector parameter
     *
     * @throws CRM_Core_Exception
     */
    public function route(string $selector): void
    {
        $this->selectedRoute = $this->searchRoute($selector);

        // Check route is present
        if (empty($this->selectedRoute)) {
            throw new CRM_Core_Exception('Unknown selector');
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
            throw new CRM_Core_Exception($message);
        }
    }

    /**
     * Search particular route from all routes
     *
     * @param string $selector Selector to search
     *
     * @return array Route data
     *
     * @throws CRM_Core_Exception
     */
    protected function searchRoute(string $selector): array
    {
        if (empty($this->routingTable)) {
            throw new CRM_Core_Exception('Empty routing table');
        }

        // Search route
        foreach ($this->routingTable as $id => $route_data) {

            // Check route data
            if (!is_array($route_data)) {
                throw new CRM_Core_Exception(sprintf('Not valid data at route ID: %s', $id));
            }
            if (!isset($route_data['selector'])) {
                throw new CRM_Core_Exception(sprintf('Selector missing at route ID: %s', $id));
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
     * @return string Handler class name
     *
     * @throws CRM_Core_Exception
     */
    public function getRouteHandler(): string
    {
        $handler = $this->selectedRoute['handler'] ?? "";

        if (empty($handler) || !CRM_Utils_Rule::string($handler)) {
            throw new CRM_Core_Exception('Not valid handler');
        }

        return $handler;
    }

    /**
     * Get logging level for selected route
     *
     * @return int Logging level
     *
     * @throws CRM_Core_Exception
     */
    public function getRouteLogLevel(): int
    {
        $log_level = $this->selectedRoute['log'] ?? PEAR_LOG_ERR;

        if (!CRM_Utils_Rule::positiveInteger($log_level) || $log_level < PEAR_LOG_NONE || $log_level > PEAR_LOG_DEBUG) {
            throw new CRM_Core_Exception('Not valid logging level');
        }

        return $log_level;
    }

    /**
     * Get permissions for selected route
     *
     * @return array Route permissions
     *
     * @throws CRM_Core_Exception
     */
    public function getRoutePermissions(): array
    {
        $permissions = $this->selectedRoute['perms'] ?? [];

        if (!is_array($permissions)) {
            throw new CRM_Core_Exception('Not valid permissions format');
        }

        return $permissions;
    }
}
