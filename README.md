# MODX to Nutshell CRM #

Version: 0.1.6

Now you can easily integrate your MODX FormIt forms with Nutshell, via the MODX to Nutshell CRM extra.
Using the Nutshell API, the MODX to Nutshell CRM extra adds a custom hook you can add to your FormIt forms.
Via the `nutshellFields` FormIt parameter you can configure which fields to use for the Nutshell data.


## Features ##
- Checks for existing People and Companies, based on form fields
- Automatically adds a new Nutshell contact (People), using email address and name from your FormIt form.
- Adds a new Company and attaches it to the contact.
- Creates a lead with a lead note based on your form fields.


## Configuration ##
To use the MODX to Nutshell CRM hook, you have to supply a username and API key. This can be configured with either system settings or formit parameters:

### System settings ###
nutshellmodx.apikey
nutshellmodx.username

### FormIt parameters ###
nutshellUsername (optional, defaults to system setting)
nutshellApikey (optional, defaults to system setting)
nutshellFields (required)

The API key can be generated in your Nutshell environment: go to Setup > Third party > API keys.
The configuration of the form fields can be done via the `nutshellFields` FormIt parameter. See below for an example.


## Example ##
```[[!FormIt?
&hooks=`NutshellModxHook`
&nutshellFields=`contact.email==email,
    contact.name==name,
    account.name==company,
    lead.note==message`
&validate=`email:email:required`
]]```

The second value in every &nutshellFields parameter (after the ==) is the name of the form field that holds the corresponding value.
The &nutshellFields parameter has minimum requirement of `contact.email==youremailfield` where `youremailfield` is the emailaddress for the Nutshell contact.