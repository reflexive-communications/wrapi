<?php

/**
 * Wrapi Authenticator
 *
 * @package  wrapi
 * @author   Sandor Semsey <sandor@es-progress.hu>
 * @license  AGPL-3.0
 */
class CRM_Wrapi_Authenticator
{
    /**
     * IO processor
     *
     * @var \CRM_Wrapi_Processor_Base
     */
    protected CRM_Wrapi_Processor_Base $processor;

    /**
     * CRM_Wrapi_Authenticator constructor.
     *
     * @param  \CRM_Wrapi_Processor_Base  $processor
     */
    public function __construct(CRM_Wrapi_Processor_Base $processor)
    {
        $this->processor = $processor;
    }

    /**
     * Authenticate request
     *
     * @param  string  $site_key  Received site-key
     * @param  string  $user_key  Received user-key
     *
     * @throws \CRM_Core_Exception
     */
    public function authenticate(string $site_key, string $user_key): void
    {
        // Authenticate site-key
        $this->authenticateSiteKey($site_key);

        // Authenticate user-key
        $this->authenticateUserKey($user_key);
    }

    /**
     * Authenticate site-key
     *
     * @param  string  $site_key_sent  Received site-key
     */
    protected function authenticateSiteKey(string $site_key_sent): void
    {
        // Get actual site-key
        $site_key_real = defined('CIVICRM_SITE_KEY') ? CIVICRM_SITE_KEY : null;

        // Check site-key is valid
        if (is_null($site_key_real) || empty($site_key_real)) {
            $this->processor->error(
                'You need to set a valid site key in civicrm.settings.php.',
                true
            );
        }
        if (strlen($site_key_real) < 8) {
            $this->processor->error(
                'Site key needs to be greater than 7 characters in civicrm.settings.php.',
                true
            );
        }

        // Check if received site-key is valid
        if ($site_key_sent !== $site_key_real) {
            $this->processor->error('Failed to authenticate key.', true);
        }
    }

    /**
     * Authenticate user key (API-key)
     *
     * @param  string  $user_key  User-key (API-key) received
     *
     * @throws \CRM_Core_Exception
     */
    protected function authenticateUserKey(string $user_key): void
    {
        $uid        = null;
        $contact_id = null;

        // Get contact ID with matching API key (user-key)
        $contact_id = CRM_Core_DAO::getFieldValue(
            'CRM_Contact_DAO_Contact',
            $user_key,
            'id',
            'api_key'
        );

        // Get CMS user matching Civi contact
        if ($contact_id) {
            $uid = CRM_Core_BAO_UFMatch::getUFId($contact_id);
        }

        // Contact and CMS user found --> bootstrap Civi
        if ($uid && $contact_id) {
            CRM_Utils_System::loadBootStrap(['uid' => $uid], true, false);
            $session = CRM_Core_Session::singleton();
            $session->set('ufID', $uid);
            $session->set('userID', $contact_id);
            CRM_Core_DAO::executeQuery(
                'SET @civicrm_user_id = %1',
                [1 => [$contact_id, 'Integer']]
            );
        } else {
            // No CMS user found
            // Same error as site-key fail, in order to make brute-force harder.
            // It is harder to debug though. You may change this when debugging.
            $this->processor->error('Failed to authenticate key.', true);
        }
    }
}
