<?php

namespace Drupal\embed_block;

use Embed\Embed;

/**
 * A service class for handling Embed Block.
 */
class EmbedBlock implements EmbedBlockInterface {

  /**
   * Saved config setting.
   *
   * @var array
   */
  public $config;

  /**
   * {@inheritdoc}
   */
  public function __construct(array $config = []) {
    $this->config = $config;
  }

  /**
   * {@inheritdoc}
   */
  public function getConfig() {
    return $this->config;
  }

  /**
   * {@inheritdoc}
   */
  public function setConfig(array $config) {
    $this->config = $config;
  }

  /**
   * {@inheritdoc}
   */
  public function getEmbed($request, array $config = []) {
    return Embed::create($request, $config ?: $this->config);
  }

}
