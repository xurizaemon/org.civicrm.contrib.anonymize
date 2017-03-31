<?php

/**
 * Database.Anonymize API specification (optional)
 * This is used for documentation and validation.
 *
 * @param array $spec description of fields supported by this API call
 * @return void
 * @see http://wiki.civicrm.org/confluence/display/CRMDOC/API+Architecture+Standards
 */
function _civicrm_api3_database_Anonymize_spec(&$spec) {
  $spec['locale']['api.required'] = 0;
  $spec['id-min']['api.required'] = 1;
  $spec['id-max']['api.required'] = 1;
}

/**
 * Database.Anonymize API
 *
 * @param array $params
 * @return array API result descriptor
 * @see civicrm_api3_create_success
 * @see civicrm_api3_create_error
 * @throws API_Exception
 */
function civicrm_api3_database_Anonymize($params) {
  $idMin = (int) $params['id-min'];
  $idMax = (int) $params['id-max'];
  for ($id = $idMin; $id <= $idMax; $id++) {
    civicrm_api3('Contact', 'anonymize', array('id' => $id));
  }
}
