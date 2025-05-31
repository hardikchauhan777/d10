<?php

namespace Drupal\api_authentication\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\api_authentication\Service\AuthenticationManager;

/**
 * A simple controller to test API authentication.
 */
class ApiTestController extends ControllerBase {

  /**
   * The authentication manager.
   *
   * @var \Drupal\api_authentication\Service\AuthenticationManager
   */
  protected $authManager;

  /**
   * Constructs an ApiTestController object.
   *
   * @param \Drupal\api_authentication\Service\AuthenticationManager $auth_manager
   *   The authentication manager.
   */
  public function __construct(AuthenticationManager $auth_manager) {
    $this->authManager = $auth_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('api_authentication.manager')
    );
  }

  /**
   * Test endpoint for API Authentication.
   */
  public function test() {
    // Retrieve selected authentication method from configuration.
    $config = $this->config('api_authentication.settings');
    $selected_method = $config->get('auth_method') ?: 'basic';

    // Get the proper authentication plugin instance.
    $plugin = $this->authManager->getAuthenticationPlugin($selected_method);

    if ($plugin) {
      // Build the settings array based on the selected method.
      $settings = [];
      switch ($selected_method) {
        case 'basic':
          $settings = [
            'username' => $config->get('basic.username'),
            'password' => $config->get('basic.password'),
          ];
          break;

        case 'oauth1':
          $settings = [
            'consumer_key' => $config->get('oauth1.consumer_key'),
            'consumer_secret' => $config->get('oauth1.consumer_secret'),
            'token' => $config->get('oauth1.token'),
            'token_secret' => $config->get('oauth1.token_secret'),
          ];
          break;

        case 'oauth2':
          $settings = [
            'client_id' => $config->get('oauth2.client_id'),
            'client_secret' => $config->get('oauth2.client_secret'),
            'token_endpoint' => $config->get('oauth2.token_endpoint'),
          ];
          break;
      }
      
      $auth_result = $plugin->authenticate($settings);
      $message = $auth_result ? 'Authentication succeeded.' : 'Authentication failed.';
    }
    else {
      $message = 'No valid authentication method configured.';
    }

    return [
      '#markup' => $this->t('Test result: @message', ['@message' => $message]),
    ];
  }
}
