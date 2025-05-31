<?php

namespace Drupal\api_authentication\Authentication;

use Drupal\Core\Authentication\AuthenticationProviderInterface;
use Symfony\Component\HttpFoundation\Request;
use Drupal\api_authentication\Service\AuthenticationManager;
use Drupal\Core\Session\AccountInterface;
use Drupal\user\Entity\User;

/**
 * Provides a custom Basic Authentication provider for API REST requests.
 */
class ApiBasicAuthAuthenticationProvider implements AuthenticationProviderInterface {

  /**
   * The custom authentication manager service.
   *
   * @var \Drupal\api_authentication\Service\AuthenticationManager
   */
  protected AuthenticationManager $authManager;

  /**
   * Constructs a new ApiBasicAuthAuthenticationProvider.
   *
   * @param \Drupal\api_authentication\Service\AuthenticationManager $authManager
   *   The authentication manager.
   */
  public function __construct(AuthenticationManager $authManager) {
    $this->authManager = $authManager;
  }

  /**
   * {@inheritdoc}
   *
   * Returns TRUE if the request has an Authorization header that starts with "Basic ".
   */
  public function applies(Request $request): bool {
    $authHeader = $request->headers->get('Authorization');
    return ($authHeader !== NULL && strpos($authHeader, 'Basic ') === 0);
  }

  /**
   * {@inheritdoc}
   *
   * Attempts to authenticate the request using the provided Basic Authentication header.
   */
  public function authenticate(Request $request): ?AccountInterface {
    $authHeader = $request->headers->get('Authorization');
    if (!$authHeader || strpos($authHeader, 'Basic ') !== 0) {
      return NULL;
    }

    // Decode the header.
    $encoded = substr($authHeader, 6);
    $decoded = base64_decode($encoded);
    if (strpos($decoded, ':') === FALSE) {
      return NULL;
    }
    list($supplied_username, $supplied_password) = explode(':', $decoded, 2);

    // Retrieve the expected credentials from configuration.
    $config = \Drupal::config('api_authentication.settings');
    $expected_username = $config->get('basic.username');
    $expected_password = $config->get('basic.password');
    $validate_endpoint = $config->get('basic.validate_endpoint');

    // Ensure the credentials match.
    if ($supplied_username !== $expected_username || $supplied_password !== $expected_password) {
      return NULL;
    }

    // Optionally, validate the credentials using the plugin.
    $plugin = $this->authManager->getAuthenticationPlugin('basic');
    if ($plugin) {
      $settings = [
        'username' => $supplied_username,
        'password' => $supplied_password,
        'validate_endpoint' => $validate_endpoint,
      ];
      if (!$plugin->authenticate($settings)) {
        return NULL;
      }
    }

    // Map these credentials to a Drupal user account.
    $users = \Drupal::entityTypeManager()
      ->getStorage('user')
      ->loadByProperties(['name' => $supplied_username]);
    $account = reset($users);

    return $account ?: NULL;
  }

}
