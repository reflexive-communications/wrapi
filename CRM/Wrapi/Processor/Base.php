<?php

/**
 * Base IO Processor
 *
 * @package  wrapi
 * @author   Sandor Semsey <sandor@es-progress.hu>
 * @license  AGPL-3.0
 */
abstract class CRM_Wrapi_Processor_Base
{
    /**
     * Parse input
     *
     * @return mixed
     */
    abstract public function input();

    /**
     * Detect content-type
     *
     * @return string
     */
    public static function detectContentType(): string
    {
        // If content-type is set use it
        if (isset($_SERVER['CONTENT_TYPE'])) {
            switch ($_SERVER['CONTENT_TYPE']) {
                case 'application/json':
                case 'application/javascript':
                    return CRM_Wrapi_Processor_JSON::class;
                case 'text/xml':
                case 'application/xml':
                    return CRM_Wrapi_Processor_XML::class;
                default:
                    return CRM_Wrapi_Processor_UrlEncodedForm::class;
            }
        }

        // Fallback to URL encoded
        return CRM_Wrapi_Processor_UrlEncodedForm::class;
    }

    /**
     * Perform basic input sanitization
     *
     * @param mixed $input Input to sanitize
     *
     * @return array|string
     *
     * @throws CRM_Core_Exception
     */
    public function sanitize($input)
    {
        $sanitized = [];

        // Input is array --> sanitize values and keys
        if (is_array($input)) {
            foreach ($input as $key => $value) {
                // Sanitize key
                $key = CRM_Utils_String::stripSpaces($key);
                $key = $this->sanitizeString($key);

                // Sanitize value
                if (is_array($value)) {
                    // Array --> recurse
                    $value = $this->sanitize($value);
                } elseif (is_int($value)) {
                    // Integer
                    $value = CRM_Utils_Type::validate($value, 'Int');
                } elseif (is_float($value)) {
                    // Float
                    $value = CRM_Utils_Type::validate($value, 'Float');
                } elseif (is_bool($value)) {
                    // Boolean
                    $value = CRM_Utils_Type::validate($value, 'Boolean');
                } else {
                    // Nothing else --> string
                    $value = $this->sanitizeString($value);
                }

                $sanitized[$key] = $value;
            }
        } elseif (is_int($input)) {
            // Input is single integer
            $sanitized = CRM_Utils_Type::validate($input, 'Int');
        } elseif (is_float($input)) {
            // Input is single float
            $sanitized = CRM_Utils_Type::validate($input, 'Float');
        } elseif (is_bool($input)) {
            // Input is single boolean
            $sanitized = CRM_Utils_Type::validate($input, 'Boolean');
        } else {
            // Input is single string
            $sanitized = $this->sanitizeString($input);
        }

        return $sanitized;
    }

    /**
     * Sanitize string
     *
     * @param $value
     *
     * @return string
     *
     * @throws CRM_Core_Exception
     */
    protected function sanitizeString($value)
    {
        // Strip whitespace
        $value = CRM_Utils_String::stripSpaces($value);
        // Escape string
        $value = CRM_Utils_Type::escape($value, 'String');
        // Remove XSS
        $value = CRM_Utils_String::purifyHTML($value);

        return $value;
    }

    /**
     * Return output to client in JSON format
     *
     * @param mixed $result Result to output
     */
    public function output($result): void
    {
        CRM_Utils_JSON::output($result);
    }

    /**
     * Log and optionally return error message to client then exit
     *
     * @param mixed $message Error message
     * @param bool $output Should we output error message
     */
    public function error($message, bool $output = true): void
    {
        // Log error
        CRM_Core_Error::debug_log_message(
            $message,
            false,
            CRM_Wrapi_ExtensionUtil::SHORT_NAME,
            PEAR_LOG_ERR
        );

        // Should we output error message?
        if ($output) {
            $response = [
                'error' => true,
                'message' => $message,
            ];
            $this->output($response);
        }

        CRM_Utils_System::civiExit();
    }

    /**
     * Process request
     *
     * @return array|string
     */
    public function processInput()
    {
        // Parse request data
        $request_data = $this->input();

        // Check if required params (keys, action) supplied and valid strings
        $this->validate($request_data);

        return $request_data;
    }

    /**
     * Validate inputs (keys, action)
     *
     * @param mixed $request_params Request data
     */
    public function validate($request_params): void
    {
        // Get parameters
        $site_key = $request_params['site_key'] ?? "";
        $user_key = $request_params['user_key'] ?? "";
        $action = $request_params['action'] ?? "";

        // Check if supplied
        if (empty($site_key)) {
            $this->error('Site key missing');
        }
        if (empty($user_key)) {
            $this->error('User key missing');
        }
        if (empty($action)) {
            $this->error('Action missing');
        }

        // Check if string
        if (!CRM_Utils_Rule::string($site_key)) {
            $this->error('Site key not a string');
        }
        if (!CRM_Utils_Rule::string($user_key)) {
            $this->error('User key not a string');
        }
        if (!CRM_Utils_Rule::string($action)) {
            $this->error('Action not a string');
        }
    }
}
