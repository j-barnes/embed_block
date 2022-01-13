<?php

namespace Drupal\Tests\embed_block\Functional;

use Drupal\editor\Entity\Editor;

/**
 * Tests the embed_block dialog controller and route.
 *
 * @group embed_block
 */
class EmbedBlockDialogTest extends EmbedBlockTestBase {

  /**
   * Tests the Embed Block dialog.
   */
  public function testEmbedBlockDialog() {
    // Route is not accessible without specifying all the parameters.
    $this->drupalGet('/embed-block/dialog');
    $this->assertSession()->statusCodeEquals(404);

    // Add an empty configuration for the plain_text editor configuration.
    $editor = Editor::create([
      'format' => 'plain_text',
      'editor' => 'ckeditor',
    ]);
    $editor->save();

    // Dialog is not accessible if filter does not have EmbedBlock button.
    $this->drupalGet('/embed-block/dialog/plain_text');
    $this->assertSession()->statusCodeEquals(403);

    // Route is accessible with a valid filter that contains EmbedBlock.
    $this->drupalGet('/embed-block/dialog/custom_format');
    $this->assertSession()->statusCodeEquals(200);
  }

}
