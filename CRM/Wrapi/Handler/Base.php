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
     * @var array
     */
    protected $permissions;

    /**
     * CRM_Wrapi_Handler_Base constructor.
     *
     * @param array|null $request_data Request data
     * @param int $logging_level Logging level
     * @param array $permissions Required permissions
     */
    public function __construct(?array $request_data, int $logging_level, array $permissions)
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
     * Check if current user (based on user-key) has required permissions
     *
     * @throws CRM_Core_Exception
     */
    protected function checkPermissions()
    {
        foreach ($this->permissions as $permission) {
            if (!CRM_Core_Permission::check($permission)) {
                throw new CRM_Core_Exception(sprintf('Required permission missing: %s', $permission));
            }
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
     * Validate Request Data
     *
     * @throws CRM_Core_Exception
     */
    protected function validate(): void
    {
        // Loop through input rules
        foreach ($this->inputRules() as $input => $rule) {
            // Get rules
            $type = $rule['type'] ?? "";
            $name = $rule['name'] ?? "";
            $required = isset($rule['required']);
            $default = $rule['default'] ?? null;

            // If input empty and default is defined --> use default
            if (empty($this->requestData[$input]) && !is_null($default)) {
                $this->requestData[$input] = $default;
            }

            // Validate input
            CRM_Wrapi_Processor_Base::validateInput($this->requestData[$input], $type, $name, $required);
        }
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

    /**
     * Process Request
     */
    abstract protected function process();

    /**
     * Return request parameter rules
     *
     * @return array Input rules
     */
    abstract protected function inputRules(): array;
}
