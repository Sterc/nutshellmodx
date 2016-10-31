---------------------------------------
NutshellModx
---------------------------------------
Version: 0.1.3
Author: Sterc <modx@sterc.nl>
---------------------------------------

Now you can easily integrate your MODX FormIt forms with Nutshell, via the NutshellModx extra.
Using the Nutshell API, the NutshellModx extra adds a custom hook you can add to your FormIt forms.
Via the `nutshellFields` FormIt parameter you can configure which fields to use for the Nutshell data.


Features
---------------------------------------
- Checks for existing People and Companies, based on form fields
- Automatically adds a new Nutshell contact (People), using email address and name from your FormIt form.
- Adds a new Company and attaches it to the contact.
- Creates a lead with a lead note based on your form fields.


Configuration
---------------------------------------
To use the NutshellModx hook, you have to supply a username and API key. This can be configured with the system settings:

nutshellmodx.apikey
nutshellmodx.username

The API key can be generated in your Nutshell environment: go to Setup > Third party > API keys.
The configuration of the form fields can be done via the `nutshellFields` FormIt parameter. See below for an example.


Example
---------------------------------------
[[!FormIt?
&hooks=`NutshellModxHook`
&nutshellFields=`contact.email==email,
    contact.name==name,
    account.name==company,
    lead.note==message`
&validate=`email:email:required`
]]

The nutshellFields parameter has minimum requirement of `contact.email==emailformfield` where `emailformfield` is the name of the form field where the user enters their emailaddress.