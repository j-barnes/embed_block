<?php

namespace Drupal\embed_block\Plugin\Filter;

use Drupal\Component\Plugin\Exception\PluginException;
use Drupal\Core\Block\BlockManagerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Plugin\Context\ContextHandlerInterface;
use Drupal\Core\Plugin\Context\ContextRepositoryInterface;
use Drupal\Core\Render\RendererInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\filter\FilterProcessResult;
use Drupal\filter\Plugin\FilterBase;
use Drupal\Core\Plugin\ContextAwarePluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Embeds blocks into content.
 *
 * @Filter(
 *   id = "embed_block",
 *   title = @Translation("Embed Block"),
 *   description = @Translation("Allows to place blocks into content."),
 *   type = Drupal\filter\Plugin\FilterInterface::TYPE_MARKUP_LANGUAGE,
 * )
 */
class EmbedBlockFilter extends FilterBase implements ContainerFactoryPluginInterface {

  /**
   * The block plugin manager service.
   *
   * @var \Drupal\Core\Block\BlockManagerInterface
   */
  protected $blockPluginManager;

  /**
   * The renderer service.
   *
   * @var \Drupal\Core\Render\RendererInterface
   */
  protected $renderer;

  /**
   * The current user.
   *
   * @var \Drupal\Core\Session\AccountInterface
   */
  protected $currentUser;

  /**
   * The Drupal context repository.
   *
   * @var \Drupal\context\Entity\ContextRepositoryInterface
   */
  protected $contextRepository;

  /**
   * The plugin context handler.
   *
   * @var \Drupal\Core\Plugin\Context\ContextHandlerInterface
   */
  protected $contextHandler;

  /**
   * Creates a new filter class instance.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Block\BlockManagerInterface $block_plugin_manager
   *   The block plugin manager service.
   * @param \Drupal\Core\Render\RendererInterface $renderer
   *   The renderer service.
   * @param \Drupal\Core\Session\AccountInterface $current_user
   *   The current user.
   * @param \Drupal\Core\Plugin\Context\ContextRepositoryInterface $context_repository
   *   Context repository service.
   * @param \Drupal\Core\Plugin\Context\ContextHandlerInterface $context_handler
   *   The plugin context handler.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, BlockManagerInterface $block_plugin_manager, RendererInterface $renderer, AccountInterface $current_user, ContextRepositoryInterface $context_repository, ContextHandlerInterface $context_handler) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->blockPluginManager = $block_plugin_manager;
    $this->renderer = $renderer;
    $this->currentUser = $current_user;
    $this->contextRepository = $context_repository;
    $this->contextHandler = $context_handler;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition): self {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('plugin.manager.block'),
      $container->get('renderer'),
      $container->get('current_user'),
      $container->get('context.repository'),
      $container->get('context.handler'),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function process($text, $langcode) {
    preg_match_all('/<drupal-embed-block\s+.*?data-block-id="([^"]*)">(.*?)<\/drupal-embed-block>/', $text, $match, PREG_SET_ORDER);
    preg_match_all('/{block:(?<plugin_id>[^}].*)}/', $text, $deprecated_match, PREG_SET_ORDER);

    // Trigger deprecated warning if we find the older block syntax.
    if (!empty($deprecated_match)) {
      @trigger_error('The {block:plugin_id} syntax is deprecated in embed_block:8.x-1.1 and is removed from embed_block:8.x-2.0. Use the <drupal-embed-block> format instead. See https://www.drupal.org/project/embed_block/issues/3225938', E_USER_DEPRECATED);
    }

    $deprecated_response = $this->processTextReplacements($text, $deprecated_match);
    $response = $this->processTextReplacements($deprecated_response->getProcessedText(), $match);
    return $response;
  }

  /**
   * {@inheritdoc}
   */
  protected function processTextReplacements($text, $match) {
    $response = new FilterProcessResult();
    $processed = [];

    foreach ($match as $found) {
      if (!isset($processed[$found[1]])) {
        try {
          /** @var \Drupal\Core\Block\BlockPluginInterface $block_plugin */
          $block_plugin = $this->blockPluginManager->createInstance($found[1]);

          // Inject runtime contexts.
          if ($block_plugin instanceof ContextAwarePluginInterface) {
            $contexts = $this->contextRepository->getRuntimeContexts($block_plugin->getContextMapping());
            $this->contextHandler->applyContextMapping($block_plugin, $contexts);
          }

          // Check access.
          if ($block_plugin->access($this->currentUser)) {
            $build = [
              '#configuration' => $block_plugin->getConfiguration(),
              '#plugin_id' => $block_plugin->getPluginId(),
              '#base_plugin_id' => $block_plugin->getBaseId(),
              '#derivative_plugin_id' => $block_plugin->getDerivativeId(),
            ];

            $build['content'] = $block_plugin->build();
            $block_content = $this->renderer->render($build);
          }

          // User does not have access, set empty string.
          else {
            $block_content = '';
          }

          // Replace the placeholder.
          $text = str_replace($found[0], $block_content, $text);

          // Cache metadata applies regardless if the user can access the block.
          $response->addCacheableDependency($block_plugin);
        }
        catch (PluginException $exception) {
          // The plugin doesn't exist, we don't touch the placeholder.
        }
        $processed[$found[1]] = TRUE;
      }
    }
    $response->setProcessedText($text);
    return $response;
  }

}
