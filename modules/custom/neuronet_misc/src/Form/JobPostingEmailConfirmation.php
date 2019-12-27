<?php

namespace Drupal\neuronet_misc\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\TempStore\PrivateTempStore;
use Drupal\Core\TempStore\PrivateTempStoreFactory;
use Drupal\neuronet_misc\Service\JobPostingEmails;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Implements a Job Posting Email Confirmation form.
 */
class JobPostingEmailConfirmation extends FormBase {

  /**
   * The tempstore object.
   *
   * @var \Drupal\Core\TempStore\SharedTempStore
   */
  protected $tempStore;

  /**
   * Job posting emails service
   *
   * @var JobPostingEmails
   */
  protected $jobPostingEmails;

  /**
   * Constructs JobPostingEmailConfirmation object.
   *
   * @var PrivateTempStore $tempStore
   */
  public function __construct(PrivateTempStoreFactory $tempStoreFactory, JobPostingEmails $JobPostingEmails) {
    $this->tempStore = $tempStoreFactory->get('neuronet_misc');
    $this->jobPostingEmails = $JobPostingEmails;
  }

  /**
   * Overrides create() method from ConfigFormBase
   *
   * @param ContainerInterface $container
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('tempstore.private'),
      $container->get('neuronet_misc.job_posting_emails')
    );
  }

  /**
   * {@inheritdoc}.
   */
  public function getFormId() {
    return 'job_posting_email_confirmation';
  }

  /**
   * {@inheritdoc}.
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $recipent_nids = $this->jobPostingEmails->getRecipients();
    $form['message'] = [
      '#type' => 'markup',
      '#markup' => '<h3>' . $this->t('You are about to send an email to everyone in the system that meets the following criteria:') . '</h3>',
    ];
    $form['submit_button'] = [
      '#type' => 'submit',
      '#attributes' => [
        'class' => [
          'btn-danger',
          'btn'
        ],
      ],
      '#value' => $this->t('I Am Sure'),
    ];

    return $form;
  }


  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->jobPostingEmails->handleConfirmationSubmit();
  }
}