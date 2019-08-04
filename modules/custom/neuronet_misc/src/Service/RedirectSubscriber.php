<?php

namespace Drupal\neuronet_misc\Service;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\KernelEvents;
use Drupal\Core\Routing\TrustedRedirectResponse;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;

class RedirectSubscriber implements EventSubscriberInterface {
  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    return([
      KernelEvents::RESPONSE => [
        ['redirectToProfile'],
      ]
    ]);
  }
  /**
   * Redirect requests for the user page to the associated
   * node profile page
   *
   * @param $event
   */
  public function redirectToProfile(FilterResponseEvent $event) {
    $request = $event->getRequest();
    global $base_url;
    // redirect user page to profile
    if (!is_null($request->attributes->get('user')) && is_null($request->query->get('pass-reset-token'))) {
     $user = $request->attributes->get('user');
     if(is_object($user)) {
       $connection = \Drupal::database();
       $query = $connection->query("SELECT field_profile_target_id
       FROM {user__field_profile}
       WHERE entity_id = :entity_id", [':entity_id' => $user->id()]);
       $result = $query->fetchAll();
       if (!empty($result)) {
         $nid = $result[0]->field_profile_target_id;
         $alias = \Drupal::service('path.alias_manager')->getAliasByPath('/node/'.$nid);
         $response = new TrustedRedirectResponse($base_url . $alias);
         return $response->send();
       }
     }
    }
  }
}