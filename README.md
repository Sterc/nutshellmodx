# MODX to Nutshell CRM #
_Version: 0.1.6_

Now you can easily integrate your MODX FormIt forms with Nutshell, via the MODX to Nutshell CRM extra.
Using the Nutshell API, the MODX to Nutshell CRM extra adds a custom hook you can add to your FormIt forms
 and generates leads, people and companies in your Nutshell environment based on your form fields.

## Features ##
- Checks for existing People and Companies, based on form fields
- Automatically adds a new Nutshell contact (People), using email address and name from your FormIt form.
- Adds a new Company and attaches it to the contact.
- Creates a lead with a lead note based on your form fields.

## Configuration ##
To use the MODX to Nutshell CRM hook, you have to supply a username and API key.
 This can be configured with system settings or FormIt parameters:

### System settings ###
- `nutshellmodx.apikey`
- `nutshellmodx.username`
- `nutshellmodx.use_existing_contact`
- `nutshellmodx.create_account`

### FormIt parameters ###
`nutshellUsername` (optional, defaults to system setting)
`nutshellApikey` (optional, defaults to system setting)
`nutshellFields` (required)

### Nutshell API key ###
The Nutshell API key can be generated in your Nutshell environment: go to Setup > Third party > API keys.

## Example ##
A basic example which generates a lead, person and company in Nutshell.
```
[[!FormIt?
&hooks=`NutshellModxHook`
&nutshellFields=`contact.email==email,contact.name==name,account.name==company,lead.note==message`
&validate=`name:required,email:email:required,company:required`
]]

<form action="your-form-action" method="post" >
  
    <label for="name">Name *</label>
    <input type="text" name="name" id="name" value="[[!+fi.name]]" />

    <label for="email">Emailaddress *</label>
    <input type="email" name="email" id="email" value="[[!+fi.email]]" />
    
    <label for="company">Company name *</label>
    <input type="text" name="company" id="company" value="[[!+fi.company]]" />
    
    <input type="submit" value="Submit" />
    
</form>

```

The second value in every &nutshellFields parameter (after the ==) is the name of the form field that holds the corresponding value.
Please be aware that the `&nutshellFields` parameter has minimum requirement of `contact.email==youremailfield` where `youremailfield` is the emailaddress for the Nutshell contact.