<?php

namespace Drupal\neuronet_misc\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\RedirectResponse;

class RedirectTaxonomyController extends ControllerBase {

  /**
   * Display the markup.
   *
   * @return array
   */
  public function content() {
    $response = new RedirectResponse('/admin/structure/taxonomy_manager/voc');
    $response->send();
    exit;
  }
}