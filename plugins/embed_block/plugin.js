/*
* Embed Block Plugin
*
*/
(function ($, Drupal, drupalSettings, CKEDITOR) {

	'use strict';
	CKEDITOR.plugins.add('embed_block', {
		requires: 'widget',
		icons: 'embed_block',
		modes: { wysiwyg : 1 },
		editorFocus: 0,
		onLoad: function() {
			CKEDITOR.addCss('.cke_editable .embed-block { border: 1px dashed #ccc; padding: 1px; background: #FFFDE3; }' );
			CKEDITOR.addCss('.cke_editable .embed-block::before { content: "▩ "; }' );
		},
		init: function (editor) {
			editor.addCommand('embed_block', new CKEDITOR.dialogCommand('embed_block', {
				allowedContent: true,
				autoParagraph: false,
				exec: function (editor) {
					var existingElement = getSelectedEmbedBlock(editor);
					var existingValues = {};

					if (existingElement && existingElement.$ && existingElement.$.firstChild) {
						getAndSetExistingEmbedBlock(existingElement.$, existingValues);
					}

					var dialogSettings = {
            title: "Embed Block",
						dialogClass: 'editor-embed-block-dialog',
						resizable: true
					};

					// Prepare a save callback to be used upon saving the dialog.
					var saveCallback = function (value) {
						// Use embed block element that is selected
						if(existingElement && existingElement.$ && existingElement.$.firstChild) {
							setEmbedBlock(value, existingElement);

						// Create new embed element
						} else {
							var embed_block = editor.document.createElement('embed-block');
							embed_block.setAttribute("class", "embed-block");
							setEmbedBlock(value, embed_block);
							editor.insertElement(embed_block);
						}
					}

          // Open the dialog for the edit form.
					Drupal.ckeditor.openDialog(editor, Drupal.url('embed-block/dialog/' + editor.config.drupal.format), existingValues, saveCallback, dialogSettings);
				}
			}));

			if (editor.contextMenu) {
				editor.addMenuGroup('embedBlockGroup');
				editor.addMenuItem('embedBlockItem', {
						label: 'Edit Block',
						icon: this.path + 'icons/icon.png',
						command: 'embed_block',
						group: 'embedBlockGroup'
				});
				editor.contextMenu.addListener( function( element ) {
					if (element.getAscendant('embed-block', true)) {
						return { embedBlockItem: CKEDITOR.TRISTATE_OFF };
					}
				});
			}

			editor.ui.addButton('EmbedBlock', {
				label : 'Embed Block',
				toolbar : 'insert',
				command : 'embed_block',
				icon : this.path + 'icons/icon.png'
			});
		}
	});

	/**
   * Get the existing embed block to storage
   *
   * @param {CKEDITOR.editor} editor
   */
	function getAndSetExistingEmbedBlock(embedBlockElement, existingValues) {
		for (var i = 0; i < embedBlockElement.attributes.length; i++) {
			var attribute = embedBlockElement.attributes.item(i);
			var attributeName = attribute.nodeName.toLowerCase();
			if (attributeName.substring(0, 13) === 'data-block-id-') {
				continue;
			}
			existingValues[attributeName] = attribute.nodeValue;
		}
	}

	/**
   * Set block for element.
   *
   * @param {CKEDITOR.editor} editor
   */
	function setEmbedBlock(value, embed_block) {
		embed_block.setAttribute('class', 'embed-block');
		embed_block.setAttribute('data-block-id', value['id']);
		embed_block.setText(value['name']);
	}

	/**
   * Get the existing embed block element.
   *
   * @param {CKEDITOR.editor} editor
   */
  function getSelectedEmbedBlock(editor) {
		var selection = editor.getSelection();
		var selectedElement = selection.getSelectedElement();
		var range = selection.getRanges(true)[0];

    if (range) {
			range.shrink(CKEDITOR.SHRINK_TEXT);
      return editor.elementPath(range.getCommonAncestor()).contains('embed-block', 1);
    }
		return selectedElement;
	}

})(jQuery, Drupal, drupalSettings, CKEDITOR);
