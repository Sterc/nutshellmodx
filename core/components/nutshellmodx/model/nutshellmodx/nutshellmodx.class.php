<?php

/**
 * The main NutshellModx service class.
 *
 * @package nutshellmodx
 */
class NutshellModx
{
    public $modx = null;
    public $namespace = 'nutshellmodx';
    public $cache = null;
    public $options = array();
    public $nutshellapi = null;

    public function __construct(modX &$modx, array $options = array())
    {
        $this->modx =& $modx;
        $this->namespace = $this->getOption('namespace', $options, 'nutshellmodx');

        $corePath = $this->getOption(
            'core_path',
            $options,
            $this->modx->getOption('core_path', null, MODX_CORE_PATH) . 'components/nutshellmodx/'
        );
        $assetsPath = $this->getOption(
            'assets_path',
            $options,
            $this->modx->getOption('assets_path', null, MODX_ASSETS_PATH) . 'components/nutshellmodx/'
        );
        $assetsUrl = $this->getOption(
            'assets_url',
            $options,
            $this->modx->getOption('assets_url', null, MODX_ASSETS_URL) . 'components/nutshellmodx/'
        );

        /* loads some default paths for easier management */
        $this->options = array_merge(array(
            'namespace' => $this->namespace,
            'corePath' => $corePath,
            'modelPath' => $corePath . 'model/',
            'chunksPath' => $corePath . 'elements/chunks/',
            'snippetsPath' => $corePath . 'elements/snippets/',
            'templatesPath' => $corePath . 'templates/',
            'assetsPath' => $assetsPath,
            'assetsUrl' => $assetsUrl,
            'jsUrl' => $assetsUrl . 'js/',
            'cssUrl' => $assetsUrl . 'css/',
            'connectorUrl' => $assetsUrl . 'connector.php'
        ), $options);

        $this->modx->addPackage('nutshellmodx', $this->getOption('modelPath'));
        $this->modx->lexicon->load('nutshellmodx:default');
    }

    /**
     * @param string $username The Nutshell username
     * @param string $apikey The Nutshell API key
     */
    public function loadApi($username = '', $apikey = '')
    {
        require_once($this->options['modelPath'].'nutshellapi/NutshellApi.php');
        if (!$username) {
            $username = $this->modx->getOption('nutshellmodx.username');
        }
        if (!$apikey) {
            $apikey = $this->modx->getOption('nutshellmodx.apikey');
        }
        if ($username && $apikey) {
            $this->nutshellapi = new NutshellApi($username, $apikey);
        } else {
            $this->modx->log(
                modX::LOG_LEVEL_ERROR,
                '[NutshellModx] ' . $this->modx->lexicon('nutshellmodx.error.no_username_apikey')
            );
        }
    }

    public function callApi($call, $params)
    {
        if ($call && $params && $this->nutshellapi) {
            return $this->nutshellapi->call($call, $params);
        }
    }

    /**
     * Find a contact (people) by email address
     * @param string $email The email address of the contact
     * @return int The contact id, or 0 on none found
     */
    public function findContact($email)
    {
        $contactId = 0;
        $findContact = $this->callApi('searchByEmail', array('emailAddressString' => $email));
        if (isset($findContact) && isset($findContact->contacts) && count($findContact->contacts)) {
            $contactId = $findContact->contacts[0]->id;
        }
        return $contactId;
    }

    /**
     * Creates a new contact in Nutshell.
     * @param string $email The contact email address
     * @param string $name
     * @return int The contactId of the newly created contact, 0 on none
     */
    public function createContact($email, $name)
    {
        $contactId = 0;
        $createContact = $this->callApi(
            'newContact',
            array(
                'contact' => array(
                    'email' => $email,
                    'name' => $name
                )
            )
        );
        if ($createContact && isset($createContact->id)) {
            $contactId = $createContact->id;
        }
        return $contactId;
    }

    /**
     * Grabs the system setting to determine whether or not the API should try to match an existing contact
     * @return int  The integer value from the system setting
     */
    public function shouldUseExistingContact()
    {
        return (int) $this->modx->getOption('nutshellmodx.use_existing_contact');
    }

    /**
     * Find an account (company) by name
     * When an exact match is found
     * @param string $name The name of the company
     * @return int The company id, or false on none found
     */
    public function findAccount($name)
    {
        $accountId = 0;
        $searchAccounts = $this->callApi(
            'searchAccounts',
            array('string' => $name)
        );
        if ($searchAccounts && count($searchAccounts)) {
            $name = strtolower($name);
            foreach ($searchAccounts as $account) {
                if (strtolower($account->name) == $name) {
                    $accountId = $account->id;
                    break;
                }
            }
        }
        return $accountId;
    }

    /**
     * Creates an account (= company) in Nutshell
     * @param   string $name The company name
     * @return  int The accountId of the newly created account
     */
    public function createAccount($name)
    {
        $accountId = 0;
        $createAccount = $this->callApi(
            'newAccount',
            array(
                'account' => array(
                    'name' => $name,
                )
            )
        );
        if ($createAccount && isset($createAccount->id)) {
            $accountId = $createAccount->id;
        }
        return $accountId;
    }
    /**
     * Should an account (= company) be created when creating the contact and lead.
     * @return bool
     */
    public function shouldCreateAccount()
    {
        return (int) $this->modx->getOption('nutshellmodx.create_account');
    }

    /**
     * Get a local configuration option or a namespaced system setting by key.
     *
     * @param string $key The option key to search for.
     * @param array $options An array of options that override local options.
     * @param mixed $default The default value returned if the option is not found locally or as a
     * namespaced system setting; by default this value is null.
     * @return mixed The option value or the default value specified.
     */
    public function getOption($key, $options = array(), $default = null)
    {
        $option = $default;
        if (!empty($key) && is_string($key)) {
            if ($options != null && array_key_exists($key, $options)) {
                $option = $options[$key];
            } elseif (array_key_exists($key, $this->options)) {
                $option = $this->options[$key];
            } elseif (array_key_exists("{$this->namespace}.{$key}", $this->modx->config)) {
                $option = $this->modx->getOption("{$this->namespace}.{$key}");
            }
        }
        return $option;
    }
}
