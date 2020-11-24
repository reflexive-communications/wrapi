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
     * Handle request
     *
     * @param $request_data
     *
     * @return mixed
     */
    public function run($request_data)
    {
        // Echo request data (in JSON)
        $this->processor->output($request_data);
    }
}
