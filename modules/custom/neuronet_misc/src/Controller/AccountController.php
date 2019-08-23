<?php

namespace Drupal\neuronet_misc\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\user\ProfileForm;
use Drupal\Core\Access\AccessResult;
use Drupal\Core\Session\AccountInterface;
use \Symfony\Component\Routing\Route;
use Drupal\node\NodeInterface;
use \Drupal\user\Entity\User;

class AccountController extends ControllerBase {

  /**
   * Display the markup.
   *
   * @return array
   */
  public function content(NodeInterface $node) {
    $connection = \Drupal::database();
    $query = $connection->query("SELECT entity_id FROM {user__field_profile} WHERE field_profile_target_id = :field_profile_target_id", [':field_profile_target_id' => $node->id()]);
    $result = $query->fetchAll();
    $uid = $result[0]->entity_id;
    $user = User::load($uid);
    $formObject = \Drupal::entityManager()
      ->getFormObject('user', 'default')
      ->setEntity($user);
    $form = \Drupal::formBuilder()->getForm($formObject);
    return [
      'user_edit_form' => $form,
    ];
  }

   /**
   * Checks access for a specific request.
   *
   * @param \Drupal\Core\Session\AccountInterface $account
   *   Run access checks for this account.
   */
  public function access(AccountInterface $account, Route $route, NodeInterface $node) {
    // Check permissions and combine that with any custom access checking needed. Pass forward
    // parameters from the route and/or request as needed.
    //if node id is the same as the entity reference in the user
    $user = User::load($account->id());
    $access = false;
    if (!$user->get('field_profile')->isEmpty() && $node->id() == $user->get('field_profile')->target_id) {
      $access = true;
    }
    elseif ($user->hasRole('deputy_admin')) {
      $entities = \Drupal::entityTypeManager()->getStorage('user')->loadByProperties([
        'field_profile' => $node->id(),
      ]);
      if (!empty($entities)) {
        $viewed_user = reset($entities);
        if ($viewed_user->id() !== 1 && !$viewed_user->hasRole('administrator')) {
          $access = true;
        }
      }
    }
    elseif ($user->id() == 1 || ($user->hasRole('administrator') && $viewed_user->id() !== 1)) {
      $access = true;
    }
    return AccessResult::allowedIf($account->hasPermission('access content') && $access);
  }
}