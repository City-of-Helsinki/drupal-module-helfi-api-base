<?php

declare(strict_types = 1);

namespace Drupal\helfi_api_base\Entity\Form;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Menu\MenuParentFormSelectorInterface;
use Drupal\Core\Site\Settings;
use Drupal\menu_link_content\Entity\MenuLinkContent;
use Drupal\menu_link_content\MenuLinkContentInterface;

/**
 * A trait to allow entity forms to provide menu link form.
 *
 * This can be used by calling this from your form controller, for
 * example by adding this to your form controller's ::buildForm()
 * callback.
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
   * Gets the menu parent form selector.
   *
   * @return \Drupal\Core\Menu\MenuParentFormSelectorInterface
   *   The menu parent form selector.
   */
  protected function menuParentFormSelector() : MenuParentFormSelectorInterface {
    if (!$this->menuParentSelector) {
      $this->menuParentSelector = \Drupal::service('menu.parent_form_selector');
    }
    return $this->menuParentSelector;
  }

  /**
   * Gets the default menu link for given translation.
   *
   * Attempts to load menu link from 'menu_link' field reference and
   * fallbacks to loading it straight from the menu link storage.
   *
   * @return \Drupal\menu_link_content\MenuLinkContentInterface
   *   The menu link.
   */
  protected function getDefaultMenuLink() : MenuLinkContentInterface {
    $entity = $this->getEntity();

    if ($menu_link = $this->getEntity()->get($this->menuLinkFieldName)->entity) {
      return $this->entityRepository->getTranslationFromContext($menu_link);
    }
    $storage = $this->entityTypeManager->getStorage('menu_link_content');

    $results = $storage->getQuery()
      ->condition('link.uri', sprintf('entity:%s/%s', $entity->getEntityTypeId(), $entity->id()))
      ->condition('menu_name', array_values($this->getAvailableMenus()), 'IN')
      ->sort('id')
      ->range(0, 1)
      ->execute();

    $menu_link = empty($results) ? MenuLinkContent::create([]) : MenuLinkContent::load(reset($results));

    return $this->entityRepository->getTranslationFromContext($menu_link);
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
    // $settings['helfi_{entity_type_id}_available_menus'] = ['main', 'your_menu'];
    // @endcode
    // Replace {entity_type_id} with a TPR entity type id, like tpr_unit or tpr_service.
    $setting_key = sprintf('helfi_%s_available_menus', $this->getEntity()->getEntityTypeId());
    $menus = Settings::get($setting_key, ['main']);

    return array_combine($menus, $menus);
  }

  /**
   * Attach menu link form.
   *
   * @param array $form
   *   The form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The form state.
   *
   * @return array
   *   The form.
   */
  protected function attachMenuLinkForm(array $form, FormStateInterface $form_state) : array {
    $menu_link = $this->getDefaultMenuLink();
    $form_state->set($this->menuLinkFieldName, $menu_link);

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
      '#default_value' => !$menu_link->isNew() && $menu_link->hasTranslation($this->getEntity()->language()->getId()),
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

    $form['menu']['link']['title'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Menu link title'),
      '#default_value' => $menu_link->label(),
    ];

    $id = $menu_link->isNew() ? '' : $menu_link->getPluginId();
    $default = $menu_link->getMenuName() . ':' . $menu_link->getParentId();


    $form['menu']['link']['menu_parent'] = $this->menuParentSelector
      ->parentSelectElement($default, $id, $this->getAvailableMenus());

    $form['actions']['submit']['#submit'][] = '::attachMenuLinkFormSubmit';

    return $form;
  }

  /**
   * Submit handler for ::attachMenuLinkForm().
   *
   * @param array $form
   *   The form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The form state.
   */
  public function attachMenuLinkFormSubmit(array $form, FormStateInterface $form_state) : void {
    /** @var \Drupal\helfi_tpr\Entity\TprEntityBase $entity */
    $entity = $this->getEntity();
    /** @var \Drupal\menu_link_content\MenuLinkContentInterface $menu_link */
    $menu_link = $form_state->get('menu_link');

    $menu_link = $menu_link->hasTranslation($entity->language()->getId()) ?
      $menu_link->getTranslation($entity->language()->getId()) :
      $menu_link->addTranslation($entity->language()->getId());

    $values = $form_state->getValue('menu');

    // Delete the menu link if user disabled it.
    if (empty($values['enabled']) && !$menu_link->isNew()) {
      // Make sure that deleting a translation does not delete the whole entity.
      if (!$menu_link->isDefaultTranslation()) {
        $menu_link = $menu_link->getUntranslated();
        $menu_link->removeTranslation($entity->language()->getId());
        $menu_link->save();
      }
      else {
        $menu_link->delete();
      }
    }
    if (!empty($values['enabled'])) {
      // Menu link inherits published status from parent entity.
      $entity->isPublished() ? $menu_link->setPublished() : $menu_link->setUnpublished();

      [$menu_name, $parent] = explode(':', $values['menu_parent'], 2);

      $menu_link->set('title', $values['title'])
        ->set('link', [
          'uri' => sprintf('entity:%s/%s', $entity->getEntityTypeId(), $entity->id()),
        ])
        ->set('menu_name', $menu_name)
        ->set('parent', $parent)
        ->set('langcode', $entity->language()->getId())
        ->save();

      $entity->set('menu_link', $menu_link)->save();
    }
  }

}
