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
        CRM_Wrapi_Installer::EXTENSION_PREFIX => [
            'next_id' => 1,
            'routing_table' => [],
            'config' => [
                'debug' => false,
            ],
        ],
    ];

    protected static bool $debugMode = false;

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
        $cfg = Civi::settings()->get(CRM_Wrapi_Installer::EXTENSION_PREFIX);
        if ($cfg !== self::DEFAULT_CONFIG[CRM_Wrapi_Installer::EXTENSION_PREFIX]) {
            return false;
        }

        self::updateDebugMode($cfg);

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
        Civi::settings()->revert(CRM_Wrapi_Installer::EXTENSION_PREFIX);

        // Check if remove properly
        $cfg = Civi::settings()->get(CRM_Wrapi_Installer::EXTENSION_PREFIX);
        if (!is_null($cfg)) {
            return false;
        }

        self::updateDebugMode($cfg);

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
        $cfg = Civi::settings()->get(CRM_Wrapi_Installer::EXTENSION_PREFIX);

        // Check if loaded
        if (is_null($cfg) || !is_array($cfg)) {
            throw new CRM_Core_Exception('WrAPI could not load config from database');
        }

        self::updateDebugMode($cfg);

        return $cfg;
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
        Civi::settings()->set(CRM_Wrapi_Installer::EXTENSION_PREFIX, $config);

        // Check if properly saved
        $saved = Civi::settings()->get(CRM_Wrapi_Installer::EXTENSION_PREFIX);
        if ($saved !== $config) {
            return false;
        }

        self::updateDebugMode($saved);

        return true;
    }

    /**
     * Update debug mode config
     *
     * @param array $config
     */
    protected static function updateDebugMode(array $config)
    {
        // Check is present
        $mode = $config['config']['debug'] ?? self::DEFAULT_CONFIG['config']['debug'];
        // Update in instance
        self::$debugMode = $mode;
    }

    /**
     * Return current debug mode
     *
     * @return bool
     */
    public static function getDebugMode()
    {
        return self::$debugMode;
    }
}
