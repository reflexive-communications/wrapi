<?php

/**
 * Wrapi Router
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
     * @var array|null
     */
    protected ?array $routingTable;

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
        $this->routingTable = null;
        $this->processor = $processor;
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
        $this->loadRoutingTable();

        $route = $this->routingTable[$action] ?? null;

        if (is_null($route) || empty($route)) {
            $this->processor->error('Unknown action.');
        }

        return $route['handler'];
    }

    /**
     * Load routing table from DB
     */
    protected function loadRoutingTable(): void
    {
        $settings = Civi::settings()->get(CRM_Wrapi_Upgrader::EXTENSION_PREFIX);
        $this->routingTable = $settings['routing_table'];
    }
}
