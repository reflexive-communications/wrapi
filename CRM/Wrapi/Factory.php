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
     *
     * @return CRM_Wrapi_Authenticator
     */
    public static function createAuthenticator(CRM_Wrapi_Processor_Base $processor): CRM_Wrapi_Authenticator
    {
        return new CRM_Wrapi_Authenticator($processor);
    }

    /**
     * Create Router
     *
     * @param CRM_Wrapi_Processor_Base $processor
     *
     * @return CRM_Wrapi_Router
     */
    public static function createRouter(CRM_Wrapi_Processor_Base $processor): CRM_Wrapi_Router
    {
        return new CRM_Wrapi_Router($processor);
    }

    /**
     * Create Handler
     *
     * @param string $handler_class
     * @param CRM_Wrapi_Processor_Base $processor
     *
     * @return CRM_Wrapi_Handler_Base
     *
     * @throws CRM_Core_Exception
     */
    public static function createHandler(
        string $handler_class,
        CRM_Wrapi_Processor_Base $processor
    ): CRM_Wrapi_Handler_Base {
        self::checkClass($handler_class);
        $handler = new $handler_class($processor);
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
