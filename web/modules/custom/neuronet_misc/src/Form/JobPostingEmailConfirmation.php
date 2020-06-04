<?php

namespace Drupal\neuronet_misc\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\TempStore\PrivateTempStore;
use Drupal\Core\TempStore\PrivateTempStoreFactory;
use Drupal\Core\Url;
use Drupal\neuronet_misc\Service\JobPostingEmails;
use Drupal\node\NodeInterface;
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
    // Get recipients & job node.
    $recipent_nids = $this->jobPostingEmails->getRecipients();
    $job = $this->tempStore->get('job_posting_node');
    // Build form.
    $form['message'] = [
      '#type' => 'markup',
      '#markup' => '<h3>' . $this->t('You are about to send an email to everyone in the system that meets the following criteria (@num people):', ['@num' => count($recipent_nids)]) . '</h3>',
    ];
    $form['details'] = [
      '#type' => 'markup',
      '#markup' =>
      '<ul>
        <li>' . $this->t('Job: @title', ['@title' => $job->getTitle() ]) . '</li>
        <li>' . $this->t('Career Stages: @stages', ['@stages' => implode(', ', $this->extractStageNames($job))]) . '</li>
      </ul>',
    ];
    $form['actions']['submit_button'] = [
      '#type' => 'submit',
      '#attributes' => [
        'class' => [
          'btn-danger',
          'btn'
        ],
      ],
      '#value' => $this->t('I Am Sure'),
    ];
    $form['actions']['cancel'] = [
      '#type' => 'link',
      '#title' => t('Cancel'),
      '#url' => Url::fromUserInput($this->tempStore->get('job_posting_redirect_destination')),
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

  /**
   * Extract names of career stages
   *
   * @param NodeInterface $job
   * @return array
   */
  protected function extractStageNames(NodeInterface $job) {
    $allowed_stages = $job->get('field_stage')->getFieldDefinition()->getSettings()['allowed_values'];
    $stage_values = array_column($job->get('field_stage')->getValue(), 'value');
    $stage_values = array_combine($stage_values, $stage_values);
    return array_intersect_key($allowed_stages, $stage_values);
  }
}