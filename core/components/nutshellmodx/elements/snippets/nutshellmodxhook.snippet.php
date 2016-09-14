<?php
/**
 * NutshellModx FormIt hook
 *
 */
$nutshellmodx = $modx->getService('nutshellmodx', 'NutshellModx', $modx->getOption('nutshellmodx.core_path', null, $modx->getOption('core_path').'components/nutshellmodx/').'model/nutshellmodx/', array());
if (!($nutshellmodx instanceof NutshellModx)) {
    return;
}

// $values = $hook->getValues();
// $formFields = $modx->getOption('NutshellFields', $formit->config, false);

// $contactEmail = $values['email'];
// $contactName = $values['name'];
// $contactName = $values['company'];

/* test */
echo '<pre>';

$nutshellFields = 'contact.email==email,contact.name==name,account.name==company';
$formFields = array();
$nutshellFields = explode(',', $nutshellFields);
foreach ($nutshellFields as $nutshellFieldKey) {
    list($name, $key) = explode('==', $nutshellFieldKey);
    $formFields[trim($name)] = trim($key);
}

$values = [
    'email' => 'joeke123@sterc.nl',
    'name' => 'Joeke Kloosterman',
    'company' => 'Testcompanyjoeke',
];

$findContact = $nutshellmodx->callApi('searchByEmail', ['emailAddressString' => $values[$formFields['contact.email']]]);

// Check if contact already exists.. if not create new one
if (isset($findContact) && isset($findContact->contacts) && count($findContact->contacts)) {
    $contactId = $findContact->contacts[0]->id;
} else {
    $createContact = $nutshellmodx->callApi(
        'newContact',
        [
            'contact' => [
                'email' => $values[$formFields['contact.email']],
                'name' => $values[$formFields['contact.name']],
            ]
        ]
    );
    if ($createContact && isset($createContact->id)) {
        $contactId = $createContact->id;
    }
}

// Use the existing or newly created contactId
if ($contactId) {
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
                $searchAccounts = $nutshellmodx->callApi('searchAccounts', ['string' => $values[$formFields['account.name']]]);
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
    echo $accountId;
    print_r($getContact);
}

return true;
