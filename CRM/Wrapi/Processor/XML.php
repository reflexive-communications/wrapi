<?php

/**
 * WrAPI XML IO Processor
 *
 * @package  wrapi
 * @author   Sandor Semsey <sandor@es-progress.hu>
 * @license  AGPL-3.0
 */
class CRM_Wrapi_Processor_XML extends CRM_Wrapi_Processor_Base
{
    /**
     * Process input
     *
     * @return array|string
     */
    public function input()
    {
        try {
            // Get contents from raw POST data
            $input = file_get_contents('php://input');

            // Disable external entity parsing to prevent XEE attack
            libxml_disable_entity_loader(true);

            // Load XML
            $xml = new SimpleXMLElement($input);

            // Encode & decode to JSON to convert XML_Element to array
            $data = json_encode($xml);
            $data = json_decode($data, true);

            return $this->sanitize($data);
        } catch (Exception $ex) {
            $this->error('Unable to parse XML');
        }
    }
}
