<?php

namespace Drupal\embed_block\Plugin\CKEditorPlugin;

use Drupal\ckeditor\CKEditorPluginCssInterface;
use Drupal\ckeditor\CKEditorPluginBase;
use Drupal\Core\Extension\ModuleExtensionList;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\editor\Entity\Editor;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Defines the "Embed Block" plugin.
 *
 * @CKEditorPlugin(
 *   id = "embed_block",
 *   label = @Translation("Embed Block Plugin")
 * )
 */
class EmbedBlockPlugin extends CKEditorPluginBase implements ContainerFactoryPluginInterface, CKEditorPluginCssInterface {

  /**
   * The module extension list.
   *
   * @var \Drupal\Core\Extension\ModuleExtensionList
   */
  protected $moduleExtensionList;

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, ModuleExtensionList $module_extension_list) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->moduleExtensionList = $module_extension_list;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition): self {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('extension.list.module'),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getButtons(): array {
    return [
      'EmbedBlock' => [
        'label' => $this->t('Embed Block'),
        'image' => $this->moduleExtensionList->getPath('embed_block') . '/plugins/embed_block/icons/icon.png',
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getLibraries(Editor $editor): array {
    return [
      'core/drupal.ajax',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getFile(): string {
    return $this->moduleExtensionList->getPath('embed_block') . '/plugins/embed_block/plugin.js';
  }

  /**
   * {@inheritdoc}
   */
  public function getConfig(Editor $editor): array {
    return [];
  }

  /**
   * {@inheritdoc}
   */
  public function getCssFiles(Editor $editor): array {
    return [
      $this->moduleExtensionList->getPath('embed_block') . '/css/style.css',
    ];
  }

}
