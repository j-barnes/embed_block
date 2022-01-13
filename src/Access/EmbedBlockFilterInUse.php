<?php

namespace Drupal\embed_block\Access;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Routing\Access\AccessInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\editor\EditorInterface;
use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * An access check to ensure the form can be used only if the filter is enabled.
 */
class EmbedBlockFilterInUse implements AccessInterface {

  /**
   * Check if the filter is used for the given filter.
   *
   * @param \Drupal\Core\Routing\RouteMatchInterface $route
   *   The route.
   * @param \Drupal\Core\Session\AccountInterface $account
   *   The currently logged in account.
   *
   * @return \Drupal\Core\Access\AccessResult
   *   An access result.
   */
  public function access(RouteMatchInterface $route, AccountInterface $account) {
    $parameters = $route->getParameters();
    $access_result = AccessResult::allowedIf($parameters->has('editor'))->addCacheContexts(['route']);

    if ($access_result->isAllowed()) {
      $editor = $parameters->get('editor');

      if ($editor instanceof EditorInterface) {
        return $access_result
          // Check if user has access to the filter format from the editor.
          ->andIf($editor->getFilterFormat()->access('use', $account, TRUE))
          // Check if 'EmbedBlock' is present in editor toolbar.
          ->andIf($this->checkButtonEditorAccess('EmbedBlock', $editor));
      }
    }
    return $access_result;
  }

  /**
   * Checks if the embed block button is enabled in an editor configuration.
   *
   * @param string $button_name
   *   The button name that should be enabled on the editor.
   * @param \Drupal\editor\EditorInterface $editor
   *   The editor entity to check.
   *
   * @return \Drupal\Core\Access\AccessResultInterface
   *   The access result.
   *
   * @throws \Symfony\Component\HttpKernel\Exception\HttpException
   *   When the received Text Editor entity does not use CKEditor. This is
   *   currently only capable of detecting buttons used by CKEditor.
   */
  protected function checkButtonEditorAccess($button_name, EditorInterface $editor) {
    if ($editor->getEditor() !== 'ckeditor') {
      throw new HttpException(500, 'Currently, only CKEditor is supported.');
    }

    $has_button = FALSE;
    $settings = $editor->getSettings();
    foreach ($settings['toolbar']['rows'] as $row) {
      foreach ($row as $group) {
        if (in_array($button_name, $group['items'])) {
          $has_button = TRUE;
          break 2;
        }
      }
    }

    return AccessResult::allowedIf($has_button)
      ->addCacheableDependency($button_name)
      ->addCacheableDependency($editor);
  }

}
