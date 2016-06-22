<?php
/**
 * @file
 * CiviCRM Anonymize extension.
 */

require_once 'anonymize.civix.php';

/**
 * Implements hook_civicrm_config().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_config
 */
function anonymize_civicrm_config(&$config) {
  _anonymize_civix_civicrm_config($config);
}

/**
 * Implements hook_civicrm_xmlMenu().
 *
 * @param array $files
 *   Array of XML files.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_xmlMenu
 */
function anonymize_civicrm_xmlMenu(&$files) {
  _anonymize_civix_civicrm_xmlMenu($files);
}

/**
 * Implements hook_civicrm_install().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_install
 */
function anonymize_civicrm_install() {
  _anonymize_civix_civicrm_install();
}

/**
 * Implements hook_civicrm_uninstall().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_uninstall
 */
function anonymize_civicrm_uninstall() {
  _anonymize_civix_civicrm_uninstall();
}

/**
 * Implements hook_civicrm_enable().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_enable
 */
function anonymize_civicrm_enable() {
  _anonymize_civix_civicrm_enable();
}

/**
 * Implements hook_civicrm_disable().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_disable
 */
function anonymize_civicrm_disable() {
  _anonymize_civix_civicrm_disable();
}

/**
 * Implements hook_civicrm_upgrade().
 *
 * @param $op string
 *   The type of operation being performed; 'check' or 'enqueue'.
 * @param $queue CRM_Queue_Queue
 *   For 'enqueue', the modifiable list of pending up upgrade tasks.
 *
 * @return mixed
 *   Based on op.
 *   For 'check', returns array(boolean) (TRUE if upgrades are pending).
 *   For 'enqueue', returns void.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_upgrade
 */
function anonymize_civicrm_upgrade($op, CRM_Queue_Queue $queue = NULL) {
  return _anonymize_civix_civicrm_upgrade($op, $queue);
}

/**
 * Implements hook_civicrm_managed().
 *
 * Generate a list of entities to create/deactivate/delete when this module
 * is installed, disabled, uninstalled.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_managed
 */
function anonymize_civicrm_managed(&$entities) {
  _anonymize_civix_civicrm_managed($entities);
}

/**
 * Implements hook_civicrm_caseTypes().
 *
 * Generate a list of case-types.
 *
 * Note: This hook only runs in CiviCRM 4.4+.
 *
 * @param array $casetypes
 *   Array of case types.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_caseTypes
 */
function anonymize_civicrm_caseTypes(&$casetypes) {
  _anonymize_civix_civicrm_caseTypes($casetypes);
}

/**
 * Implements hook_civicrm_angularModules().
 *
 * Generate a list of Angular modules.
 *
 * Note: This hook only runs in CiviCRM 4.5+. It may
 * use features only available in v4.6+.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_caseTypes
 */
function anonymize_civicrm_angularModules(&$angularmodules) {
  _anonymize_civix_civicrm_angularModules($angularmodules);
}

/**
 * Implements hook_civicrm_alterSettingsFolders().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_alterSettingsFolders
 */
function anonymize_civicrm_alterSettingsFolders(&$metadatafolders = NULL) {
  _anonymize_civix_civicrm_alterSettingsFolders($metadatafolders);
}
