# Tokens for JWSS

This extensions contains the following tokens for jwss.judiciary.gov.uk:

- **{contact.career_history}** shows a table with all the career history of a contact
- **{contact.latest_shadowing_application}** Shows the latest shadowing application
- **{activity_owner.display_name}** The name of the assignee of the activity
- **{activity_owner.email}** The email of the assignee of the activity
- **{activity_owner.phone}** The phone number of the assignee of the activity

# Requirements

This extension assumes that the following custom groups exists in the system:

- **Career__History**
- **JWSS_Applicaiton_Tribunal_Judge**

# Technical implementation

This extensions implements the hooks for tokens (hook_civicrm_tokens and hook_civicrm_tokenValues)

For the activity_owner tokens we use a combination of the hook_tokens and event subscribers.
See also http://civicrm.stackexchange.com/questions/10542/using-event-listeners-in-my-extensions/10545#10545

