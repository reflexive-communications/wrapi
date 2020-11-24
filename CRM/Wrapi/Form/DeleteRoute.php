<?php

/**
 * WrAPI Delete route controller
 *
 * @see https://docs.civicrm.org/dev/en/latest/framework/quickform/
 *
 * @package  wrapi
 * @author   Sandor Semsey <sandor@es-progress.hu>
 * @license  AGPL-3.0
 */
class CRM_Wrapi_Form_DeleteRoute extends CRM_Wrapi_Form_Base
{
    /**
     * Build Form
     */
    public function buildQuickForm()
    {
        // Add form elements
        $this->addButtons(
            [
                [
                    'type' => 'cancel',
                    'name' => ts('Cancel'),
                ],
                [
                    'type' => 'done',
                    'name' => ts('Delete'),
                    'isDefault' => true,
                ],
            ]
        );
        $this->assign('route_name', $this->config['routing_table'][$this->id]['name']);

        $this->setTitle(ts('Delete route'));

        parent::buildQuickForm();
    }

    /**
     * Post process
     */
    public function postProcess()
    {
        // Remove route from routing table
        unset($this->config['routing_table'][$this->id]);

        // Save
        if (!CRM_Wrapi_ConfigManager::saveConfig($this->config)) {
            CRM_Core_Session::setStatus(ts('Error while saving changes'), 'WrAPI', 'error');
        };

        // Show success
        CRM_Core_Session::setStatus(ts('Route deleted'), '', 'success', ['expires' => 5000,]);
    }
}
