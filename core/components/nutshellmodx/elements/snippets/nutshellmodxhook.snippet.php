<?php
/**
 * NutshellModx FormIt hook
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
$nutshellFields = $modx->getOption('nutshellFields', $formit->config, false);
$formFields = array();
$nutshellFields = explode(',', $nutshellFields);

// If fields not configured, show error
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

// No field configured for the contact email, show error
if (!isset($values[$formFields['contact.email']])) {
    $hook->hasErrors();
    $modx->setPlaceholder('fi.validation_error', true);
    $modx->setPlaceholder('fi.validation_error_message', $modx->lexicon('nutshellmodx.form.error.noemail'));
    return false;
}

// Set default lead note to contact emailaddress
// Check if note field is set in config, if so, and form value is not empty, use that
$leadNote = $values[$formFields['contact.email']];
if (isset($formFields['lead.note'])
    && isset($values[$formFields['lead.note']])
    && !empty($values[$formFields['lead.note']])) {
    $leadNote = $values[$formFields['lead.note']];
}

$findContact = $nutshellmodx->callApi('searchByEmail', ['emailAddressString' => $values[$formFields['contact.email']]]);

// Check if contact already exists.. if not create new one
if (isset($findContact) && isset($findContact->contacts) && count($findContact->contacts)) {
    $contactId = $findContact->contacts[0]->id;
} else {
    $contactName = $values[$formFields['contact.email']];
    if (isset($values[$formFields['contact.name']])) {
        $contactName = $values[$formFields['contact.name']];
    }
    $createContact = $nutshellmodx->callApi(
        'newContact',
        [
            'contact' => [
                'email' => $values[$formFields['contact.email']],
                'name' => $contactName
            ]
        ]
    );
    if ($createContact && isset($createContact->id)) {
        $contactId = $createContact->id;
    }
}

// Use the existing or newly created contactId
if (isset($contactId)) {
    $accoundId = 0;
    $getContact = $nutshellmodx->callApi('getContact', ['contactId' => $contactId]);
    if ($getContact) {
        $rev = $getContact->rev;
        // Find the associated account (company)
        if (isset($getContact->accounts) && count($getContact->accounts)) {
            $accountId = $getContact->accounts[0]->id;
        } else {
            // No account (company) is attached to this user
            // If account name is set, try to find the company via the API
            // When not found, create new company account
            if ($values[$formFields['account.name']] && !empty($values[$formFields['account.name']])) {
                $searchAccounts = $nutshellmodx->callApi(
                    'searchAccounts',
                    ['string' => $values[$formFields['account.name']]]
                );
                if ($searchAccounts && count($searchAccounts)) {
                    $accountId = $searchAccounts[0]->id;
                } else {
                    $createAccount = $nutshellmodx->callApi(
                        'newAccount',
                        [
                            'account' => [
                                'name' => $values[$formFields['account.name']],
                            ]
                        ]
                    );
                    if ($createAccount && isset($createAccount->id)) {
                        $accountId = $createAccount->id;
                    }
                }
                if ($accountId) {
                    // Attach the new account to the contact
                    $editContact = $nutshellmodx->callApi(
                        'editContact',
                        [
                            'contactId' => $contactId,
                            'rev' => $rev,
                            'contact' => [
                                'accounts' => [
                                    [
                                        'id' => $accountId
                                    ]
                                ],
                            ]
                        ]
                    );
                }
            }
        }
    }
    // Create the lead via the 'newLead' api call
    $createLead = $nutshellmodx->callApi(
        'newLead',
        [
            'lead' => [
                'contacts' => [
                    [
                        'id' => $contactId
                    ]
                ],
                'accounts' => [
                    [
                        'id' => $accountId
                    ]
                ],
                'note' => [
                     $leadNote
                 ]
            ]
        ]
    );
}

return true;