<?php

/**
 * Config Manager
 *
 * @package  wrapi
 * @author   Sandor Semsey <sandor@es-progress.hu>
 * @license  AGPL-3.0
 */
class CRM_Wrapi_ConfigManager
{
    /**
     * Default configuration
     */
    public const DEFAULT_CONFIG = [
        CRM_Wrapi_Upgrader::EXTENSION_PREFIX => [
            'next_id' => 1,
            'routing_table' => [],
            'config' => [
                'debug' => false,
            ],
        ],
    ];

    /**
     * Create config in DB
     *
     * @return bool success
     */
    public static function createConfig(): bool
    {
        // Add config
        Civi::settings()->add(self::DEFAULT_CONFIG);

        // Check if properly saved
        $cfg = Civi::settings()->get(CRM_Wrapi_Upgrader::EXTENSION_PREFIX);
        if ($cfg !== self::DEFAULT_CONFIG[CRM_Wrapi_Upgrader::EXTENSION_PREFIX]) {
            return false;
        }

        return true;
    }

    /**
     * Remove config from DB
     *
     * @return bool success
     */
    public static function removeConfig(): bool
    {
        // Remove config
        Civi::settings()->revert(CRM_Wrapi_Upgrader::EXTENSION_PREFIX);

        // Check if remove properly
        $cfg = Civi::settings()->get(CRM_Wrapi_Upgrader::EXTENSION_PREFIX);
        if (!is_null($cfg)) {
            return false;
        }

        return true;
    }

    /**
     * Load configs
     *
     * @return array Config
     *
     * @throws CRM_Core_Exception
     */
    public static function loadConfig(): array
    {
        // Load configs
        $config = Civi::settings()->get(CRM_Wrapi_Upgrader::EXTENSION_PREFIX);

        // Check if loaded
        if (is_null($config) || !is_array($config)) {
            throw new CRM_Core_Exception('WrAPI could not load config from database.');
        }

        return $config;
    }

    /**
     * Save config
     *
     * @param array $config
     *
     * @return bool success
     */
    public static function saveConfig(array $config): bool
    {
        // Save config
        Civi::settings()->set(CRM_Wrapi_Upgrader::EXTENSION_PREFIX, $config);

        // Check if properly saved
        $saved = Civi::settings()->get(CRM_Wrapi_Upgrader::EXTENSION_PREFIX);
        if ($saved !== $config) {
            return false;
        }

        return true;
    }
}
