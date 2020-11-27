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
    protected $processor;

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
            // Load configs
            $config_manager = CRM_Wrapi_Factory::createConfigManager();

            // Detect content-type & process input
            $processor = CRM_Wrapi_Factory::createProcessor(CRM_Wrapi_Processor_Base::detectContentType());
            $request_data = $processor->processInput();

            // Request now parsed --> authenticate
            $authenticator = CRM_Wrapi_Factory::createAuthenticator($config_manager->getDebugMode());
            $authenticator->authenticate($request_data['site_key'], $request_data['user_key']);

            // Civi bootstrapped --> route request
            $router = CRM_Wrapi_Factory::createRouter(
                $config_manager->getRoutingTable(),
                $config_manager->getDebugMode()
            );
            $router->route($request_data['selector']);

            // Handler found --> create handler & pass request to handler
            $handler = CRM_Wrapi_Factory::createHandler(
                $router->getRouteHandler(),
                $processor,
                $router->getRouteLogLevel()
            );
            $handler->run($request_data);

        } catch (CRM_Core_Exception $ex) {
            // Only catch known exceptions.
            // Let the rest fall out.
            $this->error($ex->getMessage());
        }
    }

    /**
     * Log and optionally return error message to client then exit
     *
     * @param string $message Error message
     */
    protected function error($message)
    {
        // Write to log
        CRM_Core_Error::debug_log_message(
            $message,
            false,
            CRM_Wrapi_ExtensionUtil::SHORT_NAME,
            PEAR_LOG_ERR
        );

        // Set response headers
        http_response_code(500);
        header('Content-Type: application/json');

        // Response body
        $response = [
            'error' => true,
            'message' => $message,
        ];

        // Send response and exit
        echo json_encode($response);
        CRM_Utils_System::civiExit();
    }
}
