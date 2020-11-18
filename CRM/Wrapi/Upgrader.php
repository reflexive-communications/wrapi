<?php

use CRM_Wrapi_ExtensionUtil as E;

/**
 * Collection of upgrade steps.
 */
class CRM_Wrapi_Upgrader extends CRM_Wrapi_Upgrader_Base
{
    /**
     * Extension prefix
     */
    public const EXTENSION_PREFIX = 'extension_'.E::SHORT_NAME;

    /**
     * Default settings
     */
    public const DEFAULT_SETTINGS = [
        self::EXTENSION_PREFIX => [
            'routing_table' => [],
            'debug' => false,
        ],
    ];

    /**
     * Install process
     */
    public function install(): void
    {
        Civi::settings()->add(self::DEFAULT_SETTINGS);
    }

    /**
     * Uninstall process
     */
    public function uninstall(): void
    {
        Civi::settings()->revert(self::EXTENSION_PREFIX);
    }
}
