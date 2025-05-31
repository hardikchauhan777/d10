<?php

namespace Drupal\api_authentication\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\api_authentication\Service\AuthenticationManager;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Configure API authentication settings.
 */
class AuthConfigForm extends ConfigFormBase {

  /**
   * The authentication manager service.
   *
   * @var \Drupal\api_authentication\Service\AuthenticationManager
   */
  protected $authManager;

  /**
   * Constructs an AuthConfigForm object.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The configuration factory service.
   * @param \Drupal\api_authentication\Service\AuthenticationManager $auth_manager
   *   The authentication manager service.
   */
  public function __construct(ConfigFactoryInterface $config_factory, AuthenticationManager $auth_manager) {
    parent::__construct($config_factory);
    $this->authManager = $auth_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('config.factory'),
      $container->get('api_authentication.manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId(): string {
    return 'api_authentication_settings_form';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames(): array {
    return ['api_authentication.settings'];
  }

  /**
   * Builds the overall configuration form.
   */
  public function buildForm(array $form, FormStateInterface $form_state): array {
    $config = $this->config('api_authentication.settings');

    // Global authentication method selection.
    $form['auth_method'] = [
      '#type' => 'select',
      '#title' => $this->t('Authentication Method'),
      '#default_value' => $config->get('auth_method') ?: 'basic',
      '#options' => [
        'basic' => $this->t('Basic Authentication'),
        'oauth1' => $this->t('OAuth 1.0'),
        'oauth2' => $this->t('OAuth 2.0'),
      ],
      '#ajax' => [
        'callback' => '::updateAuthPluginForm',
        'wrapper' => 'auth-plugin-settings-wrapper',
      ],
    ];

    // Plugin-specific configuration container.
    $form['plugin_settings'] = [
      '#type' => 'container',
      '#attributes' => ['id' => 'auth-plugin-settings-wrapper'],
    ];

    // Determine the selected method (from submitted values or saved config).
    $selected_method = $form_state->getValue('auth_method', $config->get('auth_method') ?: 'basic');
    $plugin = $this->authManager->getAuthenticationPlugin($selected_method);
    // Retrieve saved plugin configuration or use defaults.
    $plugin_config = $config->get($selected_method) ?? $this->authManager->getDefaultConfiguration($selected_method);

    if ($plugin) {
      // Nest the plugin configuration inside the container.
      $form['plugin_settings']['settings'] = $plugin->buildConfigurationForm([], $form_state, $plugin_config);
    }
    else {
      $form['plugin_settings']['message'] = [
        '#markup' => $this->t('No configuration available for the selected authentication method.'),
      ];
    }

    return parent::buildForm($form, $form_state);
  }

  /**
   * AJAX callback to update the plugin-specific configuration form.
   */
  public function updateAuthPluginForm(array &$form, FormStateInterface $form_state): array {
    $form_state->setRebuild(TRUE);
    return $form['plugin_settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state): void {
    $selected_method = $form_state->getValue('auth_method');
    $plugin = $this->authManager->getAuthenticationPlugin($selected_method);
    if ($plugin && isset($form['plugin_settings']['settings'])) {
      // Delegate plugin-specific validation.
      $plugin->validateConfigurationForm($form['plugin_settings']['settings'], $form_state, []);
    }
    parent::validateForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state): void {
    $config = $this->config('api_authentication.settings');
    $selected_method = $form_state->getValue('auth_method');
    $config->set('auth_method', $selected_method);

    $plugin = $this->authManager->getAuthenticationPlugin($selected_method);
    $plugin_config = [];
    if ($plugin && isset($form['plugin_settings']['settings'])) {
      // Delegate plugin-specific submission.
      $plugin->submitConfigurationForm($form['plugin_settings']['settings'], $form_state, $plugin_config);
    }
    // Save the plugin configuration under the selected method's key.
    $config->set($selected_method, $plugin_config)->save();

    parent::submitForm($form, $form_state);
  }
}
