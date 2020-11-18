<?php

/**
 * WrAPI URL encoded form Processor
 *
 * @package  wrapi
 * @author   Sandor Semsey <sandor@es-progress.hu>
 * @license  AGPL-3.0
 */
class CRM_Wrapi_Processor_UrlEncodedForm extends CRM_Wrapi_Processor_Base
{
    /**
     * Process input
     *
     * @return array
     */
    public function input(): ?array
    {
        switch ($_SERVER['REQUEST_METHOD']) {
            case 'GET':
                $data = $_GET;
                break;
            case 'POST':
                $data = $_POST;
                break;
            default:
                $data = [];
                break;
        }

        return $this->sanitize($data);
    }
}
