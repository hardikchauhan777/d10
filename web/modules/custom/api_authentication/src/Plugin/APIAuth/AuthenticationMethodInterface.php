<?php

namespace Drupal\api_authentication\Plugin\APIAuth;

/**
 * Interface for API Authentication methods.
 */
interface AuthenticationMethodInterface {
  /**
   * Perform authentication.
   *
   * @param array $settings
   *   An array containing authentication configuration.
   *
   * @return bool
   *   TRUE if authentication succeeds, FALSE otherwise.
   */
  public function authenticate(array $settings): bool;
}
