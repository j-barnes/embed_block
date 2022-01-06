<?php

namespace Drupal\embed_block\Plugin\CKEditorPlugin;

use Drupal\ckeditor\CKEditorPluginCssInterface;
use Drupal\ckeditor\CKEditorPluginBase;
use Drupal\Core\Config\ConfigFactoryInterface;
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
   * The config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, ConfigFactoryInterface $config_factory) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->configFactory = $config_factory;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('config.factory')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getButtons() {
    return [
      'EmbedBlock' => [
        'label' => $this->t('Embed Block'),
        'image' => \Drupal::service('extension.list.module')->getPath('embed_block') . '/plugins/embed_block/icons/icon.png',
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getLibraries(Editor $editor) {
    return [
      'core/drupal.ajax',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getFile() {
    return \Drupal::service('extension.list.module')->getPath('embed_block') . '/plugins/embed_block/plugin.js';
  }

  /**
   * {@inheritdoc}
   */
  public function getConfig(Editor $editor) {
    return [];
  }

  /**
   * {@inheritdoc}
   */
  public function getCssFiles(Editor $editor) {
    return [
      \Drupal::service('extension.list.module')->getPath('embed_block') . '/css/style.css',
    ];
  }

}
