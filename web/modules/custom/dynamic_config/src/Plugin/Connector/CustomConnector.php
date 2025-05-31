<?php

namespace Drupal\dynamic_config\Plugin\Connector;

use Drupal\Component\Plugin\PluginBase;
use Drupal\dynamic_config\Annotation\Connector;
use Drupal\dynamic_config\Plugin\Connector\ConnectorInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;

/**
 * Provides a custom connector.
 *
 * @Connector(
 *   id = "custom_connector",
 *   label = @Translation("Custom Connector")
 * )
 */
class CustomConnector extends PluginBase implements ConnectorInterface {

  use StringTranslationTrait;

  /**
   * Stores the connector configuration.
   *
   * @var array
   */
  protected $configuration = [];

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

    // Retrieve the previously saved custom option or default to 'option_1'.
    $default_value = isset($config['custom_option']) ? $config['custom_option'] : 'option_1';

    $form['custom_option'] = [
      '#type' => 'select',
      '#title' => $this->t('Custom option'),
      '#options' => [
        'option_1' => $this->t('Option 1'),
        'option_2' => $this->t('Option 2'),
        'option_3' => $this->t('Option 3'),
      ],
      '#default_value' => $default_value,
      '#description' => $this->t('Choose your custom option.'),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateConfigurationForm(array &$values, FormStateInterface $form_state) {
    if (empty($values['custom_option'])) {
      $form_state->setErrorByName('custom_option', $this->t('Please select a custom option.'));
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$values, FormStateInterface $form_state) {
    if (isset($values['custom_option'])) {
      \Drupal::configFactory()->getEditable('dynamic_config.' . $this->getPluginId())
        ->set('settings', ['custom_option' => $values['custom_option']])
        ->save();
    }
  }
}
