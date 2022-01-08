<?php

namespace Drupal\Tests\embed_block\Functional;

/**
 * Tests the embed_block filter.
 *
 * @group embed_block
 */
class EmbedBlockFilterTest extends EmbedBlockTestBase {

  /**
   * Tests the embed_block filter.
   *
   * Ensures that blocks are getting rendered when valid plugin IDs
   * are passed. Also tests situations when embed fails.
   */
  public function testFilter() {
    $block = $this->createBlockContent('Test Block');

    // Tests embed using custom test block.
    $content = '<drupal-embed-block data-block-id="block_content:' . $block->get('uuid')->value . '">Test Block</drupal-embed-block>';
    $settings = [];
    $settings['type'] = 'page';
    $settings['title'] = 'Test embed_block with a custom block';
    $settings['body'] = [['value' => $content, 'format' => 'custom_format']];
    $node = $this->drupalCreateNode($settings);
    $this->drupalGet('node/' . $node->id());
    $this->assertSession()->responseContains($block->get('body')->value);

    // Ensure that placeholder is not replaced when embed is unsuccessful.
    $content = '<drupal-embed-block data-block-id="">Test Block</drupal-embed-block>';
    $settings = [];
    $settings['type'] = 'page';
    $settings['title'] = 'Test embed_block with a custom block';
    $settings['body'] = [['value' => $content, 'format' => 'custom_format']];
    $node = $this->drupalCreateNode($settings);
    $this->drupalGet('node/' . $node->id());
    $this->assertSession()->responseNotContains(strip_tags($content));

    // Test that tag of container element is not replaced when it's not
    // <drupal-embed-block>.
    $content = '<not-drupal-embed-block data-block-id="block_content:' . $block->get('uuid')->value . '">Test Block</not-drupal-embed-block>';
    $settings = [];
    $settings['type'] = 'page';
    $settings['title'] = 'Test embed_block with a custom block';
    $settings['body'] = [['value' => $content, 'format' => 'custom_format']];
    $node = $this->drupalCreateNode($settings);
    $this->drupalGet('node/' . $node->id());
    $this->assertSession()->responseContains('</not-drupal-embed-block>');

    // Check random element like div.
    $content = '<div drupal-embed-block data-block-id="block_content:' . $block->get('uuid')->value . '">Test Block</div>';
    $settings = [];
    $settings['type'] = 'page';
    $settings['title'] = 'Test embed_block with a custom block';
    $settings['body'] = [['value' => $content, 'format' => 'custom_format']];
    $node = $this->drupalCreateNode($settings);
    $this->drupalGet('node/' . $node->id());
    $this->assertSession()->responseContains('<div drupal-embed-block');
  }

}
