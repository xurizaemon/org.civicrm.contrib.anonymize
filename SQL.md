# SQL methods

A grab bag of SQL approaches to do something similar to what the extension provides. It won't be as fancy, but it'll probably be faster.

This extension is in the process of implementing a direct SQL approach. Each approach has merits:

**Faker/API** - This approach generates better output. It's possible to generate almost plausible names in SQL alone (see [#8](https://github.com/xurizaemon/org.civicrm.contrib.anonymize/issues/8)), but they will still look pretty fake, and the level of review will decrease. If the test data seems plausibly realistic, a human reviewer will be much more able to identify errors and anomalies.

**SQL** - SQL is faster. The speed of iterating API updates against each contact just doesn't compare to a bulk SQL update.

A third approach is to reduce your test DB to a small subset, then use the "nice but slow" Faker/API approach to anonymize.

## obfuscate_db.php

From years back, here's an ugly SQL approach to munging `encryptDB.php` output to produce slightly more normal names.

* https://github.com/xurizaemon/org.civicrm.contrib.anonymize/blob/obfuscate_db/obfuscate_db.php

## Compucorp's `civicrmanonymisation`

* [civicrmanonymisation repository](https://github.com/compucorp/civicrmanonymisation/)
* [anonymiseCiviCRM.sql](https://github.com/compucorp/civicrmanonymisation/blob/master/anonymiseCiviCRM.sql)

## Replace all emails quickly

    -- Drupal name column
    UPDATE users SET name = CONCAT(LEFT(name, INSTR(name, '@')), '+', uid, 'example.net') WHERE name LIKE '%@%';
    -- Drupal users table.
    UPDATE users SET mail = CONCAT(LEFT(mail, INSTR(mail, '@')), 'example.net') WHERE mail LIKE '%@%';

    -- CiviCRM email table.
    UPDATE civicrm_email SET email = CONCAT(LEFT(email, INSTR(email, '@')), 'example.net') WHERE email LIKE '%@%';
