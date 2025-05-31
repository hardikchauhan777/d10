<?php
 
namespace Drupal\rr_example\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\Core\Block\Attribute\Block;

#[Block(
  id: "example_block",
  admin_label: new TranslatableMarkup("Example block"),
)]
class ExampleBlock extends BlockBase implements ContainerFactoryPluginInterface {

  protected EntityTypeManagerInterface $entityTypeManager;

  public function __construct(array $configuration, $plugin_id, $plugin_definition, EntityTypeManagerInterface $entity_type_manager) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->entityTypeManager = $entity_type_manager;
  }

  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition): static {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('entity_type.manager')
    );
  }

  public function build(): array {
    $articles = $this->entityTypeManager->getStorage('node')->loadByProperties([
      'type' => 'article',
      'status' => 1,
    ]);

    $items = [];
    foreach ($articles as $article) {
      $field_show_in_list = $article->get('field_show_in_list')->value ?? null;
      if ($field_show_in_list) {
        $items[] = [
          '#type' => 'link',
          '#title' => $article->label(),
          '#url' => $article->toUrl(),
        ];
      }
    }

    return [
      '#theme' => 'item_list',
      '#items' => $items,
      '#title' => $this->t('Articles'),
    ];
  }
}