<?php

declare(strict_types = 1);

namespace Drupal\helfi_api_base\Entity\Form;

use Drupal\Core\Entity\RevisionableInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\entity\Form\RevisionRevertForm;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Provides a form for reverting an entity revision for a single translation.
 */
final class RevisionRevertTranslationForm extends RevisionRevertForm {

  /**
   * The language to be reverted.
   *
   * @var string
   */
  protected string $langcode;

  /**
   * The language manager.
   *
   * @var \Drupal\Core\Language\LanguageManagerInterface
   */
  protected LanguageManagerInterface $languageManager;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    $instance = parent::create($container);
    $instance->languageManager = $container->get('language_manager');
    return $instance;
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'revision_revert_translation_confirm';
  }

  /**
   * {@inheritdoc}
   */
  public function getQuestion() {
    return $this->t('Are you sure you want to revert @language translation to the revision from %revision-date?', [
      '@language' => $this->languageManager->getLanguageName($this->langcode),
      '%revision-date' => $this->dateFormatter->format($this->revision->getRevisionCreationTime()),
    ]);
  }

  /**
   * {@inheritdoc}
   */
  public function getDescription() {
    return '';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, $_entity_revision = NULL, Request $request = NULL) {
    $this->langcode = $request->attributes->get('langcode');

    return parent::buildForm($form, $form_state, $_entity_revision);
  }

  /**
   * {@inheritdoc}
   */
  protected function prepareRevision(RevisionableInterface $revision) {
    $translation = $revision->getTranslation($this->langcode);
    return parent::prepareRevision($translation);
  }

}
