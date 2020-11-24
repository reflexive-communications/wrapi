<?php

/**
 * Engine
 *
 * @package  wrapi
 * @author   Sandor Semsey <sandor@es-progress.hu>
 * @license  AGPL-3.0
 */
class CRM_Wrapi_Engine
{
    /**
     * IO Processor
     *
     * @var CRM_Wrapi_Processor_Base|null $processor
     */
    protected ?CRM_Wrapi_Processor_Base $processor;

    /**
     * CRM_Wrapi_Engine constructor
     */
    public function __construct()
    {
        $this->processor = null;
        $this->authenticator = null;
        $this->router = null;
        $this->requestData = null;
    }

    /**
     * Run Engine
     */
    public function run(): void
    {
        try {
            // Detect content-type & process input
            $this->processor = $this->createProcessor(CRM_Wrapi_Processor_Base::detectContentType());
            $request_data = $this->processor->processInput();

            // Request now parsed --> authenticate
            $authenticator = $this->createAuthenticator($this->processor);
            $authenticator->authenticate($request_data['site_key'], $request_data['user_key']);

            // Civi bootstrapped --> route request
            $router = $this->createRouter($this->processor);
            $handler_class = $router->route($request_data['action']);

            // Handler found --> create handler & pass request to handler
            $handler = $this->createHandler($handler_class);
            $handler->handle($request_data);

        } catch (CRM_Core_Exception $ex) {
            http_response_code(500);
            $this->processor->error((string)$ex, true);
        } catch (Throwable $error) {
            http_response_code(500);
            $this->processor->error($error->getMessage(), true);
        }
    }

    /**
     * Create Processor
     *
     * @param string $processor_class Processor class name
     *
     * @return CRM_Wrapi_Processor_Base
     */
    protected function createProcessor(string $processor_class): CRM_Wrapi_Processor_Base
    {
        return new $processor_class();
    }

    /**
     * Create Authenticator
     *
     * @param CRM_Wrapi_Processor_Base $processor
     *
     * @return CRM_Wrapi_Authenticator
     */
    protected function createAuthenticator(CRM_Wrapi_Processor_Base $processor): CRM_Wrapi_Authenticator
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
    protected function createRouter(CRM_Wrapi_Processor_Base $processor): CRM_Wrapi_Router
    {
        return new CRM_Wrapi_Router($processor);
    }

    protected function createHandler(string $handler_class): CRM_Wrapi_Handler_Base
    {
        $handler = null;
        if (!class_exists($handler_class)) {
            $this->processor->error(sprintf('Handler: %s not exists', $handler_class));
        }
        $handler = new $handler_class($this->processor);

        if (!($handler instanceof CRM_Wrapi_Handler_Base)) {
            $this->processor->error(sprintf('Not handler: %s', $handler_class));
        }

        return $handler;
    }
}
