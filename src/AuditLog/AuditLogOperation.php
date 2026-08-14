<?php

declare(strict_types=1);

namespace Drupal\helfi_api_base\AuditLog;

/**
 * Operations that can be written to the audit log.
 */
enum AuditLogOperation: string {
  case EntityRead = 'ENTITY_READ';
  case EntityCreate = 'ENTITY_CREATE';
  case EntityUpdate = 'ENTITY_UPDATE';
  case EntityDelete = 'ENTITY_DELETE';
  case UserLogin = 'USER_LOGIN';
  case UserLogout = 'USER_LOGOUT';

}
