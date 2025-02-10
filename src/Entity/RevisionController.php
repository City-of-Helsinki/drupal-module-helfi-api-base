<?php

declare(strict_types=1);

namespace Drupal\helfi_api_base\Entity;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityRepositoryInterface;
use Drupal\entity\Controller\RevisionOverviewController;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a controller which shows the revision history.
 */
class RevisionController extends RevisionOverviewController {

  /**
   * The entity repository.
   *
   * @var \Drupal\Core\Entity\EntityRepositoryInterface
   */
  protected EntityRepositoryInterface $entityRepository;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    $instance = parent::create($container);
    $instance->entityRepository = $container->get('entity.repository');
    return $instance;
  }

  /**
   * Generates an overview table of older revisions of an entity.
   *
   * @param \Drupal\Core\Entity\ContentEntityInterface $entity
   *   An entity object.
   *
   * @return array
   *   A render array.
   */
  protected function revisionOverview(ContentEntityInterface $entity) {
    /** @var \Drupal\Core\Entity\ContentEntityStorageInterface $entity_storage */
    $entity_storage = $this->entityTypeManager()->getStorage($entity->getEntityTypeId());
    $revision_ids = $this->revisionIds($entity);
    $entity_revisions = $entity_storage->loadMultipleRevisions($revision_ids);

    $header = [$this->t('Revision'), $this->t('Operations')];
    $rows = [];
    foreach ($entity_revisions as $revision) {
      /** @var \Drupal\Core\Entity\ContentEntityInterface $revision */
      $revision = $this->entityRepository->getTranslationFromContext($revision);

      $row = [];
      $row[] = $this->getRevisionDescription($revision, $revision->isDefaultRevision());

      if ($revision->isDefaultRevision()) {
        $row[] = [
          'data' => [
            '#prefix' => '<em>',
            '#markup' => $this->t('Current revision'),
            '#suffix' => '</em>',
          ],
        ];
        foreach ($row as &$current) {
          $current['class'] = ['revision-current'];
        }
      }
      else {
        $links = $this->getOperationLinks($revision);
        $row[] = [
          'data' => [
            '#type' => 'operations',
            '#links' => $links,
          ],
        ];
      }

      $rows[] = $row;
    }

    $build[$entity->getEntityTypeId() . '_revisions_table'] = [
      '#theme' => 'table',
      '#rows' => $rows,
      '#header' => $header,
    ];

    // We have no clue about caching yet.
    $build['#cache']['max-age'] = 0;

    return $build;
  }

  /**
   * {@inheritdoc}
   */
  protected function buildRevertRevisionLink(EntityInterface $entity_revision) {
    if ($entity_revision->hasLinkTemplate('revision-revert-language-form')) {
      return [
        'title' => $this->t('Revert'),
        'url' => $entity_revision->toUrl('revision-revert-language-form')->setRouteParameter('langcode', $entity_revision->language()->getId()),
      ];
    }

    throw new \Exception("Revision revert language form not available");
  }

}
