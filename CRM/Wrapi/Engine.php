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
    }

    /**
     * Run Engine
     */
    public function run(): void
    {
        try {
            // Detect content-type & process input
            $this->processor = CRM_Wrapi_Factory::createProcessor(CRM_Wrapi_Processor_Base::detectContentType());
            $request_data = $this->processor->processInput();

            // Request now parsed --> authenticate
            $authenticator = CRM_Wrapi_Factory::createAuthenticator($this->processor);
            $authenticator->authenticate($request_data['site_key'], $request_data['user_key']);

            // Civi bootstrapped --> route request
            $router = CRM_Wrapi_Factory::createRouter($this->processor);
            $handler_class = $router->route($request_data['action']);

            // Handler found --> create handler & pass request to handler
            $handler = CRM_Wrapi_Factory::createHandler($handler_class, $this->processor);
            $handler->run($request_data);

        } catch (CRM_Core_Exception $ex) {
            http_response_code(500);
            $this->processor->error((string)$ex, true);
        } catch (Throwable $error) {
            http_response_code(500);
            $this->processor->error($error->getMessage(), true);
        }
    }
}
