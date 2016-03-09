# Tokens for JWSS

This extensions contains the following tokens for jwss.judiciary.gov.uk:

- **{contact.career_history}** shows a table with all the career history of a contact
- **{contact.latest_shadowing_application}** Shows the latest shadowing application

# Requirements

This extension assumes that the following custom groups exists in the system:

- **Career__History**
- **JWSS_Applicaiton_Tribunal_Judge**

# Technical implementation

This extensions implements the hooks for tokens (hook_civicrm_tokens and hook_civicrm_tokenValues)

