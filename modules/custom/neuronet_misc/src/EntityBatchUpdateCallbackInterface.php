<?php

namespace Drupal\neuronet_misc;

use Drupal\Core\Entity\EntityInterface;

/**
 * Provides an interface for a callback service for EntityBatchUpdate.
 */
interface EntityBatchUpdateCallbackInterface {

  /**
   * Any class that implements this interface must process the entity
   * during the update
   *
   * @param EntityInterface $entity
   */
  public function processUpdate(EntityInterface $entity);
}