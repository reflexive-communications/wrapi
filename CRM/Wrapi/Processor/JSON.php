<?php

/**
 * WrAPI JSON IO Processor
 *
 * @package  wrapi
 * @author   Sandor Semsey <sandor@es-progress.hu>
 * @license  AGPL-3.0
 */
class CRM_Wrapi_Processor_JSON extends CRM_Wrapi_Processor_Base
{
    /**
     * Process input
     *
     * @return array|string
     */
    public function input()
    {
        // Get contents from raw POST data
        $input = file_get_contents('php://input');

        // Decode JSON
        $decoded = json_decode($input, true);

        // Check if valid JSON
        if (json_last_error() !== JSON_ERROR_NONE) {
            $this->error(ts('Not valid JSON received.'), true);
        }

        return $this->sanitize($decoded);
    }
}
