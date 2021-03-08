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
