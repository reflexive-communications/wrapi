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

    /**
     * All configs
     *
     * @var array
     */
    protected array $config;

    /**
     * CRM_Wrapi_ConfigManager constructor.
     */
    public function __construct()
    {
        $this->config = self::DEFAULT_CONFIG;
    }

    /**
     * Create config in DB
     *
     * @return bool success
     */
    public function createConfig(): bool
    {
        // Add config
        Civi::settings()->add($this->config);

        // Check if properly saved
        $cfg = Civi::settings()->get(CRM_Wrapi_Installer::EXTENSION_PREFIX);
        if ($cfg !== $this->config) {
            return false;
        }

        return true;
    }

    /**
     * Remove config from DB
     *
     * @return bool success
     */
    public function removeConfig(): bool
    {
        // Remove config
        Civi::settings()->revert(CRM_Wrapi_Installer::EXTENSION_PREFIX);

        // Check if remove properly
        $cfg = Civi::settings()->get(CRM_Wrapi_Installer::EXTENSION_PREFIX);
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
    public function loadConfig(): void
    {
        // Load configs
        $cfg = Civi::settings()->get(CRM_Wrapi_Installer::EXTENSION_PREFIX);

        // Check if loaded
        if (is_null($cfg) || !is_array($cfg)) {
            throw new CRM_Core_Exception('WrAPI could not load config from database');
        }
    }

    /**
     * Save config
     *
     * @param array $config
     *
     * @return bool success
     */
    public function saveConfig(array $config): bool
    {
        // Save config
        Civi::settings()->set(CRM_Wrapi_Installer::EXTENSION_PREFIX, $config);

        // Check if properly saved
        $saved = Civi::settings()->get(CRM_Wrapi_Installer::EXTENSION_PREFIX);
        if ($saved !== $config) {
            return false;
        }

        return true;
    }

    /**
     * Return current debug mode
     *
     * @return bool
     */
    public function getDebugMode()
    {
        return $this->config['config']['debug'] ?? self::DEFAULT_CONFIG['config']['debug'];
    }
}
