<?php

namespace Drupal\neuronet_misc\Service;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\node\NodeInterface;
use Drupal\neuronet_misc\EntityBatchUpdateCallbackInterface;

/**
 * Service to set an existing job history item author to be the author of the
 * profile it's associated with
 * 
 * - Used in neuronet_misc_update_8005()
 */
class JobAuthorship implements EntityBatchUpdateCallbackInterface {

  /**
   * Entity type manager service
   *
   * @var EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Constructs JobAuthorship object
   */
  public function __construct(EntityTypeManagerInterface $EntityTypeManager) {
    $this->entityTypeManager = $EntityTypeManager;
  }

  /**
   * Process update, required method by interface
   *
   * @param NodeInterface $node
   */
  public function processUpdate(EntityInterface $node) {
    $this->setJobAuthorToProfileUser($node);
  }

  /**
   * Sets the job node author to the user associated w/ the profile field value
   *
   * @param NodeInterface $node
   */
  public function setJobAuthorToProfileUser($node) {
    // Get profile. Then get user. Set that user as the author of the node & save.
    $test = '';
    if (
      (!$node->get('field_profile')->isEmpty()) &&
      ($profile = $this->entityTypeManager->getStorage('node')->load($node->get('field_profile')->first()->target_id)) &&
      ($users = $this->entityTypeManager->getStorage('user')->loadByProperties(['field_profile' => $profile->id()])) &&
      ($user = reset($users))
    ) {
      $node->setOwnerId($user->id());
      $node->save();
    }
  }

}