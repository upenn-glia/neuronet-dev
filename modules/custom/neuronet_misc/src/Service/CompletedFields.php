<?php

namespace Drupal\neuronet_misc\Service;

use Drupal\node\NodeInterface;

class CompletedFields {

  /**
   * Save completed fields
   *
   * @param NodeInterface $node
   */
  public static function saveCompletedFields($node) {
    \Drupal::database()
      ->merge('completed_profile_fields')
      ->key(['nid' => $node->id()])
      ->fields([
        'num_completed_fields' => self::getCompletedFields($node),
      ])
      ->execute();
  }

  /**
   * Get number of fields that are not empty
   *
   * @param NodeInterface $node
   * @return integer
   */
  public static function getCompletedFields($node) {
    $field_names = preg_grep('/^field\w*/', array_keys($node->toArray()));
    $fields_and_values = array_intersect_key($node->toArray(), array_flip($field_names));
    $non_empty_fields = array_filter($fields_and_values);
    return count($non_empty_fields);
  }
}