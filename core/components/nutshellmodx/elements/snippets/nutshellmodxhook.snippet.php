<?php
/**
 * MODX to Nutshell CRM FormIt hook
 *
 */
$nutshellmodx = $modx->getService(
    'nutshellmodx',
    'NutshellModx',
    $modx->getOption(
        'nutshellmodx.core_path',
        null,
        $modx->getOption('core_path').'components/nutshellmodx/'
    ).'model/nutshellmodx/',
    array()
);
if (!($nutshellmodx instanceof NutshellModx)) {
    return;
}

$formit =& $hook->formit;
$values = $hook->getValues();

/**
 * Get the Nutshell username and apikey from formit config.
 * If not set, username and apikey from system settings are used.
 */
$nutshellUsername = $modx->getOption('nutshellUsername', $formit->config, false);
$nutshellApikey= $modx->getOption('nutshellApikey', $formit->config, false);

/* Load the Nutshell API */
$nutshellmodx->loadApi($nutshellUsername, $nutshellApikey);

$nutshellFields = $modx->getOption('nutshellFields', $formit->config, false);
$formFields = array();
$nutshellFields = explode(',', $nutshellFields);

/* If fields not configured, show error */
if (!count($nutshellFields)) {
    $hook->hasErrors();
    $modx->setPlaceholder('fi.validation_error', true);
    $modx->setPlaceholder('fi.validation_error_message', $modx->lexicon('nutshellmodx.form.error.nofields'));
    return false;
}
foreach ($nutshellFields as $nutshellFieldKey) {
    list($name, $key) = explode('==', $nutshellFieldKey);
    $formFields[trim($name)] = trim($key);
}

/* No field configured for the contact email, show error */
if (!isset($values[$formFields['contact.email']])) {
    $hook->hasErrors();
    $modx->setPlaceholder('fi.validation_error', true);
    $modx->setPlaceholder('fi.validation_error_message', $modx->lexicon('nutshellmodx.form.error.noemail'));
    return false;
}

/**
 * Set default lead note to contact emailaddress
 * Check if note field is set in config, if so, and form value is not empty, use that
 */
$leadNote = $values[$formFields['contact.email']];
if (isset($formFields['lead.note'])
    && isset($values[$formFields['lead.note']])
    && !empty($values[$formFields['lead.note']])) {
    $leadNote = $values[$formFields['lead.note']];
}

/* Check if API should try to match an existing contact, or create a new one */
if ($nutshellmodx->shouldUseExistingContact()) {
    /* Try to find an existing contact, if none found, $contactId returns false */
    $contactId = $nutshellmodx->findContact($values[$formFields['contact.email']]);
} else {
    $contactId = false;
}

/* If no contactId, create new contact. */
if (!$contactId) {
    /* By default, use the emailaddress for the contact name, if a name is set in the form, use that */
    $contactName = $values[$formFields['contact.email']];
    if (isset($values[$formFields['contact.name']])) {
        $contactName = $values[$formFields['contact.name']];
    }
    $contactId = $nutshellmodx->createContact($values[$formFields['contact.email']], $contactName);
}

// Use the existing or newly created contactId
if (isset($contactId)) {
    $accountId = 0;
    $getContact = $nutshellmodx->callApi('getContact', array('contactId' => $contactId));
    if ($getContact) {
        $rev = $getContact->rev;
        /* Find the associated account (company) */
        if (isset($getContact->accounts) && count($getContact->accounts)) {
            $accountId = $getContact->accounts[0]->id;
        } else {
            /**
             * No account (company) is attached to this user
             * First check if we should create an account or not
             * If account name is set, try to find the company via the API
             * When not found, create new company account
             */
            if ($nutshellmodx->shouldCreateAccount()) {
                if ($values[$formFields['account.name']] && !empty($values[$formFields['account.name']])) {
                    /* First try to match existing contact */
                    $accountId = $nutshellmodx->findAccount($values[$formFields['account.name']]);
                    if (!$accountId) {
                        $accountId = $nutshellmodx->createAccount($values[$formFields['account.name']]);
                    }
                    /* Attach the new account (company) to the contact */
                    if ($accountId) {
                        $nutshellmodx->callApi(
                            'editContact',
                            array(
                                'contactId' => $contactId,
                                'rev' => $rev,
                                'contact' => array('accounts' => array(array('id' => $accountId)))
                            )
                        );
                    }
                }
            }
        }
    }
    /* And finally, create the lead via the 'newLead' api call */
    $nutshellmodx->callApi(
        'newLead',
        array(
            'lead' => array(
                'contacts' => array(array('id' => $contactId)),
                'accounts' => ($accountId ? array(array('id' => $accountId)) : false),
                'note' => array($leadNote)
            )
        )
    );
}

return true;