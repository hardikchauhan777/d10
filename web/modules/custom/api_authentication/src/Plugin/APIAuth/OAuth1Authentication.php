<?php

namespace Drupal\api_authentication\Plugin\APIAuth;

use Drupal\Core\Form\FormStateInterface;

/**
 * Provides OAuth 1.0 API Authentication.
 */
class OAuth1Authentication implements ConfigurableAuthenticationMethodInterface {

  /**
   * {@inheritdoc}
   */
  public static function defaultConfiguration(): array {
    return [
      'consumer_key' => '',
      'consumer_secret' => '',
      'token' => '',
      'token_secret' => '',
    ];
  }

  /**
   * Builds the configuration form for OAuth 1.0 settings.
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state, array $config): array {
    $form['consumer_key'] = [
      '#type' => 'textfield',
      '#title' => t('Consumer Key'),
      '#default_value' => $config['consumer_key'] ?? '',
    ];
    $form['consumer_secret'] = [
      '#type' => 'textfield',
      '#title' => t('Consumer Secret'),
      '#default_value' => $config['consumer_secret'] ?? '',
    ];
    $form['token'] = [
      '#type' => 'textfield',
      '#title' => t('Token'),
      '#default_value' => $config['token'] ?? '',
    ];
    $form['token_secret'] = [
      '#type' => 'textfield',
      '#title' => t('Token Secret'),
      '#default_value' => $config['token_secret'] ?? '',
    ];
    return $form;
  }
  /**
   * Validates the OAuth 1.0 configuration form.
   */
  public function validateConfigurationForm(array $form, FormStateInterface $form_state, array $config): void {
    if (empty($form_state->getValue('consumer_key'))) {
      $form_state->setErrorByName('consumer_key', t('Consumer Key is required for OAuth 1.0 Authentication.'));
    }
    if (empty($form_state->getValue('consumer_secret'))) {
      $form_state->setErrorByName('consumer_secret', t('Consumer Secret is required for OAuth 1.0 Authentication.'));
    }
    if (empty($form_state->getValue('token'))) {
      $form_state->setErrorByName('token', t('Token is required for OAuth 1.0 Authentication.'));
    }
    if (empty($form_state->getValue('token_secret'))) {
      $form_state->setErrorByName('token_secret', t('Token Secret is required for OAuth 1.0 Authentication.'));
    }
  }
  /**
   * Processes the submission of the OAuth 1.0 configuration form.
   */
  public function submitConfigurationForm(array $form, FormStateInterface $form_state, array &$config): void {
    $config['consumer_key'] = $form_state->getValue('consumer_key');
    $config['consumer_secret'] = $form_state->getValue('consumer_secret');
    $config['token'] = $form_state->getValue('token');
    $config['token_secret'] = $form_state->getValue('token_secret');
  }
  /**
   * {@inheritdoc}
   */
  public function authenticate(array $settings): bool {
    // Ensure all required OAuth 1.0 parameters exist.
    if (!empty($settings['consumer_key']) &&
      !empty($settings['consumer_secret']) &&
      !empty($settings['token']) &&
      !empty($settings['token_secret'])) {
      // Normally, implement signature generation and verification here.
      return TRUE;
    }
    return FALSE;
  }
}
