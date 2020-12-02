<?php

/**
 * Noop Handler
 *
 * Does nothing
 *
 * @package  wrapi
 * @author   Sandor Semsey <sandor@es-progress.hu>
 * @license  AGPL-3.0
 */
class CRM_Wrapi_Handler_Noop extends CRM_Wrapi_Handler_Base
{
    /**
     * Return request parameter rules
     *
     * @return array Input rules
     */
    protected function inputRules(): array
    {
        return [];
    }

    /**
     * Process Request
     */
    protected function process()
    {
        return null;
    }
}
