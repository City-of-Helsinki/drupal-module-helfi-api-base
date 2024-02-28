<?php

declare(strict_types=1);

namespace Drupal\helfi_api_base\Entity\Routing;

use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\entity\Routing\RevisionRouteProvider as EntityRevisionRouteProvider;
use Drupal\helfi_api_base\Entity\RevisionController;
use Symfony\Component\Routing\Route;

/**
 * Providers extended revision routes for content entities.
 */
final class RevisionRouteProvider extends EntityRevisionRouteProvider {

  /**
   * {@inheritdoc}
   */
  public function getRoutes(EntityTypeInterface $entity_type) {
    $routes = parent::getRoutes($entity_type);
    $entity_type_id = $entity_type->id();

    if ($revision_revert_language = $this->getRevisionRevertLanguageRoute($entity_type)) {
      $routes->add("entity.$entity_type_id.revision_revert_language_form", $revision_revert_language);
    }

    return $routes;
  }

  /**
   * {@inheritdoc}
   */
  protected function getRevisionHistoryRoute(EntityTypeInterface $entity_type): ?Route {
    if ($route = parent::getRevisionHistoryRoute($entity_type)) {
      $route->setDefault('_controller', RevisionController::class . '::revisionOverviewController');
      return $route;
    }
    return NULL;
  }

  /**
   * Gets the entity revert revision route.
   *
   * @param \Drupal\Core\Entity\EntityTypeInterface $entity_type
   *   The entity type.
   *
   * @return \Symfony\Component\Routing\Route|null
   *   The generated route, if available.
   */
  private function getRevisionRevertLanguageRoute(EntityTypeInterface $entity_type) : ? Route {
    if (!$entity_type->hasLinkTemplate('revision-revert-language-form')) {
      return NULL;
    }
    $entity_type_id = $entity_type->id();
    $route = new Route($entity_type->getLinkTemplate('revision-revert-language-form'));
    $route->addDefaults([
      '_form' => '\Drupal\helfi_api_base\Entity\Form\RevisionRevertTranslationForm',
      'title' => 'Revert to earlier revision',
    ]);
    $route->addRequirements([
      '_entity_access_revision' => "$entity_type_id.update",
    ]);
    $route->setOption('parameters', [
      $entity_type->id() => [
        'type' => 'entity:' . $entity_type->id(),
      ],
      $entity_type->id() . '_revision' => [
        'type' => 'entity_revision:' . $entity_type->id(),
      ],
    ]);
    return $route;
  }

}
