<?php

namespace Drupal\api_authentication\Plugin\APIAuth;

use Drupal\Core\Form\FormStateInterface;

/**
 * Provides OAuth 2.0 API Authentication.
 */
class OAuth2Authentication implements ConfigurableAuthenticationMethodInterface {
  /**
   * {@inheritdoc}
   */
  public static function defaultConfiguration(): array {
    return [
      'client_id' => '',
      'client_secret' => '',
      'token_endpoint' => '',
    ];
  }

  /**
   * Builds the configuration form for OAuth 2.0 settings.
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state, array $config): array {
    $form['client_id'] = [
      '#type' => 'textfield',
      '#title' => t('Client ID'),
      '#default_value' => $config['client_id'] ?? '',
    ];
    $form['client_secret'] = [
      '#type' => 'textfield',
      '#title' => t('Client Secret'),
      '#default_value' => $config['client_secret'] ?? '',
    ];
    $form['token_endpoint'] = [
      '#type' => 'textfield',
      '#title' => t('Token Endpoint'),
      '#default_value' => $config['token_endpoint'] ?? '',
    ];
    return $form;
  }

  /**
   * Validates the OAuth 2.0 configuration form.
   */
  public function validateConfigurationForm(array $form, FormStateInterface $form_state, array $config): void {
    if (empty($form_state->getValue('client_id'))) {
      $form_state->setErrorByName('client_id', t('Client ID is required for OAuth 2.0 Authentication.'));
    }
    if (empty($form_state->getValue('client_secret'))) {
      $form_state->setErrorByName('client_secret', t('Client Secret is required for OAuth 2.0 Authentication.'));
    }
    if (empty($form_state->getValue('token_endpoint'))) {
      $form_state->setErrorByName('token_endpoint', t('Token Endpoint is required for OAuth 2.0 Authentication.'));
    }
  }

  /**
   * Processes the submission of the OAuth 2.0 configuration form.
   */
  public function submitConfigurationForm(array $form, FormStateInterface $form_state, array &$config): void {
    $config['client_id'] = $form_state->getValue('client_id');
    $config['client_secret'] = $form_state->getValue('client_secret');
    $config['token_endpoint'] = $form_state->getValue('token_endpoint');
  }

  /**
   * {@inheritdoc}
   */
  public function authenticate(array $settings): bool {
    // Ensure all required OAuth 2.0 parameters exist.
    if (!empty($settings['client_id']) &&
      !empty($settings['client_secret']) &&
      !empty($settings['token_endpoint'])) {
      // Typically, you would request a token or validate an access token here.
      return TRUE;
    }
    return FALSE;
  }
}
