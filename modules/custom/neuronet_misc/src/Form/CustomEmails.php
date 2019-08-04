<?php

namespace Drupal\neuronet_misc\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\State\State;
use Drupal\Core\TempStore\PrivateTempStoreFactory;

class CustomEmails extends ConfigFormBase {

  /**
   * State service
   *
   * @var State
   */
  protected $state;

  /**
   * The tempstore object.
   *
   * @var \Drupal\Core\TempStore\SharedTempStore
   */
  protected $tempStore;

  /**
   * Constructs CustomEmails object.
   *
   * @var State $State
   */
  public function __construct(State $State, PrivateTempStoreFactory $tempStoreFactory) {
    $this->state = $State;
    $this->tempStore = $tempStoreFactory;
  }

  /**
   * Overrides create() method from ConfigFormBase
   *
   * @param ContainerInterface $container
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('state'),
      $container->get('tempstore.private')
    );
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'neuronet_misc.custom_emails',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'neuronet_misc_custom_emails';
  }

  /**
   * Property: Number of API items in form
   *
   * @var integer
   */
  protected $emailItemTotal = 1;

  /**
   * Property: Whether the Add New Email was pressed
   *
   * @var boolean
   */
  protected $ajaxPressed = false;

  /**
   * Property: Whether the Remove Email button was pressed
   *
   * @var boolean
   */
  protected $removePressed = false;

  /**
   * Temporary config, to be used by the Remove Email button.
   *
   * @var array
   */
  protected $tempConfig = [];

  /**
   * Item id to remove.
   *
   * @var integer
   */
  protected $itemToRemove;

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    // get config
    $config = $this->state->get('neuronet_misc.custom_emails');
    // make sure to use tree
    $form['#tree'] = TRUE;
    // Disable caching on this form.
    $form_state->setCached(FALSE);
    $form['form_title'] = [
      '#type'       => 'markup',
      '#markup' => '<h2>Custom Emails</h2>',
    ];
    $form['emails_container'] = [
      '#type'       => 'container',
      '#attributes' => ['id' => 'emails-container'],
      '#title' => $this->t('Custom Emails'),
    ];
    // get number of apis already loaded into config
    if (!empty($config['emails_container']) && !$this->ajaxPressed) {
      $this->emailItemTotal = count($config['emails_container']) > 0 ? count($config['emails_container']) : 1;
    }
    // Set config to tempConfig if remove button is pressed.
    if ($this->removePressed) {
      $config = $this->tempConfig;
    }
    // build api container
    for ($i = 1; $i <= $this->emailItemTotal; $i++) {
      // select content type
      $form['emails_container'][$i] = [
        '#type'       => 'fieldset',
      ];
      $form['emails_container'][$i]['name'] = [
        '#type'       => 'textfield',
        '#title' => $this->t('Email Name'),
        '#default_value' => empty($config['emails_container'][$i]['name']) ? '' : $config['emails_container'][$i]['name'],
        '#required' => true,
      ];
      $form['emails_container'][$i]['subject'] = [
        '#type'       => 'textfield',
        '#title' => $this->t('Email Subject'),
        '#default_value' => empty($config['emails_container'][$i]['subject']) ? '' : $config['emails_container'][$i]['subject'],
        '#required' => true,
      ];
      $form['emails_container'][$i]['email'] = [
        '#type'       => 'text_format',
        '#title' => $this->t('Email Body'),
        '#default_value' => empty($config['emails_container'][$i]['email']['value']) ? '' : $config['emails_container'][$i]['email']['value'],
        '#required' => true,
        '#description' => $this->t('You can insert the following variable placeholders,
        which will be replaced by their user-specific values upon email send:
          %%%FIRSTNAME%%% %%%LASTNAME%%% %%%LOGINLINK%%%'),
      ];
      // Remove button.
      $form['emails_container'][$i]['remove_item_' . $i] =[
        '#type'                    => 'submit',
        '#name'                    => 'remove_' . $i,
        '#value'                   => $this->t('Remove Email'),
        '#submit'                  => ['::_custom_emails_remove_item'],
        // Since we are removing an item, don't validate until later.
        '#limit_validation_errors' => [],
        '#ajax'                    => [
          'callback' => [$this, 'ajax_callback'],
          'wrapper'  => 'emails-container',
        ],
      ];
    }
      $form['emails_container']['actions'] = [
        '#type' => 'actions',
      ];
      $form['emails_container']['actions']['add_item'] = [
        '#type'   => 'submit',
        '#value'  => $this->t('Add a New Email'),
        '#submit' => ['::_custom_emails_add_item'], // very oddly the [$this, '_custom_emails_add_item'] format does not work here ...
        '#ajax'   => [
          'callback' => [$this, 'ajax_callback'],
          'wrapper'  => 'emails-container',
        ],
      ];
    return parent::buildForm($form, $form_state);
  }

