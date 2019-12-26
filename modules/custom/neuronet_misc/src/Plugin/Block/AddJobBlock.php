<?php

namespace Drupal\neuronet_misc\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Path\AliasManagerInterface;
use Drupal\Core\Path\CurrentPathStack;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a 'Add Job' Block.
 *
 * @Block(
 *   id = "add_job_block",
 *   admin_label = @Translation("Add job block"),
 *   category = @Translation("Job Block"),
 * )
 */
class AddJobBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * Current Path service
   *
   * @var CurrentPathStack
   */
  protected $currentPath;

  /**
   * Alias manager service
   *
   * @var AliasManagerInterface
   */
  protected $aliasManager;

  /**
   * Route match service
   *
   * @var RouteMatchInterface
   */
  protected $routeMatch;

  /**
   * Constructs AddJobBlock
   *
   * @param array $configuration
   * @param string $plugin_id
   * @param string $plugin_definition
   * @param CurrentPathStack $current_path
   * @param AliasManagerInterface $alias_manager
   * @param RouteMatchInterface $route_match
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, CurrentPathStack $current_path, AliasManagerInterface $alias_manager, RouteMatchInterface $route_match) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->currentPath = $current_path;
    $this->aliasManager = $alias_manager;
    $this->routeMatch = $route_match;
  }

  /**
   * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
   * @param array $configuration
   * @param string $plugin_id
   * @param mixed $plugin_definition
   *
   * @return static
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('path.current'),
      $container->get('path.alias_manager'),
      $container->get('current_route_match')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $current_path = $this->currentPath->getPath();
    $path_alias = $this->aliasManager->getAliasByPath($current_path);
    $url = Url::fromRoute('<front>')->toString() . 'node/add/job?destination=' . $path_alias;
    $node = $this->routeMatch->getParameter('node');
    if ($node) {
      $url .= '&profile=' . $node->id();
    }
    $link = '<a class="btn-primary btn" href="' . $url . '">Add Job to History</a>';
    return [
      '#markup' => $link,
    ];
  }

}