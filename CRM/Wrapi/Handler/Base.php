<?php

/**
 * Base Handler
 *
 * @package  wrapi
 * @author   Sandor Semsey <sandor@es-progress.hu>
 * @license  AGPL-3.0
 */
abstract class CRM_Wrapi_Handler_Base
{
    /**
     * IO Processor
     *
     * @var CRM_Wrapi_Processor_Base
     */
    protected $processor;

    /**
     * Request Data
     *
     * @var null|array
     */
    protected $requestData;

    /**
     * Logging Level
     *
     * @var int
     */
    protected $logLevel;

    /**
     * CRM_Wrapi_Handler_Base constructor.
     *
     * @param CRM_Wrapi_Processor_Base $processor
     * @param int $logging_level
     */
    public function __construct(CRM_Wrapi_Processor_Base $processor, int $logging_level)
    {
        $this->processor = $processor;
        $this->requestData = null;
        $this->logLevel = $logging_level;
    }

    /**
     * Handle request
     *
     * @param $request_data
     *
     * @return mixed
     */
    public function run($request_data): void
    {
        // Get parsed, sanitized request data
        $this->requestData = $request_data;

        // Log incoming request according to logging level
        $this->logIncomingRequest();

        // Validate request data
        $this->validate();

        // Process request
        $this->process();
    }

    /**
     * Validate Request Data
     */
    protected function validate()
    {
    }

    /**
     * Process Request
     */
    protected function process()
    {
    }

    /**
     * Log incoming request
     */
    protected function logIncomingRequest()
    {
        // Log only if log level is below notice
        if ($this->logLevel < 6) {
            return;
        }

        // Compose message
        $message = $_SERVER['REMOTE_ADDR'].' Request received Selector: '.$this->requestData['selector'];

        // Also log request data for debug
        if ($this->logLevel == 7) {
            $data = $this->requestData;

            // Exclude sensitive data from logging
            unset($data['site_key']);
            unset($data['user_key']);
            // Already logged
            unset($data['selector']);

            $message .= " Data: ".serialize($data);
        }

        // Create logger then log
        $file_logger = CRM_Core_Error::createDebugLogger(CRM_Wrapi_ExtensionUtil::SHORT_NAME);
        $file_logger->log("$message\n", $this->logLevel);
    }

    /**
     * Log request processed
     */
    protected function logRequestProcessed()
    {
        // Log only if log level is below notice
        if ($this->logLevel < 6) {
            return;
        }

        // Compose message
        $message = $_SERVER['REMOTE_ADDR'].' Request processed Selector: '.$this->requestData['selector'];

        // Create logger then log
        $file_logger = CRM_Core_Error::createDebugLogger(CRM_Wrapi_ExtensionUtil::SHORT_NAME);
        $file_logger->log("$message\n", $this->logLevel);
    }
}
