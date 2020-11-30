<?php

/**
 * Echo Handler
 *
 * Echoes request back to client (good for debug)
 *
 * @package  wrapi
 * @author   Sandor Semsey <sandor@es-progress.hu>
 * @license  AGPL-3.0
 */
class CRM_Wrapi_Handler_Echo extends CRM_Wrapi_Handler_Base
{
    /**
     * Process Request
     */
    protected function process()
    {
        // Log request processed
        $this->logRequestProcessed();

        // Echo request data
        return $this->requestData;
    }
}
