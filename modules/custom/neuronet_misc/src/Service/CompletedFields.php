<?php

namespace Drupal\neuronet_misc\Service;

use Drupal\Core\Database\Connection;
use Drupal\Core\Entity\EntityInterface;
use Drupal\node\NodeInterface;
use Drupal\neuronet_misc\EntityBatchUpdateCallbackInterface;

/**
 * Service to calculate and save completed fields for a profile
 */
class CompletedFields implements EntityBatchUpdateCallbackInterface {

  /**
   * Database service
   *
   * @var Connection
   */
  protected $database;

  /**
   * Constructs EntityBatchUpdate object
   */
  public function __construct(Connection $Connection) {
    $this->database = $Connection;
  }

  /**
   * Process update, required method by interface
   *
   * @param NodeInterface $node
   */
  public function processUpdate(EntityInterface $node) {
    $this->saveCompletedFields($node);
  }

  /**
   * Save completed fields
   *
   * @param NodeInterface $node
   */
  public function saveCompletedFields($node) {
    $this->database
      ->merge('completed_profile_fields')
      ->key(['nid' => $node->id()])
      ->fields([
        'num_completed_fields' => $this->getCompletedFields($node),
      ])
      ->execute();
  }

  /**
   * Get number of fields that are not empty
   *
   * @param NodeInterface $node
   * @param integer $subtract
   *   The number of completed fields to subtract from the final count.
   * @return integer
   */
  public function getCompletedFields($node, $subtract = 2) {
    $field_names = preg_grep('/^field\w*/', array_keys($node->toArray()));
    $fields_and_values = array_intersect_key($node->toArray(), array_flip($field_names));
    $non_empty_fields = array_filter($fields_and_values);
    return count($non_empty_fields) - $subtract;
  }
}