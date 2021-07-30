<?php

namespace Drupal\embed_block;

use Drupal\Core\Extension\ModuleHandlerInterface;

/**
 * Wrapper methods for Embed Block.
 *
 * This utility trait should only be used in application-level code, such as
 * classes that would implement ContainerInjectionInterface. Services registered
 * in the Container should not use this trait but inject the appropriate service
 * directly for easier testing.
 */
trait EmbedBlockHelperTrait {

  /**
   * The module handler service.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

  /**
   * The embed block service.
   *
   * @var \Drupal\embed_block\EmbedBlockService
   */
  protected $embedBlockEmbed;

  /**
   * Returns the module handler.
   *
   * @return \Drupal\Core\Extension\ModuleHandlerInterface
   *   The module handler.
   */
  protected function moduleHandler() {
    if (!isset($this->moduleHandler)) {
      $this->moduleHandler = \Drupal::moduleHandler();
    }
    return $this->moduleHandler;
  }

  /**
   * Sets the module handler service.
   *
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler service.
   */
  public function setModuleHandler(ModuleHandlerInterface $module_handler) {
    $this->moduleHandler = $module_handler;
    return $this;
  }

  /**
   * Returns the Embed Block service.
   *
   * @return \Drupal\embed_block\EmbedBlockInterface
   *   The Embed Block service.
   */
  protected function embedBlock() {
    if (!isset($this->embedBlockEmbed)) {
      $this->embedBlock = \Drupal::service('embedBlock');
    }
    return $this->embedBlock;
  }

  /**
   * Sets the Embed Block service.
   */
  public function setEmbedBlock(EmbedBlockInterface $embedBlock) {
    $this->embedBlock = $embedBlock;
    return $this;
  }

}
