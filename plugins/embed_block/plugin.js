/*
 * Embed Block Plugin
 *
 */
(function(Drupal, CKEDITOR) {
  /**
   * Get the existing embed block to storage
   *
   * @param {CKEDITOR.editor} editor
   */
  function getAndSetExistingEmbedBlock(embedBlockElement, existingValues) {
    for (let i = 0; i < embedBlockElement.attributes.length; i++) {
      const attribute = embedBlockElement.attributes.item(i);
      const attributeName = attribute.nodeName.toLowerCase();
      if (attributeName.substring(0, 13) === "data-block-id-") {
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
  function setEmbedBlock(value, embedBlock) {
    embedBlock.setAttribute("class", "embed-block");
    embedBlock.setAttribute("data-block-id", value.id);
    embedBlock.setText(value.name);
  }

  /**
   * Get the existing embed block element.
   *
   * @param {CKEDITOR.editor} editor
   */
  function getSelectedEmbedBlock(editor) {
    const selection = editor.getSelection();
    const selectedElement = selection.getSelectedElement();
    const range = selection.getRanges(true)[0];

    if (range) {
      range.shrink(CKEDITOR.SHRINK_TEXT);
      return editor
        .elementPath(range.getCommonAncestor())
        .contains("embed-block", 1);
    }
    return selectedElement;
  }

  CKEDITOR.plugins.add("embed_block", {
    requires: "widget",
    icons: "embed_block",
    modes: { wysiwyg: 1 },
    editorFocus: 0,
    init(editor) {
      editor.addCommand(
        "embed_block",
        new CKEDITOR.dialogCommand("embed_block", {
          allowedContent: true,
          autoParagraph: false,
          exec(editor) {
            const existingElement = getSelectedEmbedBlock(editor);
            const existingValues = {};

            if (
              existingElement &&
              existingElement.$ &&
              existingElement.$.firstChild
            ) {
              getAndSetExistingEmbedBlock(existingElement.$, existingValues);
            }

            const dialogSettings = {
              title: "Embed Block",
              dialogClass: "editor-embed-block-dialog",
              resizable: true
            };

            // Prepare a save callback to be used upon saving the dialog.
            const saveCallback = function(value) {
              // Use embed block element that is selected
              if (
                existingElement &&
                existingElement.$ &&
                existingElement.$.firstChild
              ) {
                setEmbedBlock(value, existingElement);

                // Create new embed element
              } else {
                const embedBlock = editor.document.createElement("drupal-embed-block");
                embedBlock.setAttribute("class", "embed-block");
                setEmbedBlock(value, embedBlock);
                editor.insertElement(embedBlock);
              }
            };

            // Open the dialog for the edit form.
            Drupal.ckeditor.openDialog(
              editor,
              Drupal.url(`embed-block/dialog/${editor.config.drupal.format}`),
              existingValues,
              saveCallback,
              dialogSettings
            );
          }
        })
      );

      if (editor.contextMenu) {
        editor.addMenuGroup("embedBlockGroup");
        editor.addMenuItem("embedBlockItem", {
          label: "Edit Block",
          icon: `${this.path}icons/icon.png`,
          command: "embed_block",
          group: "embedBlockGroup"
        });
        editor.contextMenu.addListener(function(element) {
          if (element.getAscendant("drupal-embed-block", true)) {
            return { embedBlockItem: CKEDITOR.TRISTATE_OFF };
          }
        });
      }

      editor.ui.addButton("EmbedBlock", {
        label: "Embed Block",
        toolbar: "insert",
        command: "embed_block",
        icon: `${this.path}icons/icon.png`
      });
    }
  });
})(Drupal, CKEDITOR);
