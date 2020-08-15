<?php

namespace Drupal\neuronet_misc\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\views\Views;

class NeuronetController extends ControllerBase {

  /**
   * Display the markup.
   *
   * @return array
   */
  public function content() {
    $view = Views::getView('current_students');
    $view->setDisplay('page_2');
    $render_view = $view->render();
    return $render_view;
  }

}