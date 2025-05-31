<?php

namespace Drupal\dynamic_config\Plugin\Connector;

use Drupal\Component\Plugin\PluginBase;
use Drupal\dynamic_config\Annotation\Connector;
use Drupal\dynamic_config\Plugin\Connector\ConnectorInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Config\ConfigFactoryInterface;

/**
 * Provides the default connector.
 *
 * @Connector(
 *   id = "default_connector",
 *   label = @Translation("Default Connector")
 * )
 */
class DefaultConnector extends PluginBase implements ConnectorInterface, ContainerFactoryPluginInterface {

  use StringTranslationTrait;

  /**
   * The current configuration values for this plugin.
   *
   * @var array
   */
  protected $configuration = [];

  /**
   * The configuration factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * Constructs a new DefaultConnector instance.
   *
   * @param array $configuration
   *   A configuration array containing plugin configuration.
   * @param string $plugin_id
   *   The plugin_id for the connector.
   * @param array $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The configuration factory service.
   */
  public function __construct(array $configuration, $plugin_id, array $plugin_definition, ConfigFactoryInterface $config_factory) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->configFactory = $config_factory;
    // Merge in any provided configuration.
    $this->configuration = $configuration;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('config.factory')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getConfiguration() {
    return $this->configuration;
  }

  /**
   * {@inheritdoc}
   */
  public function setConfiguration(array $configuration) {
    $this->configuration = $configuration;
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $config = $this->getConfiguration();
    $default_value = isset($config['setting_default']) ? $config['setting_default'] : '';

    // Wrap the field under 'config' so that it is namespaced.
    $form['config'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Default setting'),
      '#default_value' => $default_value,
      '#description' => $this->t('A default setting for the default connector.'),
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateConfigurationForm(array &$values, FormStateInterface $form_state) {
    // Add connector-specific validation if needed.
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$values, FormStateInterface $form_state) {
    if (isset($values['config'])) {
      $this->configFactory->getEditable('dynamic_config.' . $this->getPluginId())
        ->set('settings', ['setting_default' => $values['config']])
        ->save();
    }
  }
}
