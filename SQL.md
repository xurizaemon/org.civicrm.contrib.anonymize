# SQL methods

A grab bag of SQL approaches to do something similar to what the extension provides. It won't be as fancy, but it'll probably be faster.

This extension intentionally does not use a direct SQL approach. The motivation stems from an opinion that human testing / review will not be as effective or rigorous when the data being interacted with is obviously fake. It's possible to generate almost plausible names in SQL alone, but they will still look pretty fake, and the level of review will decrease. If the test data seems plausibly realistic, a human reviewer will be much more able to identify errors and anomalies.

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
