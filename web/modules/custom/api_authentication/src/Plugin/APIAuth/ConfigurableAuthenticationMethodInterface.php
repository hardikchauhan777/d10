<?php

namespace Drupal\api_authentication\Plugin\APIAuth;

use Drupal\Core\Form\FormStateInterface;

/**
 * An interface for API Authentication methods with their own configuration.
 */
interface ConfigurableAuthenticationMethodInterface extends AuthenticationMethodInterface {

  /**
   * Returns the default configuration for the plugin.
   *
   * @return array
   *   The default configuration.
   */
  public static function defaultConfiguration(): array;

  /**
   * Builds a configuration form specific for the authentication method.
   *
   * @param array $form
   *   Existing form elements.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The form state.
   * @param array $config
   *   The currently saved configuration for this plugin.
   *
   * @return array
   *   A renderable array with the configuration form elements.
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state, array $config): array;

  /**
   * Validates the plugin configuration form.
   *
   * @param array $form
   *   The plugin’s subform for configuration.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The form state.
   * @param array $config
   *   The plugin configuration values.
   */
  public function validateConfigurationForm(array $form, FormStateInterface $form_state, array $config): void;

  /**
   * Processes the submission of the plugin configuration form.
   *
   * @param array $form
   *   The plugin’s configuration form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The form state.
   * @param array &$config
   *   The configuration array to be updated.
   */
  public function submitConfigurationForm(array $form, FormStateInterface $form_state, array &$config): void;
}
