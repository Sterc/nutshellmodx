<?php
/**
 * Default English Lexicon Entries for NutshellModx
 *
 * @package nutshellmodx
 * @subpackage lexicon
 */

$_lang['nutshellmodx'] = 'MODX to Nutshell CRM';
$_lang['nutshellmodx.form.error.nofields'] = 'No fields configured for MODX to Nutshell CRM. 
Please configure the FormIt parameter `nutshellFields`.';
$_lang['nutshellmodx.form.error.noemail'] = 'No contact email field configured for MODX to Nutshell CRM. 
Please add a `contact.email==formemailfieldname` to the `nutshellFields` FormIt parameter.';
$_lang['nutshellmodx.error.no_username_apikey'] = 'Nutshell username or API key not found.';

// System settings
$_lang['setting_nutshellmodx.apikey'] = 'Nutshell API key';
$_lang['setting_nutshellmodx.username'] = 'Nutshell username';
$_lang['setting_nutshellmodx.use_existing_contact'] = 'Use an existing Nutshell contact';
$_lang['setting_nutshellmodx.use_existing_contact_desc'] = 'When set to yes the hook will try
 to match an existing Nutshell contact using the supplied emailaddress.';
$_lang['setting_nutshellmodx.create_account'] = 'Create company';
$_lang['setting_nutshellmodx.create_account_desc'] = 'When set to yes the hook will add a company
 to Nutshell using the company supplied in the form.';
