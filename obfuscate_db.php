#!/usr/bin/php
<?php
/*
 * Based on encryptDB.php from CiviCRM v4.3.
 * 
 * This file generates a set of SQL commands which can be piped to a
 * DB, thus allowing us to sidestep the requirement of bootstrapping
 * CiviCRM.
 *
 * It adds salt to the hashes, which are otherwise trivial to reverse
 * (eg "f5bcaaa202075bfd3d33b9664f4191df, a12cb17654c0eb7332a42aa8413cc5dd" 
 * is "Burgess, Chris"). This looks incomprehensible, but it only 
 * takes a list of common first/last names to deanonymise your DB.
 *
 * @TODO Generate pronounceable names.
 *
 * @TODO Set "Display name" to match the data generated (currently it generates its own obfuscated string unrelated to the contact's first/last name).
 *
 * @TODO Obfuscate these fields / displays:
 * - Contact summary shows email greeting, postal greeting, addressee, source.
 * - 
 *
 * @TODO Obfuscate these fields in ways which pass validation:
 * - Email.
 * - Website.
 *
 * @TODO Obfuscate the address in a way which passes geocoding sometimes. (Hmm.)
 *
 * For Drupal DBs, check out http://drupalscout.com/knowledge-base/creating-sanitized-drupal-database-backup
 */

define('CRM_ENCRYPT', 1);
define('CRM_SETNULL', 2);
define('CRM_ENCRYPT_EMAIL', 3);

function encryptDB() {
  $salt = md5(rand().microtime());
  $tables = array(
    'civicrm_contact' => array(
      'first_name' => CRM_ENCRYPT,
      'last_name' => CRM_ENCRYPT,
      'organization_name' => CRM_ENCRYPT,
      'household_name' => CRM_ENCRYPT,
      'sort_name' => CRM_ENCRYPT,
      'display_name' => CRM_ENCRYPT,
      'legal_name' => CRM_ENCRYPT,
      'addressee_display' => CRM_ENCRYPT,
      'postal_greeting_custom' => CRM_ENCRYPT,
      'email_greeting_display' => CRM_ENCRYPT,
      'birth_date' => CRM_SETNULL,
      'source' => CRM_ENCRYPT,
      'image_URL' => CRM_SETNULL,
    ),
    'civicrm_address' => array(
      'street_address' => CRM_ENCRYPT,
      'supplemental_address_1' => CRM_ENCRYPT,
      'supplemental_address_2' => CRM_ENCRYPT,
      'city' => CRM_ENCRYPT,
      'postal_code' => CRM_SETNULL,
      'postal_code_suffix' => CRM_SETNULL,
      'geo_code_1' => CRM_SETNULL,
      'geo_code_2' => CRM_SETNULL,
    ),
    'civicrm_website' => array(
      'url' => CRM_ENCRYPT,
    ),
    'civicrm_email' => array(
      'email' => CRM_ENCRYPT_EMAIL,
    ),
    'civicrm_phone' => array(
      'phone' => CRM_ENCRYPT,
    ),
  );

  foreach ($tables as $tableName => $fields) {
    $clauses = array();
    foreach ($fields as $fieldName => $action) {
      switch ($action) {
        case CRM_ENCRYPT:
          // bbchgsdp
          $clauses[] = "  $fieldName = LCASE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LEFT(MD5(CONCAT('$salt',$fieldName)),8),'0','O'),'1','I'),'2','Z'),'3','B'),'4','H'),'5','S'),'6','G'),'7','T'),'8','0'),'9','P'))";
          break;
          
        case CRM_ENCRYPT_EMAIL:
          // bcsdpizh@example.org
          $clauses[] = "  $fieldName = CONCAT(LCASE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LEFT(MD5(CONCAT('$salt',$fieldName)),8),'0','o'),'1','i'),'2','z'),'3','b'),'4','h'),'5','s'),'6','g'),'7','t'),'8','b'),'9','p')),'@example.org')";
          break;

        case CRM_SETNULL:
          $clauses[] = "  $fieldName = NULL";
          break;
      }
    }

    if (!empty($clauses)) {
      $clause = implode(",\n", $clauses);
      $query = "UPDATE $tableName SET \n$clause;\n";
      echo $query;
    }
  }
}

encryptDB();

