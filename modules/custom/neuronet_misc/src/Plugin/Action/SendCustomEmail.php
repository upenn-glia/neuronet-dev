<?php

namespace Drupal\neuronet_misc\Plugin\Action;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\TempStore\PrivateTempStoreFactory;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Action\ActionBase;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Url;
use Drupal\Core\Path\PathValidator;
use Symfony\Component\HttpFoundation\Request;
use Drupal\views_bulk_operations\Action\ViewsBulkOperationsActionBase;
use Drupal\Core\Plugin\PluginFormInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\State\State;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Drupal\Core\Routing\UrlGeneratorTrait;

/**
 * Redirects to an entity deletion form.
 *
 * @Action(
 *   id = "send_custom_email",
 *   label = @Translation("Send custom email"),
 *   type = "node"
 * )
 */
class SendCustomEmail extends ViewsBulkOperationsActionBase implements ContainerFactoryPluginInterface, PluginFormInterface {

  use UrlGeneratorTrait;
  /**
   * The tempstore object.
   *
   * @var \Drupal\Core\TempStore\SharedTempStore
   */
  protected $tempStore;

  /**
   * The current user.
   *
   * @var \Drupal\Core\Session\AccountInterface
   */
  protected $currentUser;

  /**
   * Previous Route name
   *
   * @var string
   */
  protected $previousRoute = 'entity.node.collection';

  /**
   * Constructs a new DeleteAction object.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin ID for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   * @param \Drupal\Core\TempStore\PrivateTempStoreFactory $temp_store_factory
   *   The tempstore factory.
   * @param \Drupal\Core\Session\AccountInterface $current_user
   *   Current user.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, EntityTypeManagerInterface $entity_type_manager, PrivateTempStoreFactory $temp_store_factory, AccountInterface $current_user, State $State) {
    $this->currentUser = $current_user;
    $this->tempStore = $temp_store_factory->get('send_custom_email__form_path');
    $this->state = $State;
    parent::__construct($configuration, $plugin_id, $plugin_definition, $entity_type_manager);
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('entity_type.manager'),
      $container->get('tempstore.private'),
      $container->get('current_user'),
      $container->get('state')
    );
  }

  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $config = $this->state->get('neuronet_misc.custom_emails');
    if (empty($config['emails_container'])) {
      $this->tempStore->set(\Drupal::currentUser()->id(), $this->context['redirect_url']->getRouteName());
      $response = new RedirectResponse(\Drupal::url('neuronet_misc.custom_emails'));
      $response->send();
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
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function executeMultiple(array $entities) {
    $titles = '';
    foreach ($entities as $entity) {
      $titles .= $entity->getTitle() . ', ';
    }
    $titles = rtrim($titles, ', ');
    $this->messenger()->addStatus($this->t('Sent email to: @title', ['@title' => $titles]));
  }

  /**
   * {@inheritdoc}
   */
  public function execute($object = NULL) {
    $this->executeMultiple([$object]);
  }

  /**
   * {@inheritdoc}
   */
  public function access($object, AccountInterface $account = NULL, $return_as_object = FALSE) {
    return $object->access('edit', $account, $return_as_object);
  }

}