  /**
   * Implements callback for Ajax event
   *
   * @param array $form
   *   From render array.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   Current state of form.
   *
   * @return array
   *   Container section of the form.
   */
  public function ajax_callback($form, $form_state) {
    // Set new values if remove was pressed.
    if ($this->removePressed) {
      // Get input values;
      $values = $form_state->getUserInput();
      // Remove the removed item;
      unset($values['emails_container'][$this->itemToRemove]);
      $values['emails_container'] = array_combine(range(1, count($values['emails_container'])), array_values($values['emails_container']));
      // Set new values;
      for ($i = 1; $i <= $this->emailItemTotal; $i++) {
        $form['emails_container'][$i]['email']['#value'] = empty($values['emails_container'][$i]['email']['value']) ? '' : $values['emails_container'][$i]['email']['value'];
        $form['emails_container'][$i]['name']['#value'] = empty($values['emails_container'][$i]['name']) ? '' : $values['emails_container'][$i]['name'];
      }
    }
    return $form['emails_container'];
  }

  /**
   * Adds an API item to form
   *
   * @param array $form
   * @param FormStateInterface $form_state
   */
  public function _custom_emails_add_item(array &$form, FormStateInterface $form_state) {
    $this->ajaxPressed = true;
    $this->emailItemTotal++;
    $form_state->setRebuild();
  }

  /**
   * Removes an API item from form
   *
   * @param array $form
   * @param FormStateInterface $form_state
   */
  public function _custom_emails_remove_item(array &$form, FormStateInterface $form_state) {
    $this->ajaxPressed = true;
    $this->removePressed = true;
    $this->emailItemTotal--;
    // Get triggering item id;
    $triggering_element = $form_state->getTriggeringElement();
    preg_match_all('!\d+!', $triggering_element['#name'], $matches);
    $item_id = (int) $matches[0][0];
    $this->itemToRemove = $item_id;
    // Remove item from config, reindex at 1, and set tempConfig to it.
    $config = empty($this->tempConfig) ? $this->state->get('neuronet_misc.custom_emails') : $this->tempConfig;
    unset($config['emails_container'][$item_id]);
    $config['emails_container'] = array_combine(range(1, count($config['emails_container'])), array_values($config['emails_container']));
    $this->tempConfig = $config;
    // Rebuild form;
    $form_state->setRebuild();
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
    // Final submit
    parent::submitForm($form, $form_state);
    // get submitted values
    $values = $form_state->getValues();
    // remove the actions that we don't want to save to config
    if (isset($values['emails_container']['actions'])) {
      unset($values['emails_container']['actions']);
    }
    $this->state->set('neuronet_misc.custom_emails', $values);
    if ($path = $this->tempStore->get('send_custom_email__form_path')->get($this->currentUser()->id())) {
      $this->tempStore->get('send_custom_email__form_path')->delete($this->currentUser()->id());
      $form_state->setRedirect($path);
    }
  }
}