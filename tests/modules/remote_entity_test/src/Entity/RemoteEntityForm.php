<?php

declare(strict_types=1);

namespace Drupal\remote_entity_test\Entity;

use Drupal\Core\Datetime\DateFormatterInterface;
use Drupal\Core\Entity\ContentEntityForm as CoreContentEntityForm;
use Drupal\Core\Form\FormStateInterface;
use Drupal\helfi_api_base\Entity\Form\MenuLinkFormTrait;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Form handler for entity forms.
 */
class RemoteEntityForm extends CoreContentEntityForm {

  use MenuLinkFormTrait;

  /**
   * The date formatter service.
   *
   * @var \Drupal\Core\Datetime\DateFormatterInterface
   */
  protected DateFormatterInterface $dateFormatter;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    $instance = parent::create($container);
    $instance->dateFormatter = $container->get('date.formatter');
    $instance->menuParentSelector = $container->get('menu.parent_form_selector');
    return $instance;
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) : array {
    $form = parent::buildForm($form, $form_state);
    return $this->attachMenuLinkForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $entityTypeId = $this->entity->getEntityTypeId();

    parent::save($form, $form_state);

    $this->messenger()
      ->addStatus($this->t('%title saved.', [
        '%title' => $this->entity->label(),
      ]));

    $form_state->setRedirect('entity.' . $entityTypeId . '.canonical', [
      $entityTypeId => $this->entity->id(),
    ]);
  }

}
