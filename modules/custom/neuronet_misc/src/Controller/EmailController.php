<?php

namespace Drupal\neuronet_misc\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\node\NodeInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use \Drupal\user\Entity\User;

class EmailController extends ControllerBase {

  /**
   * Display the markup.
   *
   * @return array
   */
  public function sendIndividualEmail(NodeInterface $node) {
    // /drupal_set_message($message = 'Login links emailed to all current students', $type = 'status', $repeat = FALSE);
    // see if they're a user
    $connection = \Drupal::database();
    $query = $connection->query("SELECT entity_id FROM {user__field_profile} WHERE field_profile_target_id = :field_profile_target_id", [':field_profile_target_id' => $node->id()]);
    $result = $query->fetchAll();
      if (!empty($result)) {
        $uid = $result[0]->entity_id;
        $user = User::load($uid);
        if (is_object($user)){
          $mailManager = \Drupal::service('plugin.manager.mail');
          $langcode = $user->getPreferredLangcode();
          $params['context']['subject'] = "Login Info for Neuronet";
          $link = user_pass_reset_url($user);
          $params['context']['message'] = _neuronet_alumnus_email_body($link);
          $to = "gwolfman@gmail.com";
          $mailManager->mail('system', 'mail', $to, $langcode, $params);
          drupal_set_message($message = 'Login link email sent to ' . $node->getTitle(), $type = 'status');
        }
      }
      else {
        drupal_set_message($node->getTitle() . ' does not have an account', $type = 'warning');
      }
      // if they're a user, then get body
      // now send email to the user email
    $request = \Drupal::request();
    $response = new RedirectResponse($request->headers->get('referer'));
    $response->send();
    exit;
  }

  /**
   * Display the markup.
   *
   * @return array
   */
  public function sendGroupEmail() {
    drupal_set_message($message = 'Login links emailed to all current students', $type = 'status', $repeat = FALSE);
    $request = \Drupal::request();
    $response = new RedirectResponse($request->headers->get('referer'));
    $response->send();
  }

}

/*function _neuronet_alumnus_email_body($link) {
  $body = "<p>Hi NGG alum!</p>
<p>NGG and GLIA are proud to announce the creation of NGG's very own online current student and alumni database, NeuroNet, and you're invited to be one of the first alumni users! At this point the site is pretty much done and just needs those finishing touches. If you could log-in to the site here: <a href='" . $link . "'>LOG INTO NEURONET</a> , update your profile, and take a look around the site and then let us know any questions/comments/concerns you have, we'd really appreciate it!</p>

<p>In particular we would like to know:</p>
<ul>
  <li>Do you find the site easy to log-in to?</li>
  <li>Are you content with the information displayed on the database?</li>
  <li>Would you use this database to look up other alumni and future NGG students?</li>
  <li>If the database grew to include a discussion board where you could place job postings or inquiries, would you use it?</li>
  <li>Are there any other functions or information you would like to see on this site?</li>
</ul>
<p>Please log-in to NeuroNet and let us know what you think! We're looking forward to your feedback!</p>

<p>Thanks for your time and efforts with this!</p>

<p>Alice Dallstream</p>
<p>The NeuroNet Committee (Matt Schaff, Alice Dallstream, Sydney Cason, Rebecca Somach, Jeni Stiso)</p>";
return $body;
}*/