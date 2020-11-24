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
     * Extension prefix
     */
    public const EXTENSION_PREFIX = 'extension_'.E::SHORT_NAME;

    /**
     * Install process
     *
     * @throws CRM_Core_Exception
     */
    public function install(): void
    {
        // Create default configs
        if (!CRM_Wrapi_ConfigManager::createConfig()) {
            throw new CRM_Core_Exception(ts('WrAPI could not create configs in database'));
        };
    }

    /**
     * Uninstall process
     *
     * @throws CRM_Core_Exception
     */
    public function uninstall(): void
    {
        // Remove configs
        if (!CRM_Wrapi_ConfigManager::removeConfig()) {
            throw new CRM_Core_Exception(ts('WrAPI could not remove configs from database'));
        };
    }
}
