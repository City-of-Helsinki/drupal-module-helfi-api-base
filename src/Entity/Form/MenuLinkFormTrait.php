<?php

declare(strict_types = 1);

namespace Drupal\helfi_api_base\Entity\Form;

use Drupal\Core\Entity\FieldableEntityInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Menu\MenuParentFormSelectorInterface;
use Drupal\Core\Site\Settings;
use Drupal\menu_link_content\Entity\MenuLinkContent;
use Drupal\menu_link_content\MenuLinkContentInterface;

/**
 * A trait to allow entity forms to provide a menu link form.
 *
 * To use this, call the '::attachMenuLinkForm()' method in your
 * '::buildForm()' method, like:
 *
 * @code
 * $form = $this->attachMenuLinkForm($form, $form_state);
 * @endcode
 */
trait MenuLinkFormTrait {

  /**
   * The default menu link field name.
   *
   * @var string
   */
  protected string $menuLinkFieldName = 'menu_link';

  /**
   * The menu parent form selector.
   *
   * @var \Drupal\Core\Menu\MenuParentFormSelectorInterface
   */
  protected MenuParentFormSelectorInterface $menuParentSelector;

  /**
   * Gets the default menu link for given translation.
   *
   * Attempts to load menu link from 'menu_link' field reference and
   * fallbacks to loading it from the menu link storage using the
   * entity's canonical URL.
   *
   * @return \Drupal\menu_link_content\MenuLinkContentInterface
   *   The menu link.
   */
  protected function getDefaultMenuLink() : MenuLinkContentInterface {
    $entity = $this->getEntity();
    assert($entity instanceof FieldableEntityInterface);

    if (!$menuLink = $entity->get($this->menuLinkFieldName)->entity) {
      $storage = $this->entityTypeManager
        ->getStorage('menu_link_content');

      $results = $storage->getQuery()
        ->condition('link.uri', sprintf('entity:%s/%s', $entity->getEntityTypeId(), $entity->id()))
        ->condition('menu_name', array_values($this->getAvailableMenus()), 'IN')
        ->sort('id')
        ->range(0, 1)
        ->accessCheck(FALSE)
        ->execute();

      $menuLink = empty($results) ? MenuLinkContent::create([]) : MenuLinkContent::load(reset($results));
    }

    $entity = $this->entityRepository->getTranslationFromContext($menuLink);
    assert($entity instanceof MenuLinkContentInterface);

    return $entity;
  }

  /**
   * Gets the available menus.
   *
   * @return array
   *   List of available menus.
   */
  protected function getAvailableMenus() : array {
    // Allow available menus to be overridden in settings.php:
    // @code
    // $settings['helfi_{entity_type_id}_available_menus'] = ['your_menu'];
    // @endcode
    // Replace {entity_type_id} with a TPR entity type id, like tpr_unit or
    // tpr_service.
    $key = sprintf('helfi_%s_available_menus', $this->getEntity()->getEntityTypeId());
    $menus = Settings::get($key, ['main']);

    return array_combine($menus, $menus);
  }

  /**
   * Attach menu link form.
   *
   * @param array $form
   *   The form.
   * @param \Drupal\Core\Form\FormStateInterface $formState
   *   The form state.
   *
   * @return array
   *   The form.
   */
  protected function attachMenuLinkForm(array $form, FormStateInterface $formState) : array {
    $menuLink = $this->getDefaultMenuLink();
    $formState->set($this->menuLinkFieldName, $menuLink);

    $form['menu'] = [
      '#type' => 'details',
      '#title' => $this->t('Menu settings'),
      '#access' => $this->currentUser()->hasPermission('administer menu'),
      '#group' => 'advanced',
      '#weight' => 90,
      '#tree' => TRUE,
    ];

    $form['menu']['enabled'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Provide a menu link'),
      '#default_value' => !$menuLink->isNew() && $menuLink->hasTranslation($this->getEntity()->language()->getId()),
    ];

    $form['menu']['link'] = [
      '#type' => 'container',
      '#parents' => ['menu'],
      '#states' => [
        'invisible' => [
          'input[name="menu[enabled]"]' => ['checked' => FALSE],
        ],
      ],
    ];

    $form['menu']['published'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Enabled'),
      '#default_value' => $menuLink->isNew() || $menuLink->isPublished(),
      '#states' => [
        'invisible' => [
          'input[name="menu[enabled]"]' => ['checked' => FALSE],
        ],
      ],
      '#description' => $this->t('All languages'),
    ];

    $form['menu']['link']['title'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Menu link title'),
      '#default_value' => $menuLink->label(),
    ];

    $id = $menuLink->isNew() ? '' : $menuLink->getPluginId();
    $default = $menuLink->getMenuName() . ':' . $menuLink->getParentId();

    $form['menu']['link']['menu_parent'] = $this
      ->menuParentSelector
      ->parentSelectElement($default, $id, $this->getAvailableMenus());

    $form['menu']['link']['weight'] = [
      '#type' => 'number',
      '#title' => t('Weight'),
      '#default_value' => $menuLink->getWeight(),
      '#description' => $this->t('Menu links with lower weights are displayed before links with higher weights.'),
    ];

    $form['actions']['submit']['#submit'][] = '::attachMenuLinkFormSubmit';

    return $form;
  }

  /**
   * Submit handler for ::attachMenuLinkForm().
   *
   * @param array $form
   *   The form.
   * @param \Drupal\Core\Form\FormStateInterface $formState
   *   The form state.
   */
  public function attachMenuLinkFormSubmit(array $form, FormStateInterface $formState) : void {
    $entity = $this->getEntity();
    assert($entity instanceof FieldableEntityInterface);
    /** @var \Drupal\menu_link_content\MenuLinkContentInterface $menuLink */
    $menuLink = $formState->get('menu_link');

    $menuLink = $menuLink->hasTranslation($entity->language()->getId()) ?
      $menuLink->getTranslation($entity->language()->getId()) :
      $menuLink->addTranslation($entity->language()->getId());

    $values = $formState->getValue('menu');

    // Delete the menu link if user disabled it.
    if (empty($values['enabled']) && !$menuLink->isNew()) {
      // Make sure that deleting a translation does not delete the whole entity.
      if (!$menuLink->isDefaultTranslation()) {
        $menuLink = $menuLink->getUntranslated();
        $menuLink->removeTranslation($entity->language()->getId());
        $menuLink->save();
      }
      else {
        $menuLink->delete();
      }
    }
    if (!empty($values['enabled'])) {
      $values['published'] ? $menuLink->setPublished() : $menuLink->setUnpublished();

      [$menuName, $parent] = explode(':', $values['menu_parent'], 2);

      $menuLink->set('title', $values['title'])
        ->set('link', [
          'uri' => sprintf('entity:%s/%s', $entity->getEntityTypeId(), $entity->id()),
        ])
        ->set('menu_name', $menuName)
        ->set('parent', $parent)
        ->set('langcode', $entity->language()->getId())
        ->set('weight', $values['weight'])
        ->save();

      $entity->set($this->menuLinkFieldName, $menuLink)->save();
    }
  }

}
