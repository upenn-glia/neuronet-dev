<?php

use Drupal\Core\Form\FormStateInterface;
use Drupal\neuronet_misc\Service\JobPostingEmails;
use Drupal\node\NodeInterface;
use Drupal\user\PrivateTempStoreFactory;

/**
 * @file
 * Helper functions for neuronet_misc.module
 */

/**
 * Set job posting node to private temp store
 *
 * - Only if field_send_email_notifications is true.
 * - Assists the processing of email notifications.
 *
 * @param NodeInterface $node
 */
function _neuronet_misc_set_job_posting_temp_store(NodeInterface $node) {
  if ($node->getType() === 'job_posting' && $node->get('field_send_email_notifications')->value) {
    /** @var PrivateTempStoreFactory $tempstore */
    $tempstore = \Drupal::service('tempstore.private');
    $store = $tempstore->get('neuronet_misc');
    $store->set('job_posting_node', $node);
  }
}

/**
 * Redirect job posting to send email confirmation form
 *
 * - Also store the destination parameter
 * - Also select recipients for confirmation form
 *
 * @param array $form
 * @param FormStateInterface $form_state
 */
function _neuronet_misc_redirect_job_posting($form, FormStateInterface $form_state) {
  if ($form_state->getValue('field_send_email_notifications')['value']) {
    /** @var PrivateTempStoreFactory $tempstore */
    $tempstore = \Drupal::service('tempstore.private');
    $store = $tempstore->get('neuronet_misc');
    $store->set('job_posting_redirect_destination', \Drupal::request()->query->get('destination'));
    \Drupal::request()->query->remove('destination');
    $form_state->setRedirect('neuronet_misc.job_posting_email_confirmation');
    /** @var JobPostingEmails $job_posting_emails */
    $job_posting_emails = \Drupal::service('neuronet_misc.job_posting_emails');
    $job_posting_emails->selectRecipients();
  }
}

/**
 * Callback submit function for profiles
 *
 * - Redirects to the create person form, for NeuroNet managers.
 *
 * @param array $form
 * @param FormStateInterface $form_state
 */
function _neuronet_misc_profile_submit(array $form, FormStateInterface &$form_state){
  $form_state->setRedirect('neuronet_misc.create_person');
}

/**
 * Validate new profile form
 *
 * - Sets form errors if form fails to validate.
 * - Looks to make sure:
 *    - Emails don't already exist.
 *    - Combinations of first & last names don't already exist.
 * @param array $form
 * @param FormStateInterface $form_state
 */
function _neuronet_misc_profile_validate(array $form, FormStateInterface &$form_state) {
  $email = $form_state->getValue('field_email');
  $email = $email[0]['value'];
  if (user_load_by_mail($email) != FALSE) {
    $form_state->setErrorByName('field_email', t('That email already exists.'));
  }
  $firstname = $form_state->getValue('field_first_name'); $firstname = $firstname[0]['value'];
  $lastname = $form_state->getValue('field_last_name'); $lastname = $lastname[0]['value'];
  if (user_load_by_name($firstname . ' ' . $lastname) != FALSE) {
    $form_state->setErrorByName('field_first_name', t('That combination of first and last names already exists.'));
  }
}