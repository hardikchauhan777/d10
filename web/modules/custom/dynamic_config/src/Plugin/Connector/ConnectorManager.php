<?php

namespace Drupal\dynamic_config\Plugin\Connector;

use Drupal\Core\Plugin\DefaultPluginManager;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;

class ConnectorManager extends DefaultPluginManager {

  /**
   * Constructs a new ConnectorManager.
   *
   * @param \Traversable $namespaces
   *   An object that implements Traversable containing the root paths keyed by
   *   the corresponding namespace to look for plugin implementations.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler.
   * @param \Drupal\Core\Cache\CacheBackendInterface $cache_backend
   *   The cache backend.
   */
  public function __construct(\Traversable $namespaces, ModuleHandlerInterface $module_handler, CacheBackendInterface $cache_backend) {
    parent::__construct(
      'Plugin/Connector', // The subdirectory to look for plugins.
      $namespaces,
      $module_handler,
      'Drupal\dynamic_config\Plugin\Connector\ConnectorInterface', // The plugin interface.
      'Drupal\dynamic_config\Annotation\Connector' // The annotation class.
    );
    $this->alterInfo('connector_info');
    $this->setCacheBackend($cache_backend, 'connector_plugin');
  }
}
