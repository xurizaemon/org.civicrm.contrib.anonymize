# TODO

* Move work code from API calls to Civi/Anonymize* files.
* Use CiviCRM chaining appropriately, instead of methods like `Contact::anonymizeAddresses($contact_id)`
* If locale is passed in, pass it on to methods (eg pass locale from Contact to Address).
* Generate more appropriate data for non-Individual types.
* Add images but only if there's already an image, and so forth.
* What to do with custom data? Just vape the lot? Free text unless there are defined options?
* Performance.
