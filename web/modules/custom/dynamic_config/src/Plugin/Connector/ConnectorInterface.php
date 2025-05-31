<?php

namespace Drupal\dynamic_config\Plugin\Connector;

use Drupal\Component\Plugin\PluginInspectionInterface;
use Drupal\Core\Form\FormStateInterface;

interface ConnectorInterface extends PluginInspectionInterface {

  /**
   * Builds the connector-specific configuration form.
   *
   * @param array $form
   *   The base form array.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current form state.
   *
   * @return array
   *   The connector configuration form elements.
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state);

  /**
   * Validates the connector configuration form.
   *
   * @param array $form
   *   The form structure.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current form state.
   */
  public function validateConfigurationForm(array &$form, FormStateInterface $form_state);

  /**
   * Handles submission of the connector configuration form.
   *
   * @param array $form
   *   The form structure.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current form state.
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state);
}
