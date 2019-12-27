<?php

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
 * @param \Drupal\Core\Form\FormStateInterface $form_state
 */
function _neuronet_misc_redirect_job_posting($form, \Drupal\Core\Form\FormStateInterface $form_state) {
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