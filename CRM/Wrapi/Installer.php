<?php

use CRM_Wrapi_ExtensionUtil as E;

/**
 * Installer
 *
 * @package  wrapi
 * @author   Sandor Semsey <sandor@es-progress.hu>
 * @license  AGPL-3.0
 */
class CRM_Wrapi_Installer extends CRM_Wrapi_Upgrader_Base
{
    /**
     * Install process
     *
     * @throws CRM_Core_Exception
     */
    public function install(): void
    {
        $config_manager = new CRM_Wrapi_ConfigManager();
        // Create default configs
        if (!$config_manager->createConfig()) {
            throw new CRM_Core_Exception(ts('WrAPI could not create configs in database'));
        }
    }

    /**
     * Uninstall process
     *
     * @throws CRM_Core_Exception
     */
    public function uninstall(): void
    {
        $config_manager = new CRM_Wrapi_ConfigManager();
        // Remove configs
        if (!$config_manager->removeConfig()) {
            throw new CRM_Core_Exception(ts('WrAPI could not remove configs from database'));
        }
    }
}
