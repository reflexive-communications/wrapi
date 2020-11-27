<?php

/**
 * Factory
 *
 * @package  wrapi
 * @author   Sandor Semsey <sandor@es-progress.hu>
 * @license  AGPL-3.0
 */
class CRM_Wrapi_Factory
{
    /**
     * Create Config Manager
     *
     * @return CRM_Wrapi_ConfigManager
     *
     * @throws CRM_Core_Exception
     */
    public static function createConfigManager(): CRM_Wrapi_ConfigManager
    {
        // Instantiate
        $config_manager = new CRM_Wrapi_ConfigManager();

        // Load settings
        $config_manager->loadConfig();

        return $config_manager;
    }

    /**
     * Create Processor
     *
     * @param string $processor_class Processor class name
     *
     * @return CRM_Wrapi_Processor_Base
     *
     * @throws CRM_Core_Exception
     */
    public static function createProcessor(string $processor_class): CRM_Wrapi_Processor_Base
    {
        self::checkClass($processor_class);
        $processor = new $processor_class();
        self::checkInstance($processor, CRM_Wrapi_Processor_Base::class);

        return $processor;
    }

    /**
     * Create Authenticator
     *
     * @param CRM_Wrapi_Processor_Base $processor
     * @param bool $debug_mode
     *
     * @return CRM_Wrapi_Authenticator
     */
    public static function createAuthenticator(
        CRM_Wrapi_Processor_Base $processor,
        bool $debug_mode
    ): CRM_Wrapi_Authenticator {
        return new CRM_Wrapi_Authenticator($processor, $debug_mode);
    }

    /**
     * Create Router
     *
     * @param CRM_Wrapi_Processor_Base $processor
     * @param bool $debug_mode
     * @param array $routing_table
     *
     * @return CRM_Wrapi_Router
     */
    public static function createRouter(
        CRM_Wrapi_Processor_Base $processor,
        bool $debug_mode,
        array $routing_table
    ): CRM_Wrapi_Router {
        return new CRM_Wrapi_Router($processor, $debug_mode, $routing_table);
    }

    /**
     * Create Handler
     *
     * @param string $handler_class
     * @param CRM_Wrapi_Processor_Base $processor
     * @param int $logging_level
     *
     * @return CRM_Wrapi_Handler_Base
     *
     * @throws CRM_Core_Exception
     */
    public static function createHandler(
        string $handler_class,
        CRM_Wrapi_Processor_Base $processor,
        int $logging_level
    ): CRM_Wrapi_Handler_Base {
        self::checkClass($handler_class);
        $handler = new $handler_class($processor, $logging_level);
        self::checkInstance($handler, CRM_Wrapi_Handler_Base::class);

        return $handler;
    }

    /**
     * Check if class exists
     *
     * @param string $class
     *
     * @throws CRM_Core_Exception
     */
    protected static function checkClass(string $class)
    {
        if (!class_exists($class)) {
            throw new CRM_Core_Exception(sprintf('Class %s not exists', $class));
        }
    }

    /**
     * Check instance type
     *
     * @param $instance
     * @param string $class
     *
     * @throws CRM_Core_Exception
     */
    protected static function checkInstance($instance, string $class)
    {
        if (!($instance instanceof $class)) {
            throw new CRM_Core_Exception(sprintf('Class %s is not an instance of %s', get_class($instance), $class));
        }
    }
}
