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
 * Provides an advanced connector.
 *
 * @Connector(
 *   id = "advanced_connector",
 *   label = @Translation("Advanced Connector")
 * )
 */
class AdvancedConnector extends PluginBase implements ConnectorInterface, ContainerFactoryPluginInterface {

  use StringTranslationTrait;

  /**
   * Stores the connector configuration.
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

  public function __construct(array $configuration, $plugin_id, array $plugin_definition, ConfigFactoryInterface $config_factory) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->configuration = $configuration;
    $this->configFactory = $config_factory;
  }

  /**
   * Creates an instance of the plugin.
   *
   * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
   *   The service container.
   * @param array $configuration
   *   The plugin configuration.
   * @param string $plugin_id
   *   The plugin ID.
   * @param array $plugin_definition
   *   The plugin definition.
   *
   * @return static
   *   A new instance of the plugin.
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

    // Get default values or fallback to empty values.
    $default_text = isset($config['advanced_text']) ? $config['advanced_text'] : '';
    $default_checkbox = isset($config['advanced_checkbox']) ? $config['advanced_checkbox'] : 0;

    $form['advanced_text'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Advanced text'),
      '#default_value' => $default_text,
      '#description' => $this->t('Enter some advanced text setting for the advanced connector.'),
    ];
    $form['advanced_checkbox'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Enable advanced option'),
      '#default_value' => $default_checkbox,
      '#description' => $this->t('Check this to enable advanced features.'),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateConfigurationForm(array &$values, FormStateInterface $form_state) {
    if (empty($values['advanced_text'])) {
      $form_state->setErrorByName('advanced_text', $this->t('Advanced text cannot be empty.'));
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$values, FormStateInterface $form_state) {
    if (isset($values['advanced_text']) || isset($values['advanced_checkbox'])) {
      $this->configFactory->getEditable('dynamic_config.' . $this->getPluginId())
        ->set('settings', [
          'advanced_text' => $values['advanced_text'],
          'advanced_checkbox' => $values['advanced_checkbox'],
        ])
        ->save();
    }
  }
}
