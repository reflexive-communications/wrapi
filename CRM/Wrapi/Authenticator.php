<?php

/**
 * Authenticator
 *
 * @package  wrapi
 * @author   Sandor Semsey <sandor@es-progress.hu>
 * @license  AGPL-3.0
 */
class CRM_Wrapi_Authenticator
{
    /**
     * Debug mode
     *
     * @var bool
     */
    protected $debugMode;

    /**
     * CRM_Wrapi_Authenticator constructor.
     *
     * @param bool $debug_mode
     */
    public function __construct(bool $debug_mode)
    {
        $this->debugMode = $debug_mode;
    }

    /**
     * Check HTTP request method
     *
     * @throws CRM_Core_Exception
     */
    public static function checkHTTPRequestMethod()
    {
        $method=$_SERVER['REQUEST_METHOD'] ?? "";

        if ($method != 'POST') {
            throw new CRM_Core_Exception('Only POST method is allowed');
        }
    }

    /**
     * Authenticate request
     *
     * @param string $site_key Received site-key
     * @param string $user_key Received user-key
     *
     * @throws CRM_Core_Exception
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
     * @param string $site_key_sent Received site-key
     *
     * @throws CRM_Core_Exception
     */
    protected function authenticateSiteKey(string $site_key_sent): void
    {
        // Get actual site-key
        $site_key_real = defined('CIVICRM_SITE_KEY') ? CIVICRM_SITE_KEY : "";

        // Check site-key is valid
        if (empty($site_key_real)) {
            throw new CRM_Core_Exception('You need to set a valid site key in civicrm.settings.php');
        }
        if (strlen($site_key_real) < 8) {
            throw new CRM_Core_Exception('Site key needs to be greater than 7 characters in civicrm.settings.php');
        }

        // Check if received site-key is valid
        if ($site_key_sent !== $site_key_real) {
            // Verbose error msg in debug mode
            if ($this->debugMode) {
                $message = 'Failed to authenticate site-key';
            } else {
                $message = 'Failed to authenticate key';
            }
            throw new CRM_Core_Exception($message);
        }
    }

    /**
     * Authenticate user key (API-key)
     *
     * @param string $user_key User-key (API-key) received
     *
     * @throws CRM_Core_Exception
     */
    protected function authenticateUserKey(string $user_key): void
    {
        $uid = null;
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
            // Verbose error msg in debug mode
            if ($this->debugMode) {
                $message = 'Failed to authenticate user-key';
            } else {
                $message = 'Failed to authenticate key';
            }
            throw new CRM_Core_Exception($message);
        }
    }
}
