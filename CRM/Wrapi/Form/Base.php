<?php

/**
 * WrAPI Base Form controller
 *
 * @see https://docs.civicrm.org/dev/en/latest/framework/quickform/
 *
 * @package  wrapi
 * @author   Sandor Semsey <sandor@es-progress.hu>
 * @license  AGPL-3.0
 */
class CRM_Wrapi_Form_Base extends CRM_Core_Form
{
    /**
     * Current WrAPI config
     *
     * @var array
     */
    protected array $config;

    /**
     * Config Manager
     *
     * @var CRM_Wrapi_ConfigManager
     */
    protected CRM_Wrapi_ConfigManager $configManager;

    /**
     * Route ID
     *
     * @var int|null
     */
    protected ?int $id;

    /**
     * Form action
     *
     * @var int|null
     */
    protected ?int $action;

    /**
     * Get route from routing table
     *
     * @param int $id Route ID
     *
     * @return array
     */
    protected function getRoute(int $id): array
    {
        $route = $this->config['routing_table'][$id];

        if (!is_array($route)) {
            return [];
        }

        return $route;
    }

    /**
     * Preprocess form
     *
     * @throws CRM_Core_Exception
     */
    public function preProcess()
    {
        // Get current settings
        $this->configManager = CRM_Wrapi_Factory::createConfigManager();
        $this->config = $this->configManager->getAllConfig();

        // Get route ID from request
        $this->id = CRM_Utils_Request::retrieve('id', 'Positive');
    }
}
