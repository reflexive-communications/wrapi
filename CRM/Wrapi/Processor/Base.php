<?php

/**
 * Base Input Processor Class
 */
abstract class CRM_Wrapi_Processor_Base
{
    /**
     * Values max length in request
     */
    public const MAX_LENGTH = 255;

    /**
     * Process input
     *
     * @return mixed
     */
    abstract public function input();

    /**
     * Detect content-type
     *
     * @return string
     */
    public static function detectContentType()
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
     * @param  mixed  $input  Input to sanitize
     *
     * @return array|string
     */
    public function sanitize($input)
    {
        $sanitized = [];

        if (is_array($input)) {
            foreach ($input as $key => $value) {
                // Strip whitespace
                $key   = CRM_Utils_String::stripSpaces($key);
                $value = CRM_Utils_String::stripSpaces($value);

                // Replace non english alpha-numeric chars in keys
                $key = CRM_Utils_String::munge(
                    $key,
                    '_',
                    self::MAX_LENGTH
                );

                // Remove potential XSS strings from values
                $value = CRM_Utils_String::purifyHTML($value);

                $sanitized[$key] = $value;
            }
        } else {
            $sanitized = CRM_Utils_String::stripSpaces($input);
            $sanitized = CRM_Utils_String::purifyHTML($sanitized);
        }

        return $sanitized;
    }

    /**
     * Return output to client in JSON format
     *
     * @param  mixed  $result  Result to output
     */
    public function output($result)
    {
        CRM_Utils_JSON::output($result);
    }

    /**
     * Log and optionally return error message to client then exit
     *
     * @param  mixed  $message  Error message
     * @param  bool  $output  Should we output error message
     */
    public function error($message, bool $output = false)
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
                'error'   => true,
                'message' => $message,
            ];
            $this->output($response);
        }

        CRM_Utils_System::civiExit();
    }
}
