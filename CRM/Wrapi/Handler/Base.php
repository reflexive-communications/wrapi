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
            $allowed_values = $rule['values'] ?? [];
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
                CRM_Wrapi_Processor_Base::validateInput($value, $type, $name, $required, $allowed_values);
            }
        }
    }

    /**
     * Set default values
     *
     * Loop through input rules, and checks the received data,
     * if there is default specified and data is not set then set value to default
     *
     * @param mixed $data Data to check
     * @param array $rules Validation rules
     *
     * @return mixed Data with defaults
     *
     * @throws CRM_Core_Exception
     */
    protected function setDefaultValues($data, array $rules)
    {
        $data_with_defaults = null;

        // Loop through input rules
        foreach ($rules as $field => $rule) {
            // Get rule details
            $type = $rule['type'] ?? "";
            $elements = $rule['elements'] ?? [];
            $default = $rule['default'] ?? null;

            // Field is a list
            if ($type == "list") {
                // If list is empty --> check for default --> if there is a default, use it
                if (!isset($data[$field])) {
                    if (isset($default)) {
                        $data_with_defaults[$field] = $default;
                    }
                } else {
                    // List not empty --> loop through elements, and recurse into children
                    foreach ($data[$field] as $item) {
                        $data_with_defaults[$field] = $this->setDefaultValues($item, $elements);
                    }
                }
                continue;
            }

            // Data is an array
            if (is_array($data)) {
                if (!isset($data[$field])) {
                    // Data not set, if there is a default --> use it
                    // If there is no default --> then skip this field
                    if (isset($default)) {
                        $data_with_defaults[$field] = $default;
                    }
                } else {
                    // Data set --> copy value
                    $data_with_defaults[$field] = $data[$field];
                }
                continue;
            }

            // Data not a list, not an array --> primitive type
            if (!isset($data)) {
                if (isset($default)) {
                    $data_with_defaults = $default;
                }
            } else {
                $data_with_defaults = $data;
            }
        }

        return $data_with_defaults;
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
     * Map fields in input to fields specified by mapping
     *
     * @param array $request_data Input data
     * @param array $mapping Mapping rules
     *  format: [
     *      'input_field_1_name => 'mapped_field_name_1,
     *      'input_field_2_name => 'mapped_field_name_2,
     *  ]
     * @return array Mapped data
     */
    protected function mapFieldsString(array $request_data, array $mapping): array
    {
        $mapped_data = [];

        // Loop through mapping
        foreach ($mapping as $field_in_request => $field_mapped) {
            $value = $request_data[$field_in_request];

            if (isset($value)) {
                $mapped_data[$field_mapped] = $value;
            }
        }

        return $mapped_data;
    }

    /**
     * Map fields in input to integer fields specified by mapping
     *
     * @param array $request_data Input data
     * @param array $mapping Mapping rules
     *  format: [
     *      'input_field_1_name => 'mapped_field_name_1,
     *      'input_field_2_name => 'mapped_field_name_2,
     *  ]
     * @return array Mapped data
     */
    protected function mapFieldsInteger(array $request_data, array $mapping): array
    {
        $mapped_data = [];

        // Loop through mapping
        foreach ($mapping as $field_in_request => $field_mapped) {
            $value = $request_data[$field_in_request];

            if (isset($value)) {
                $mapped_data[$field_mapped] = (int)$value;
            }
        }

        return $mapped_data;
    }

    /**
     * Map Bool fields in input to fields specified by mapping
     *
     * @param array $request_data Input data
     * @param array $mapping Mapping rules
     *  format: [
     *      'input_field_1_name => 'mapped_field_name_1,
     *      'input_field_2_name => 'mapped_field_name_2,
     *  ]
     * @return array Mapped data
     */
    protected function mapFieldsBool(array $request_data, array $mapping): array
    {
        $mapped_data = [];

        // Loop through mapping
        foreach ($mapping as $field_in_request => $field_mapped) {
            $value = $request_data[$field_in_request];
            if (isset($value)) {

                $true_values = [true, 1, 'Yes', 'yes'];

                if (in_array($value, $true_values, true)) {
                    $mapped_data[$field_mapped] = 1;
                } else {
                    $mapped_data[$field_mapped] = 0;
                }
            }
        }

        return $mapped_data;
    }

    /**
     * Map ISO8601 DateTime fields in input to MySQL DateTime fields specified by mapping
     *
     * @param array $request_data Input data
     * @param array $mapping Mapping rules
     *  format: [
     *      'input_field_1_name => 'mapped_field_name_1,
     *      'input_field_2_name => 'mapped_field_name_2,
     *  ]
     * @return array Mapped data
     */
    protected function mapFieldsDateTimeISO8601(array $request_data, array $mapping): array
    {
        $mapped_data = [];

        // Loop through mapping
        foreach ($mapping as $field_in_request => $field_mapped) {
            $value = $request_data[$field_in_request];

            if (isset($value)) {
                // Parse ISO8601 Date
                $iso8601_date = DateTime::createFromFormat("Y-m-d\TH:i:s.uP", $value);
                // Convert to MySQL Date
                $mapped_data[$field_mapped] = $iso8601_date->format("Y-m-d H:i:s");
            }
        }

        return $mapped_data;
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
