<?php

namespace Drupal\Tests\embed_block\Functional;

use Drupal\block_content\Entity\BlockContent;
use Drupal\block_content\Entity\BlockContentType;
use Drupal\editor\Entity\Editor;
use Drupal\filter\Entity\FilterFormat;
use Drupal\Tests\BrowserTestBase;

/**
 * Base class for all embed_block tests.
 */
abstract class EmbedBlockTestBase extends BrowserTestBase {

  /**
   * Modules to enable.
   *
   * @var array
   */
  protected static $modules = [
    'embed_block',
    'node',
    'ckeditor',
    'block',
    'block_content',
  ];

  /**
   * {@inheritdoc}
   */
  public $defaultTheme = 'stark';

  /**
   * A test user with administrative privileges.
   *
   * @var \Drupal\user\UserInterface
   */

  protected $adminUser;

  /**
   * A test user.
   *
   * @var \Drupal\user\UserInterface
   */
  protected $webUser;

  /**
   * A set up for all tests.
   */
  protected function setUp() {
    parent::setUp();

    // Create a page content type.
    $this->drupalCreateContentType(['type' => 'page', 'name' => 'Basic page']);

    // Create a text format and enable the embed_block filter.
    $format = FilterFormat::create([
      'format' => 'custom_format',
      'name' => 'Custom format',
      'filters' => [
        'embed_block' => [
          'status' => 1,
        ],
      ],
    ]);
    $format->save();

    $editor_group = [
      'name' => 'Embed Block',
      'items' => [
        'embed_block',
      ],
    ];
    $editor = Editor::create([
      'format' => 'custom_format',
      'editor' => 'ckeditor',
      'settings' => [
        'toolbar' => [
          'rows' => [[$editor_group]],
        ],
      ],
    ]);
    $editor->save();

    // Create a user with required permissions.
    $this->webUser = $this->drupalCreateUser([
      'access content',
      'create page content',
      'use text format custom_format',
    ]);
    $this->drupalLogin($this->webUser);

    // Create basic custom block type.
    $this->createBlockContentType('basic', TRUE);

  }

  /**
   * Creates a custom block.
   *
   * @param bool|string $title
   *   (optional) Title of block. When no value is given uses a random name.
   *   Defaults to FALSE.
   * @param string $bundle
   *   (optional) Bundle name. Defaults to 'basic'.
   * @param bool $save
   *   (optional) Whether to save the block. Defaults to TRUE.
   *
   * @return \Drupal\block_content\Entity\BlockContent
   *   Created custom block.
   */
  protected function createBlockContent($title = FALSE, $bundle = 'basic', $save = TRUE) {
    $title = $title ?: $this->randomMachineName();
    $block_content = BlockContent::create([
      'info' => $title,
      'type' => $bundle,
      'langcode' => 'en',
      'body' => ['value' => 'This is a test block.'],
      'id' => 'block_test',
    ]);
    if ($block_content && $save === TRUE) {
      $block_content->save();
    }
    return $block_content;
  }

  /**
   * Creates a custom block type (bundle).
   *
   * @param array|string $values
   *   The value to create the block content type. If $values is an array
   *   it should be like: ['id' => 'foo', 'label' => 'Foo']. If $values
   *   is a string, it will be considered that it represents the label.
   * @param bool $create_body
   *   Whether or not to create the body field.
   *
   * @return \Drupal\block_content\Entity\BlockContentType
   *   Created custom block type.
   */
  protected function createBlockContentType($values, $create_body = FALSE) {
    if (is_array($values)) {
      if (!isset($values['id'])) {
        do {
          $id = strtolower($this->randomMachineName(8));
        } while (BlockContentType::load($id));
      }
      else {
        $id = $values['id'];
      }
      $values += [
        'id' => $id,
        'label' => $id,
        'revision' => FALSE,
      ];
      $bundle = BlockContentType::create($values);
    }
    else {
      $bundle = BlockContentType::create([
        'id' => $values,
        'label' => $values,
        'revision' => FALSE,
      ]);
    }
    $bundle->save();
    if ($create_body) {
      block_content_add_body_field($bundle->id());
    }
    return $bundle;
  }

}
