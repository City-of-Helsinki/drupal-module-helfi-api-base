<?php

declare(strict_types = 1);

namespace Drupal\helfi_api_base\Entity\Routing;

use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Entity\Routing\AdminHtmlRouteProvider;
use Drupal\helfi_api_base\Entity\Form\ContentEntitySettingsForm;
use Symfony\Component\Routing\Route;

/**
 * Provides routes for content entities.
 *
 * @see \Drupal\Core\Entity\Routing\AdminHtmlRouteProvider
 * @see \Drupal\Core\Entity\Routing\DefaultHtmlRouteProvider
 */
final class EntityRouteProvider extends AdminHtmlRouteProvider {

  /**
   * {@inheritdoc}
   */
  public function getRoutes(EntityTypeInterface $entity_type) {
    $collection = parent::getRoutes($entity_type);

    $entity_type_id = $entity_type->id();

    if ($settings_form_route = $this->getSettingsFormRoute($entity_type)) {
      $collection->add("$entity_type_id.settings", $settings_form_route);
    }

    if ($revision_routes = $this->getRevisionRoutes($entity_type)) {
      foreach ($revision_routes as $route_name => $route) {
        $collection->add($route_name, $route);
      }
    }

    return $collection;
  }

  /**
   * Gets the revision routes.
   *
   * @param \Drupal\Core\Entity\EntityTypeInterface $entity_type
   *   The entity type.
   *
   * @return \Symfony\Component\Routing\Route[]|null
   *   The routes.
   */
  protected function getRevisionRoutes(EntityTypeInterface $entity_type) : ? array {
    if (!$entity_type->hasKey('revision_table')) {
      return NULL;
    }
    $entity_type_id = $entity_type->id();

    $routes = [];

    if ($version_history_link = $entity_type->getLinkTemplate('version-history')) {
      $version_history_route = new Route($version_history_link);
      $routes[sprintf('entity.%s.version_history', $entity_type_id)] = $version_history_route;

      $confirm_route_base = sprintf('%s/{%s_revision}/revert', $version_history_link, $entity_type_id);

      $revert_confirm_route = new Route($confirm_route_base);
      $routes[sprintf('%s.revision_revert_confirm', $entity_type_id)] = $revert_confirm_route;

      $revert_translation_route = new Route(sprintf('%s/{langcode}', $confirm_route_base));
      $routes[sprintf('%s.revision_reveret_translation_confirm', $entity_type_id)] = $revert_translation_route;
    }

    if ($entity_revision_link = $entity_type->getLinkTemplate('revision')) {
      $entity_revision = new Route($entity_revision_link);
      $routes[sprintf('entity.%s.revision', $entity_type_id)] = $entity_revision;
    }

    return $routes;
  }

  /**
   * Gets the settings form route.
   *
   * @param \Drupal\Core\Entity\EntityTypeInterface $entity_type
   *   The entity type.
   *
   * @return \Symfony\Component\Routing\Route|null
   *   The generated route, if available.
   */
  protected function getSettingsFormRoute(EntityTypeInterface $entity_type) : ? Route {
    if (!$entity_type->getBundleEntityType()) {
      $route = new Route("/admin/structure/{$entity_type->id()}/settings");
      $route
        ->setDefaults([
          '_form' => ContentEntitySettingsForm::class,
          '_title' => "{$entity_type->getLabel()} settings",
        ])
        ->setRequirement('_permission', $entity_type->getAdminPermission())
        ->setOption('_admin_route', TRUE);

      return $route;
    }

    return NULL;
  }

}
