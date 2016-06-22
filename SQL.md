# SQL methods

This file is just a grab bag of SQL approaches to do something similar to what the extension provides. It won't be as fancy, but it'll probably be faster.

## Replace all emails quickly

    -- Drupal name column
    UPDATE users SET name = CONCAT(LEFT(name, INSTR(name, '@')), '+', uid, 'example.net') WHERE name LIKE '%@%';
    -- Drupal users table.
    UPDATE users SET mail = CONCAT(LEFT(mail, INSTR(mail, '@')), 'example.net') WHERE mail LIKE '%@%';

    -- CiviCRM email table.
    UPDATE civicrm_email SET email = CONCAT(LEFT(email, INSTR(email, '@')), 'example.net') WHERE email LIKE '%@%';
