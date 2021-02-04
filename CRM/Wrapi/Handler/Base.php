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
     * Request processed message
     */
    public const REQUEST_PROCESSED = 'Request processed';

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
     * Options
     *
     * @var array
     */
    protected $options;

    /**
     * File logger
     *
     * @var Log_file
     */
    protected $logger;

    /**
     * CRM_Wrapi_Handler_Base constructor.
     *
     * @param array|null $request_data Request data
     * @param int $logging_level Logging level
     * @param array $permissions Required permissions
     * @param array $options Options
     * @param Log_file $file_logger File logger
     */
    public function __construct(
        ?array $request_data,
        int $logging_level,
        array $permissions,
        array $options,
        Log_file $file_logger
    ) {
        $this->requestData = $request_data;
        $this->logLevel = $logging_level;
        $this->permissions = $permissions;
        $this->options = $options;
        $this->logger = $file_logger;
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
        $this->validate($this->requestData, $this->inputRules());

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
        // Log only if log level is higher than notice
        if ($this->logLevel <= PEAR_LOG_NOTICE) {
            return;
        }

        // Compose message
        $message = $_SERVER['REMOTE_ADDR'].' Request received  Selector: '.$this->requestData['selector'];

        // Also log request data for debug
        if ($this->logLevel == PEAR_LOG_DEBUG) {
            $data = $this->requestData;

            // Exclude sensitive data from logging
            unset($data['site_key']);
            unset($data['user_key']);
            // Already logged
            unset($data['selector']);

            $message .= " Data: ".serialize($data);
        }

        $this->logger->log($message, $this->logLevel);
    }

    /**
     * Validate Request Data
     *
     * @param mixed $data Data to validate
     * @param array $rules Validation rules
     *
     * @throws CRM_Core_Exception
     */
    protected function validate($data, array $rules): void
    {
        // Loop through input rules
        foreach ($rules as $field => $rule) {
            // Get rule details
            $type = $rule['type'] ?? "";
            $name = $rule['name'] ?? "";
            $required = (bool)($rule['required'] ?? false);
            $elements = $rule['elements'] ?? [];

            // Validate input fields

            // Field is a list
            if ($type == "list") {
                // Check child elements (recursively)
                foreach ($data[$field] as $item) {
                    $this->validate($item, $elements);
                }
            } else {
                if (is_array($data)) {
                    $value = $data[$field];
                } else {
                    $value = $data;
                }
                CRM_Wrapi_Processor_Base::validateInput($value, $type, $name, $required);
            }
        }
    }

    /**
     * Log request processed
     */
    protected function logRequestProcessed()
    {
        // Log only if log level is higher than notice
        if ($this->logLevel <= PEAR_LOG_NOTICE) {
            return;
        }

        // Compose message
        $message = $_SERVER['REMOTE_ADDR'].' Request processed Selector: '.$this->requestData['selector'];

        $this->logger->log($message, $this->logLevel);
    }

    /**
     * Write debug message to log if current log level is DEBUG
     *
     * @param string $message Message to log
     */
    protected function debug(string $message)
    {
        if ($this->logLevel >= PEAR_LOG_DEBUG) {
            $this->logger->debug($message);
        }
    }

    /**
     * Write info message to log if current log level at least INFO
     *
     * @param string $message Message to log
     */
    protected function info(string $message)
    {
        if ($this->logLevel >= PEAR_LOG_INFO) {
            $this->logger->info($message);
        }
    }

    /**
     * Process Request
     */
    abstract protected function process();

    /**
     * Return request parameter rules
     *
     * @return array Input rules
     *
     * Properties:
     *   - type:     Type of field (string | email | int | id | float | bool | date | datetime | list)
     *   - name:     Name of field
     *   - required: Is required field (true | false)
     *   - default:  Default value
     *   - elements: Definition for list elements (only for list type)
     */
    abstract protected function inputRules(): array;
}
