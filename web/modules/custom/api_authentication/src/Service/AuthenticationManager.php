<?php

namespace Drupal\api_authentication\Service;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\api_authentication\Plugin\APIAuth\ConfigurableAuthenticationMethodInterface;
use Drupal\api_authentication\Plugin\APIAuth\BasicAuthentication;
use Drupal\api_authentication\Plugin\APIAuth\OAuth1Authentication;
use Drupal\api_authentication\Plugin\APIAuth\OAuth2Authentication;

/**
 * Manager to handle API Authentication plugins.
 */
class AuthenticationManager {

  /**
   * The configuration factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * Constructs a new AuthenticationManager.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The configuration factory.
   */
  public function __construct(ConfigFactoryInterface $config_factory) {
    $this->configFactory = $config_factory;
  }

  /**
   * Returns the authentication plugin instance based on the method.
   *
   * @param string $method
   *   The authentication method identifier.
   *
   * @return \Drupal\api_authentication\Plugin\APIAuth\ConfigurableAuthenticationMethodInterface|null
   *   The plugin instance or NULL if the method is unrecognized.
   */
  public function getAuthenticationPlugin(string $method): ?ConfigurableAuthenticationMethodInterface {
    switch ($method) {
      case 'basic':
        return new BasicAuthentication();
      case 'oauth1':
        return new OAuth1Authentication();
      case 'oauth2':
        return new OAuth2Authentication();
      default:
        return NULL;
    }
  }

  /**
   * Returns the default configuration for a given authentication method.
   *
   * @param string $method
   *   The authentication method identifier.
   *
   * @return array
   *   The default configuration.
   */
  public function getDefaultConfiguration(string $method): array {
    $plugin = $this->getAuthenticationPlugin($method);
    return $plugin ? $plugin::defaultConfiguration() : [];
  }
}
