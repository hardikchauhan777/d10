<?php

namespace Drupal\dynamic_config\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\dynamic_config\Plugin\Connector\ConnectorManager;
use Symfony\Component\DependencyInjection\ContainerInterface;

class ConnectorConfigForm extends ConfigFormBase {

  /**
   * The connector plugin manager.
   *
   * @var \Drupal\dynamic_config\Plugin\Connector\ConnectorManager
   */
  protected $connectorManager;

  /**
   * Constructs a ConnectorConfigForm.
   *
   * @param \Drupal\dynamic_config\Plugin\Connector\ConnectorManager $connector_manager
   *   The connector plugin manager.
   */
  public function __construct(ConnectorManager $connector_manager) {
    $this->connectorManager = $connector_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('dynamic_config.connector_manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'connector_config_form';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['dynamic_config.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    // Get available connectors.
    $plugin_definitions = $this->connectorManager->getDefinitions();
    $options = [];
    foreach ($plugin_definitions as $id => $definition) {
      $options[$id] = $definition['label'];
    }

    $config = $this->config('dynamic_config.settings');
    $default_plugin = $config->get('default_plugin') ?: key($options);

    $form['default_plugin'] = [
      '#type' => 'select',
      '#title' => $this->t('Select Connector'),
      '#options' => $options,
      '#default_value' => $default_plugin,
      '#ajax' => [
        'callback' => '::updateConnectorForm',
        'wrapper' => 'connector-settings-wrapper',
      ],
    ];

    $selected_plugin = $form_state->getValue('default_plugin', $default_plugin);

    // Wrap plugin form fields.
    $form['connector_settings'] = [
      '#type' => 'container',
      '#attributes' => ['id' => 'connector-settings-wrapper'],
      '#tree' => TRUE,
    ];

    // Load plugin-specific configuration into the plugin instance.
    if ($this->connectorManager->hasDefinition($selected_plugin)) {
      $plugin_instance = $this->connectorManager->createInstance($selected_plugin);

      // Load existing plugin configuration.
      $plugin_config = $this->config('dynamic_config.' . $selected_plugin)->get('settings') ?: [];
      $plugin_instance->setConfiguration($plugin_config);

      // Namespace the plugin's config form under its plugin id.
      $form['connector_settings'][$selected_plugin] = $plugin_instance->buildConfigurationForm([], $form_state);
    }

    return parent::buildForm($form, $form_state);
  }

  /**
   * AJAX callback to update the connector subform.
   */
  public function updateConnectorForm(array $form, FormStateInterface $form_state) {
    $form_state->setRebuild(TRUE);
    return $form['connector_settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    $selected_plugin = $form_state->getValue('default_plugin');
    if ($this->connectorManager->hasDefinition($selected_plugin)) {
      $plugin_instance = $this->connectorManager->createInstance($selected_plugin);
      // Optionally, pass the plugin-specific values.
      $plugin_values = $form_state->getValue('connector_settings')[$selected_plugin] ?? [];
      $plugin_instance->validateConfigurationForm($plugin_values, $form_state);
    }
    parent::validateForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $selected_plugin = $form_state->getValue('default_plugin');

    // Save the selected plugin in the main configuration.
    $this->config('dynamic_config.settings')
      ->set('default_plugin', $selected_plugin)
      ->save();

    // Process and save the configuration for the selected plugin.
    if ($this->connectorManager->hasDefinition($selected_plugin)) {
      $plugin_instance = $this->connectorManager->createInstance($selected_plugin);
      // Retrieve only the values for the selected plugin.
      $plugin_values = $form_state->getValue('connector_settings')[$selected_plugin] ?? [];
      $plugin_instance->submitConfigurationForm($plugin_values, $form_state);
    }

    // Remove the configuration of all connectors except the selected one.
    $all_definitions = $this->connectorManager->getDefinitions();
    foreach ($all_definitions as $plugin_id => $definition) {
      if ($plugin_id !== $selected_plugin) {
        // Delete the config object for the non-selected plugin.
        $this->configFactory->getEditable('dynamic_config.' . $plugin_id)->delete();
      }
    }

    $this->addItemsToQueue(['plugin' => $selected_plugin, 'settings' => $plugin_values]);

    parent::submitForm($form, $form_state);
  }

  public function addItemsToQueue(array $items) {
    // This method can be used to add items to a queue for processing.
    // For example, you might want to queue some data for later processing.
    $queue = \Drupal::queue('dynamic_config_demoqueueworker');
    foreach ($items as $item) {
      $queue->createItem($item);
    }
  }

}
