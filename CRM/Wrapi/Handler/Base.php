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
     * Required permissions
     *
     * @var string
     */
    protected $permissions;

    /**
     * CRM_Wrapi_Handler_Base constructor.
     *
     * @param array|null $request_data Request data
     * @param int $logging_level Logging level
     * @param string $permissions Required permissions
     */
    public function __construct(?array $request_data, int $logging_level, string $permissions)
    {
        $this->requestData = $request_data;
        $this->logLevel = $logging_level;
        $this->permissions = $permissions;
    }

    /**
     * Handle request
     *
     * @return mixed
     *
     * @throws CRM_Core_Exception
     */
    public function run()
    {
        // Check permissions
        $this->checkPermissions();

        // Log incoming request according to logging level
        $this->logIncomingRequest();

        // Validate request data
        $this->validate();

        // Process request
        return $this->process();
    }

    /**
     * Validate Request Data
     */
    abstract protected function validate();

    /**
     * Process Request
     */
    abstract protected function process();

    /**
     * Check if current user (based on user-key) has required permissions
     *
     * @throws CRM_Core_Exception
     */
    protected function checkPermissions()
    {
        if (empty($this->permissions)) {
            return;
        }

        $permissions = explode(',', $this->permissions);

        if (!CRM_Core_Permission::check($permissions)) {
            throw new CRM_Core_Exception(sprintf('Required permission missing: %s', $this->permissions));
        }
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
        $message = $_SERVER['REMOTE_ADDR'].' Request received  Selector: '.$this->requestData['selector'];

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
        $file_logger->log($message, $this->logLevel);
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
        $file_logger->log($message, $this->logLevel);
    }
}
