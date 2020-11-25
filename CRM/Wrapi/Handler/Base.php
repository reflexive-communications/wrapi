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
     * IO Processor
     *
     * @var CRM_Wrapi_Processor_Base
     */
    protected $processor;

    /**
     * CRM_Wrapi_Handler_Base constructor.
     *
     * @param CRM_Wrapi_Processor_Base $processor
     */
    public function __construct(CRM_Wrapi_Processor_Base $processor)
    {
        $this->processor = $processor;
    }

    /**
     * Handle request
     *
     * @param $request_data
     *
     * @return mixed
     */
    abstract public function run($request_data);
}
