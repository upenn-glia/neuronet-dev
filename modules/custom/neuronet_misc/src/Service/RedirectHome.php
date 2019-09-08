<?php

namespace Drupal\neuronet_misc\Service;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\KernelEvents;
use Drupal\user\Entity\User;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;

class RedirectHome implements EventSubscriberInterface {
  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    return([
      KernelEvents::RESPONSE => [
        ['redirectHome'],
      ]
    ]);
  }
  /**
   * Redirect requests for the front page
   *
   * @param $event
   */
  public function redirectHome(FilterResponseEvent $event) {
    $request = $event->getRequest();
    if (\Drupal::service('path.matcher')->isFrontPage()) {
      $user = User::load(\Drupal::currentUser()->id());
      if ($user->isAnonymous()) {
        $response = new RedirectResponse(\Drupal::url('user.page'));
        return $response->send();
      }
      if ($user->hasRole('current_student') || $user->hasRole('administrator')) {
        $response = new RedirectResponse(\Drupal::url('view.current_students.page_1'));
        return $response->send();
      }
      else {
        $response = new RedirectResponse(\Drupal::url('user.page'));
        return $response->send();
      }

    }
  }
}