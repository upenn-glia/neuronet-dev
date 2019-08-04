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

/**
 * Redirects to an entity deletion form.
 *
 * @Action(
 *   id = "send_custom_email",
 *   label = @Translation("Send custom email"),
 *   type = "node"
 * )
 */
class SendCustomEmail extends ActionBase implements ContainerFactoryPluginInterface {

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
  public function __construct(array $configuration, $plugin_id, $plugin_definition, EntityTypeManagerInterface $entity_type_manager, PrivateTempStoreFactory $temp_store_factory, AccountInterface $current_user) {
    $this->currentUser = $current_user;
    $this->tempStore = $temp_store_factory->get('send_custom_email');
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
      $container->get('current_user')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function setContext(array &$context) {
    if (!is_null($context['redirect_url']) && $context['redirect_url']->getRouteName()) {
      $this->previousRoute = $context['redirect_url']->getRouteName();
    }
    $this->context['sandbox'] = &$context['sandbox'];
    $this->context['results'] = &$context['results'];
    foreach ($context as $key => $item) {
      if ($key === 'sandbox' || $key === 'results') {
        continue;
      }
      $this->context[$key] = $item;
    }
  }

  /**
   * {@inheritdoc}
   */
  public function executeMultiple(array $entities) {

    $nids = [];
    foreach ($entities as $entity) {
      $nids[] = $entity->id();
    }
    $temp_vars = [
      'nids' => $nids,
      'previous_route' => $this->previousRoute,
    ];
    $this->tempStore->set($this->currentUser->id(), $temp_vars);
    $this->context['results']['redirect_url'] = Url::fromRoute('neuronet_misc.select_custom_email');
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