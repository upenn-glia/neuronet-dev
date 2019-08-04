<?php

namespace Drupal\neuronet_misc\Form;

use Drupal\Core\Form\ConfirmFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Drupal\Core\TempStore\PrivateTempStoreFactory;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\node\Entity\Node;
use Drupal\field\Entity\FieldConfig;
use Drupal\Core\State\State;
use Symfony\Component\HttpFoundation\Request;
use Drupal\Core\Path\PathValidator;

/**
 * Provides a confirmation form for cancelling multiple user accounts.
 *
 * @internal
 */
class SelectEmailToSend extends ConfirmFormBase {

  /**
   * The temp store factory.
   *
   * @var \Drupal\Core\TempStore\PrivateTempStoreFactory
   */
  protected $tempStoreFactory;

  /**
   * Selected nodes
   *
   * @var array
   */
  protected $selectedNodes;

  /**
   * State service
   *
   * @var State
   */
  protected $state;

  /**
   * Selected node ids
   *
   * @var array
   */
  protected $nids;

  /**
   * Previous Route name
   *
   * @var string
   */
  protected $previousRoute = 'entity.node.collection';


  /**
   * Constructs a new SelectEmailToSend.
   *
   * @param \Drupal\Core\TempStore\PrivateTempStoreFactory $temp_store_factory
   *   The temp store factory.
   *   The user storage.
   */
  public function __construct(PrivateTempStoreFactory $temp_store_factory, State $State) {
    $this->tempStoreFactory = $temp_store_factory;
    $this->state = $State;
    $temp_vars = $this->tempStoreFactory
      ->get('send_custom_email')
      ->get($this->currentUser()->id());
    $this->nids = !empty($temp_vars['nids']) ? $temp_vars['nids'] : false;
    $this->previousRoute = !empty($temp_vars['previous_route']) ? $temp_vars['previous_route'] : false;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('tempstore.private'),
      $container->get('state')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'neuronet_misc.select_custom_email';
  }

  /**
   * {@inheritdoc}
   */
  public function getQuestion() {
    return $this->t('Choose the custom email that should be sent.');
  }

  /**
   * {@inheritdoc}
   */
  public function getCancelUrl() {
    return new Url($this->previousRoute);
  }

  /**
   * {@inheritdoc}
   */
  public function getConfirmText() {
    return $this->t('Confirm the custom email to send.');
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $this->messenger()->deleteAll();
    $config = $this->state->get('neuronet_misc.custom_emails');
    if (empty($config['emails_container'])) {
      $this->messenger()->addWarning($this->t('There are no custom emails saved. Add one.'));
      return $this->redirect('neuronet_misc.custom_emails');
    }
    // Redirect to /admin/content if no node IDs.
    if (!$this->nids) {
      $this->messenger()->addWarning($this->t('You have not selected any users to send emails to.'));
      return $this->redirect($this->previousRoute);
    }
    $form['nodes_container'] = [
      '#type' => 'container',
      '#prefix' => '<ul><strong>' . $this->t('Select Custom Email to Send These People:') . '</strong>',
      '#suffix' => '</li>'
    ];
    // Load all nodes titles from selected content.
    foreach ($this->nids as $nid) {
      $node = Node::load($nid);
      $this->selectedNodes[] = $node;
      $form['nodes_container']['node_' . $nid] = array(
        '#type' => 'markup',
        '#markup' => '<li>' . $node->getTitle() . '</li>',
      );
    }
    // Set select options.
    $options = [];
    foreach ($config['emails_container'] as $email) {
      $options[] = $email['name'];
    }
    $form['custom_email_selected'] = [
      '#type' => 'select',
      '#required' => true,
      '#title' => $this->t('Custom Emails to Send'),
      '#options' => $options,
    ];
    $form = parent::buildForm($form, $form_state);
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // Transform selected consumers into a format we can set to the node field value.
    // $consumers_to_set = [];
    // foreach ($form_state->getValue('custom_email_selected') as $external_consumer => $value) {
    //   if ($value !== 0) {
    //     $consumers_to_set[] = $external_consumer;
    //   }
    // }
    // $form_state->getValue('custom_email_selected');
    // // Set node field values.
    // foreach ($this->selectedNodes as $node) {
    //   $node->set('field_external_consumers', empty($consumers_to_set)? null : $consumers_to_set);
    //   $node->save();
    // }
    // Clear out the accounts from the temp store.
    $this->tempStoreFactory->get('send_custom_email')->delete($this->currentUser()->id());
    // Redirect to previous route.
    $this->messenger()->addMessage($this->t('IT WORKED!'));
    $form_state->setRedirect($this->previousRoute);
  }

}