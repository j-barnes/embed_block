<?php

namespace Drupal\embed_block\Form;

use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\CloseModalDialogCommand;
use Drupal\Core\Ajax\HtmlCommand;
use Drupal\Core\Block\BlockManagerInterface;
use Drupal\Core\Plugin\Context\ContextRepositoryInterface;
use Drupal\Core\Form\BaseFormIdInterface;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\editor\Ajax\EditorDialogSave;
use Drupal\editor\EditorInterface;
use Drupal\filter\Entity\FilterFormat;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a link dialog for text editors.
 */
class EmbedBlockPluginDialog extends FormBase implements BaseFormIdInterface {

  /**
   * Context repository.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $contextRepository;

  /**
   * The block manager.
   *
   * @var \Drupal\Core\Block\BlockManagerInterface
   */
  protected $blockManager;

  /**
   * Class constructor.
   *
   * @param \Drupal\Core\Plugin\Context\ContextRepositoryInterface $contextRepository
   *   Context repository service.
   * @param \Drupal\Core\Block\BlockManagerInterface $blockManager
   *   Block manager service.
   */
  public function __construct(ContextRepositoryInterface $contextRepository, BlockManagerInterface $blockManager) {
    $this->contextRepository = $contextRepository;
    $this->blockManager = $blockManager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container): self {
    return new static(
      $container->get('context.repository'),
      $container->get('plugin.manager.block'),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId(): string {
    return 'embed_block_dialog';
  }

  /**
   * {@inheritdoc}
   */
  public function getBaseFormId(): string {
    return 'editor_embed_block_dialog';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, EditorInterface $editor = NULL, FilterFormat $filter_format = NULL): array {
    $input = $form_state->getUserInput();
    $block_id = $input['editor_object']['data-block-id'] ?? [];
    $form['#tree'] = TRUE;
    $form['#attached']['library'][] = 'editor/drupal.editor.dialog';
    $form['#attached']['library'][] = 'core/drupal.dialog.ajax';
    $form['#prefix'] = '<div id="embed-block-dialog-form">';
    $form['#suffix'] = '</div>';

    $form['embed_block'] = [
      '#title' => $this->t('Select Block'),
      '#type' => 'select',
      '#options' => $this->getBlockOptions(),
      '#default_value' => $block_id,
    ];

    $form['actions'] = [
      '#type' => 'actions',
    ];

    $form['actions']['save_modal'] = [
      '#type' => 'submit',
      '#value' => $this->t('Save'),
      '#submit' => [],
      '#ajax' => [
        'wrapper' => 'embed-block-dialog-form',
        'callback' => '::submitForm',
        'event' => 'click',
      ],
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $response = new AjaxResponse();
    $value = $form_state->getValues();

    if ($form_state->getErrors()) {
      unset($form['#prefix'], $form['#suffix']);
      $form['status_messages'] = [
        '#type' => 'status_messages',
        '#weight' => -10,
      ];
      $response->addCommand(new HtmlCommand('#embed-block-dialog-form', $form));
    }
    else {
      $response->addCommand(new EditorDialogSave(
        [
          'id' => $value['embed_block'],
          'name' => $this->getBlockDefinitions()[$value['embed_block']]['admin_label'],
        ]
      ));
      $response->addCommand(new CloseModalDialogCommand());
    }

    return $response;
  }

  /**
   * Returns an key => value array based on allowed blocks.
   *
   * Generates hierarchy for block selection dropdown.
   *
   * @return array
   *   Array of options from definitions.
   *
   * @throws \Drupal\Component\Plugin\Exception\PluginException
   *   Thrown if the plugin ID cannot be found.
   */
  public function getBlockOptions(): array {
    $options = [];
    foreach ($this->getBlockDefinitions() as $plugin_id => $definition) {
      $category = (string) $definition['category'];
      $options[$category][$plugin_id] = $definition['admin_label'];
    }
    return $options;
  }

  /**
   * Returns block definitions.
   *
   * @return array
   *   Array of block definitions.
   */
  public function getBlockDefinitions(): array {
    return $this->blockManager->getDefinitionsForContexts($this->contextRepository->getAvailableContexts());
  }

}
