<?php

/**
 * Wrapi Actions Controller
 *
 * @package  wrapi
 * @author   Sandor Semsey <sandor@es-progress.hu>
 * @license  AGPL-3.0
 */
class CRM_Wrapi_Form_RouteActions extends CRM_Wrapi_Form_Base
{
    /**
     * Preprocess form
     *
     * @throws CRM_Core_Exception
     */
    public function preProcess()
    {
        parent::preProcess();

        // Get action from request
        $this->action = CRM_Utils_Request::retrieve('action', 'String');

        $this->performAction();
    }

    /**
     * Perform action
     */
    protected function performAction()
    {
        // Valid ID but no route found
        if (empty($this->getRoute($this->id))) {
            CRM_Core_Session::setStatus(ts('No route found with this ID: %1', ['1' => $this->id,]), 'WrAPI', 'error');
            CRM_Core_Page_AJAX::returnJsonResponse('');
        }

        // Switch action
        switch ($this->action) {
            case CRM_Core_Action::ENABLE:
                $this->config['routing_table'][$this->id]['enabled'] = true;
                $status = ts('Route enabled');
                break;
            case CRM_Core_Action::DISABLE:
                $this->config['routing_table'][$this->id]['enabled'] = false;
                $status = ts('Route disabled');
                break;
            case CRM_Core_Action::DELETE:
                unset($this->config['routing_table'][$this->id]);
                $status = ts('Route deleted');
                break;
            default:
                $status = ts('Not supported action');
                break;
        }

        // Save
        if (!CRM_Wrapi_ConfigManager::saveConfig($this->config)) {
            $status = ts('Error while saving changes');
        };

        // Show success
        CRM_Core_Session::setStatus($status, '', 'success', ['expires' => 5000,]);
        CRM_Core_Page_AJAX::returnJsonResponse('');
    }
}
