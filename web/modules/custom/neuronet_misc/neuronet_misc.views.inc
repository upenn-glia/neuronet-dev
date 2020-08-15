<?php

/**
 * @file
 * 
 * Views hooks for neuronet_misc
 */

/**
 * Implements hook_views_data().
 */
function neuronet_misc_views_data() {
  // Expose SQL table data to Views for Completed Fields.
  $data = [];
  $data['completed_profile_fields']['table']['group'] = t('Completed Fields');
  $data['completed_profile_fields']['table']['base'] = [
    'field' => 'nid',
    'title' => t('Completed Fields'),
    'access query tag' => 'node_access',
  ];
  $data['completed_profile_fields']['table']['join'] = [
    'node_field_data' => [
      'left_field' => 'nid',
      'field' => 'nid',
    ],
  ];
  $data['completed_profile_fields']['num_completed_fields'] = [
    'title' => t('Number of Completed Fields'),
    'help' => t('The number of node fields that have been completed.'),
    'field' => [
      'id' => 'numeric',
    ],
    'filter' => [
      'id' => 'numeric',
    ],
    'argument' => [
      'id' => 'numeric',
    ],
    'sort' => [
      'id' => 'standard',
    ],
  ];
  return $data;
}


/**
 * Implements hook_views_data_alter().
 * Attaches the custom field "latest_job" to nodes.
 */
function neuronet_misc_views_data_alter(array &$data) {
  $data['node']['latest_job'] = [
    'title' => t('Latest job info'),
    'field' => [
      'title' => t('Latest job info'),
      'help' => t('Information about the most recent job held by this alum'),
      'id' => 'latest_job',
      'real field' => 'nid'
    ],
  ];
}