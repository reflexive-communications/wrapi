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
     * Route ID
     *
     * @var int|null
     */
    protected ?int $id;

    /**
     * Get route from routing table
     *
     * @param int $id Route ID
     *
     * @return array
     */
    protected function getRoute(int $id): array
    {
        return $this->config['routing_table'][$id];
    }

    /**
     * Preprocess form
     *
     * @throws CRM_Core_Exception
     */
    public function preProcess()
    {
        // Get current settings
        $this->config = CRM_Wrapi_ConfigManager::loadConfig();

        // Get route ID from request
        $this->id = CRM_Utils_Request::retrieve('id', 'Positive');
    }
}
