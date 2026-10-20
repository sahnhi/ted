Update mail template
==========

When you need to update a mail template, there are a few things you need to do.
- Update template in source code (if needed)
- Update template on Mailchimp
- Deploy template from Mailchimp to Mandrill

## 1. Update template in source code
This might be necessary if you need to add / remove / update a variable that you are passing to the template.

All mail templates are located in src/Services/Mail.

The template name associated in Mailchimp is usually in the $templateName variable.

## 2. Update template on Mailchimp
To edit a template on Mailchimp, you need to have access to <a href="https://us11.admin.mailchimp.com/">Mailchimp dashboard</a>.

If you don't have access to that, see sarah@tediber.com

Then locate the template you need to edit in <a href="https://us11.admin.mailchimp.com/templates/">Email templates</a>.
It will have the name you found in the $templateName variable (cf 1.)

You can edit your template here. There are a few things to be careful with:
- All templates are used in production environment
- If a variable is present in the source code but not in the template, this will generate an error

## 3. Deploy template from Mailchimp to Mandrill
Once you've edited your template in Mailchimp, you need to upload it to Mandrill.

To do that, there is an option list next to the edit template button on Mailchimp.

Open it and click on "Send to Mandrill".
