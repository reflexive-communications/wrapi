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
     * Run Engine
     */
    public function run(): void
    {
        try {
            // Load configs
            $config_manager = CRM_Wrapi_Factory::createConfigManager();

            // Check request method
            CRM_Wrapi_Authenticator::checkHTTPRequestMethod();

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
                $request_data,
                $router->getRouteLogLevel(),
                $router->getRoutePermissions(),
                $router->getRouteOptions()
            );
            $response = $handler->run();

            // Output response to client
            $processor->output($response);

        } catch (Throwable $ex) {
            // Catch all errors
            $this->error($ex->getMessage());
        }
    }

    /**
     * Log and return error message to client then exit
     *
     * @param string $message Error message
     */
    protected function error($message)
    {
        // Message to log
        $log = "${_SERVER['REMOTE_ADDR']} ${message}";

        // Create logger then log
        $file_logger = CRM_Core_Error::createDebugLogger(CRM_Wrapi_ExtensionUtil::SHORT_NAME);
        $file_logger->err($log);

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
