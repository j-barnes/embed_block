# Embed Block Module

[Embed Block](https://www.drupal.org/project/embed_block) module
allows any block to be embedded using a text editor.


## Installation

Embed Block can be installed via the
[standard Drupal installation process](http://drupal.org/node/895232).

## Embedding Blocks

In order to embed a block in text, you should add the
following placeholder:

```<drupal-embed-block data-block-id="PLUGIN_ID"></drupal-embed-block>```

The 'PLUGIN_ID' should be replaced by the block plugin identifier.
The block access is checked against the current user and if the access
is denied, the filter replaces the placeholder with an empty string ("").
If the specified plugin ID doesn't exist, the placeholder is kept unchanged.

### Example
```html
<drupal-embed-block data-block-id="block_content:cbd45566-bcac-44bf-812c-8f3af63a9b8c">
  Test Block
</drupal-embed-block>
```

## Embedding Blocks with WYSIWYG

* Install and enable [Embed Block](https://www.drupal.org/project/embed_block) module.
* Go to the 'Text formats and editors' configuration page:
  `/admin/config/content/formats`,
  and for each text format/editor combo where you want to embed blocks,
  do the following:
  * Enable the "Embed Block" filter for the desired text formats
    on the Text Formats configuration page.
  * Drag and drop the 'Embed Block' button into the Active toolbar.
  * If the text format uses the 'Limit allowed HTML tags and correct
    faulty HTML' filter, ensure the necessary tags and attributes were
    automatically whitelisted:
    ```<drupal-embed-block class data-block-id>```
    appears in the 'Allowed HTML tags' setting.

## Usage

* For example, create a new *Article* content.
* Click on the 'Embed Block' button in the text editor.
* Select the block you wish to embed from the dropdown.
