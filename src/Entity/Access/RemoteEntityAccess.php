<?php

declare(strict_types = 1);

namespace Drupal\helfi_api_base\Entity\Access;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Access\AccessResultInterface;
use Drupal\Core\Entity\EntityAccessControlHandler;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;

/**
 * Defines the access control handler for remote entities.
 */
final class RemoteEntityAccess extends EntityAccessControlHandler {

  /**
   * {@inheritdoc}
   */
  protected function checkAccess(EntityInterface $entity, $operation, AccountInterface $account) : AccessResultInterface {
    return match ($operation) {
      'view' => AccessResult::allowedIfHasPermissions($account, [
        'view remote entities',
        'administer remote entities',
      ], 'OR'),
      'update' => AccessResult::allowedIfHasPermissions($account, [
        'edit remote entities',
        'administer remote entities',
      ], 'OR'),
      'delete' =>  AccessResult::allowedIfHasPermissions($account, [
        'delete remote entities',
        'administer remote entities',
      ], 'OR'),
      default => AccessResult::neutral(),
    };
  }

  /**
   * {@inheritdoc}
   */
  protected function checkCreateAccess(AccountInterface $account, array $context, $entity_bundle = NULL) : AccessResultInterface {
    return AccessResult::allowedIfHasPermissions($account, [
      'create remote entities',
      'administer remote entities',
    ], 'OR');
  }

}
