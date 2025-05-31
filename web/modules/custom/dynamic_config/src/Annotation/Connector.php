<?php

namespace Drupal\dynamic_config\Annotation;

use Drupal\Component\Annotation\Plugin;

/**
 * Defines a Connector annotation object.
 *
 * @Annotation
 */
class Connector extends Plugin {
  /**
   * The ID of the connector.
   *
   * @var string
   */
  public $id;

  /**
   * The human-readable name of the connector.
   *
   * @var \Drupal\Core\Annotation\Translation
   */
  public $label;
}
