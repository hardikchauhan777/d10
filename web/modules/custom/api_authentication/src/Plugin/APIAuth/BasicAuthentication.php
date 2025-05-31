<?php

namespace Drupal\api_authentication\Plugin\APIAuth;

use Drupal\api_authentication\Plugin\APIAuth\ConfigurableAuthenticationMethodInterface;
use Drupal\Core\Form\FormStateInterface;

/**
 * Provides Basic API Authentication.
 *
 * This implementation generates an HTTP Basic Authentication header.
 * In a production scenario, if a validation endpoint is provided, it
 * performs a GET request to that endpoint with the generated header to
 * verify that the credentials are accepted.
 */
class BasicAuthentication implements ConfigurableAuthenticationMethodInterface {

  /**
   * The generated Basic Authorization header.
   *
   * @var string
   */
  protected $authorizationHeader = '';

  /**
   * {@inheritdoc}
   */
  public static function defaultConfiguration(): array {
    return [
      'username' => '',
      'password' => '',
      // Optional URL endpoint for validating the provided credentials.
      'validate_endpoint' => '',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state, array $config): array {
    $form['username'] = [
      '#type' => 'textfield',
      '#title' => t('Username'),
      '#default_value' => $config['username'] ?? '',
      '#required' => TRUE,
    ];
    // Use a password element to mask sensitive information.
    $form['password'] = [
      '#type' => 'password',
      '#title' => t('Password'),
      '#default_value' => $config['password'] ?? '',
      '#required' => TRUE,
    ];
    $form['validate_endpoint'] = [
      '#type' => 'url',
      '#title' => t('Validation Endpoint'),
      '#description' => t('Optional: A URL endpoint to validate credentials. A GET request will be made using the generated Basic Auth header. Leave empty if not needed.'),
      '#default_value' => $config['validate_endpoint'] ?? '',
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateConfigurationForm(array $form, FormStateInterface $form_state, array $config): void {
    $username = $form_state->getValue('username');
    $password = $form_state->getValue('password');
    $validate_endpoint = $form_state->getValue('validate_endpoint');

    if (empty($username)) {
      $form_state->setErrorByName('username', t('Username is required for Basic Authentication.'));
    }
    if (empty($password)) {
      $form_state->setErrorByName('password', t('Password is required for Basic Authentication.'));
    }
    // If provided, validate that the endpoint is a proper URL.
    if (!empty($validate_endpoint) && !filter_var($validate_endpoint, FILTER_VALIDATE_URL)) {
      $form_state->setErrorByName('validate_endpoint', t('The validation endpoint must be a valid URL.'));
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array $form, FormStateInterface $form_state, array &$config): void {
    $config['username'] = $form_state->getValue('username');
    $config['password'] = $form_state->getValue('password');
    $config['validate_endpoint'] = $form_state->getValue('validate_endpoint');
  }

  /**
   * {@inheritdoc}
   *
   * Generates the Basic Auth header and (optionally) validates the credentials.
   */
  public function authenticate(array $settings): bool {
    // Ensure both username and password are provided.
    if (empty($settings['username']) || empty($settings['password'])) {
      return FALSE;
    }

    // Generate the Basic Authorization header.
    $credentials = base64_encode($settings['username'] . ':' . $settings['password']);
    $this->authorizationHeader = 'Basic ' . $credentials;

    // If a validation endpoint is provided, perform a real HTTP call.
    if (!empty($settings['validate_endpoint'])) {
      try {
        $client = \Drupal::httpClient();
        $response = $client->get($settings['validate_endpoint'], [
          'headers' => [
            'Authorization' => $this->authorizationHeader,
          ],
          'timeout' => 5,
        ]);
        // Only consider the authentication valid if the endpoint returns status 200.
        if ($response->getStatusCode() == 200) {
          return TRUE;
        }
        else {
          \Drupal::logger('api_authentication')->error('Validation endpoint returned status code @code', ['@code' => $response->getStatusCode()]);
          return FALSE;
        }
      }
      catch (\Exception $e) {
        \Drupal::logger('api_authentication')->error('Error during credential validation: @message', ['@message' => $e->getMessage()]);
        return FALSE;
      }
    }

    // If no validation endpoint is provided, assume the credentials are valid.
    return TRUE;
  }

  /**
   * Retrieves the generated Basic Authorization header.
   *
   * @return string
   *   The HTTP Basic Authorization header.
   */
  public function getAuthorizationHeader(): string {
    return $this->authorizationHeader;
  }

}
