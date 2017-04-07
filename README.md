# CiviCRM Anonymizer

This extension is intended to anonymize your database, so you can share your CiviCRM DB more readily.

## Terms of use

* You're responsible for your data.
* You're welcome to use this tool.
* Don't do anything wrong-headed.
* Contributions and input are welcome.

### Do not install in production

This extension **will** ruin your production dataset if you use it there. Don't even install it on your live site.

### No guarantee or warranty

There is no guarantee or warranty with this extension. If you've made a custom field which includes identifying or sensitive data then you cannot expect this extension to fully anonymize your DB. Sensitive data includes: credit cards, social security IDs, resumes, profile submissions. You need to be your own data expert here.

### Contributions and complaints

* Contributions are welcome via the GitHub project page. You know what to do. Bug reports are contributions.
* Complaints are welcome! A NZD 150 processing fee is required in advance. [Send NZD$150 to my PayPal account](https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=WB3P25G5WV692) and I'll be in touch.

## Installation

There's no release yet, which means you need to clone the git repo and then run `composer install`.

## How to use

Congratulations, you made it past the pseudolegal boilerplate! Sorry if that freaked you out.

Examples below use Drush, but you can of course use any CiviCRM API interface you prefer.

### Bulk anonymization

This command will anonymize the entire database, based on default settings.

    cv api database.anonymize
    
This command will print all the SQL queries that *would* run, but it won't run them. If you like, you can pipe this output to `mysql` or redirect it to a file for running later.

    cv api database.anonymize --out=none dry-run=1


### Piecemeal anonymization

#### Anonymize a single contact

Use the Contact.anonymize API to anonymize a contact. Identify the target contact by id, eg

    cv api Contact.anonymize id=1234

#### Anonymize an email or address

If you anonymize the related contact, this will happen automatically, but you can directly anonymize these entities also. The id here is the Email or Address id, not the parent entity.

    cv api Email.anonymize id=12345

### Locales

Some Anonymise methods accept a locale, so you can generate names and addresses that match your demographic.

    cv api Contact.anonymize id=1234 locale=fr_FR
