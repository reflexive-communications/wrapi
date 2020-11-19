<?php

/**
 * Base IO Processor Class
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
     */
    public function sanitize($input)
    {
        $sanitized = [];

        if (is_array($input)) {
            foreach ($input as $key => $value) {
                // Sanitize key
                $key = CRM_Utils_String::stripSpaces($key);

                // Sanitize value
                if (is_array($value)) {
                    $value = $this->sanitize($value);
                } else {
                    $value = CRM_Utils_String::stripSpaces($value);
                }

                $sanitized[$key] = $value;
            }
        } else {
            $sanitized = CRM_Utils_String::stripSpaces($input);
        }

        return $sanitized;
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
        // Get request parameters
        $request_data = $this->input();

        // Validate input
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
            $this->error('Site key missing.');
        }
        if (empty($user_key)) {
            $this->error('User key missing.');
        }
        if (empty($action)) {
            $this->error('Action missing.');
        }

        // Check if string
        if (!CRM_Utils_Rule::string($site_key)) {
            $this->error('Site key not a string.');
        }
        if (!CRM_Utils_Rule::string($user_key)) {
            $this->error('User key not a string.');
        }
        if (!CRM_Utils_Rule::string($action)) {
            $this->error('Action not a string.');
        }
    }
}
