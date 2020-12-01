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
     * Config name
     */
    public const CONFIG_NAME = CRM_Wrapi_ExtensionUtil::SHORT_NAME.'_config';

    /**
     * Default configuration
     */
    public const DEFAULT_CONFIG = [
        self::CONFIG_NAME => [
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
     * @var array|null
     */
    protected $config;

    /**
     * CRM_Wrapi_ConfigManager constructor.
     */
    public function __construct()
    {
        $this->config = self::DEFAULT_CONFIG[self::CONFIG_NAME];
    }

    /**
     * Create config in DB
     *
     * @return bool success
     */
    public function createConfig(): bool
    {
        // Add config
        Civi::settings()->add(self::DEFAULT_CONFIG);

        // Check if properly saved
        $cfg = Civi::settings()->get(self::CONFIG_NAME);
        if ($cfg !== $this->config) {
            return false;
        }

        // Update configs
        $this->config = $cfg;

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
        Civi::settings()->revert(self::CONFIG_NAME);

        // Check if remove properly
        $cfg = Civi::settings()->get(self::CONFIG_NAME);
        if (!is_null($cfg)) {
            return false;
        }

        // Update configs
        $this->config = $cfg;

        return true;
    }

    /**
     * Load configs
     *
     * @throws CRM_Core_Exception
     */
    public function loadConfig(): void
    {
        // Load configs
        $cfg = Civi::settings()->get(self::CONFIG_NAME);

        // Check if loaded
        if (is_null($cfg) || !is_array($cfg)) {
            throw new CRM_Core_Exception('WrAPI could not load config from database');
        }

        // Update configs
        $this->config = $cfg;
    }

    /**
     * Save config
     *
     * @param array $config Config to save
     *
     * @return bool success
     */
    public function saveConfig(array $config): bool
    {
        // Save config
        Civi::settings()->set(self::CONFIG_NAME, $config);

        // Check if properly saved
        $saved = Civi::settings()->get(self::CONFIG_NAME);
        if ($saved !== $config) {
            return false;
        }

        // Update configs
        $this->config = $saved;

        return true;
    }

    /**
     * Return current debug mode
     *
     * @return bool Current Debug mode
     */
    public function getDebugMode(): bool
    {
        $debug = $this->config['config']['debug'] ?? self::DEFAULT_CONFIG['config']['debug'];
        if (!is_bool($debug)) {
            return self::DEFAULT_CONFIG['config']['debug'];
        }

        return $debug;
    }

    /**
     * Return current routing table
     *
     * @return array Current Routing table
     */
    public function getRoutingTable(): array
    {
        $routing_table = $this->config['routing_table'] ?? [];

        if (!is_array($routing_table)) {
            return [];
        }

        return $routing_table;
    }

    /**
     * Return current configs
     *
     * @return array Current configs
     */
    public function getAllConfig(): array
    {
        return $this->config ?? [];
    }
}
