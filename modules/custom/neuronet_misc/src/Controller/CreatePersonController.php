<?php

namespace Drupal\neuronet_misc\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Form\FormStateInterface;

class CreatePersonController extends ControllerBase {

  /**
   * Display the markup.
   *
   * @return array
   */
  public function content() {
    $values = array('type' => 'profile');
    $node = \Drupal::entityTypeManager()
      ->getStorage('node')
      ->create($values);
    $formObject = \Drupal::entityTypeManager()
      ->getFormObject('node', 'default')
      ->setEntity($node);
    $form = \Drupal::formBuilder()->getForm($formObject);
    return [
      'node_add_form' => $form,
    ];
  }

}