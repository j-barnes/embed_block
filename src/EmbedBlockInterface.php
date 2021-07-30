<?php

namespace Drupal\embed_block;

/**
 * A service class for handling Embed Block.
 *
 * @todo Add more documentation.
 */
interface EmbedBlockInterface {

  /**
   * Constructor for embed block interface.
   */
  public function __construct(array $config = []);

  /**
   * Allows for retrieving of config.
   */
  public function getConfig();

  /**
   * Allows for setting of config.
   */
  public function setConfig(array $config);

  /**
   * Allows for getting of embed.
   */
  public function getEmbed($request, array $config = []);

}
