<?php

declare(strict_types=1);

namespace Drupal\dynamic_config\Plugin\QueueWorker;

use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Queue\QueueWorkerBase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Defines 'dynamic_config_demoqueueworker' queue worker.
 *
 * @QueueWorker(
 *   id = "dynamic_config_demoqueueworker",
 *   title = @Translation("DemoQueueWorker"),
 *   cron = {"time" = 60},
 * )
 */
final class DemoQueueWorker extends QueueWorkerBase implements ContainerFactoryPluginInterface {

    /**
   * The logger channel.
   *
   * @var \Drupal\Core\Logger\LoggerChannelInterface
   */
  protected $logger;

  /**
   * Constructs a new DemoQueueWorker instance.
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    private readonly LoggerChannelFactoryInterface $loggerFactory,
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->logger = $loggerFactory->get('dynamic_config_demoqueueworker');
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition): self {
    return new self(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('logger.factory'),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function processItem($data) {
    // $data represents the queued data.
    // Add your logic here, e.g., processing an email, updating records, etc.
    $this->logger->notice('Processing queue item. Data: @data', ['@data' => print_r($data, TRUE)]);

    // Optionally, if processing fails, you might throw an exception.
    // Throwing an exception will prevent the item from being removed from the queue,
    // so Drupal can retry it later.
  }

}
