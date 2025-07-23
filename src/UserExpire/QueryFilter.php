<?php

declare(strict_types=1);

namespace Drupal\helfi_api_base\UserExpire;

/**
 * Contains DB query filters used by UserExpireManager.
 */
final readonly class QueryFilter {

  /**
   * Constructs a new instance.
   *
   * @param int $expire
   *   The account expire time. Used as "current_time - expire".
   * @param int|null $status
   *   The account status.
   * @param string|null $queryTag
   *   The query tag.
   */
  public function __construct(
    public int $expire,
    public ?int $status = NULL,
    public ?string $queryTag = NULL,
  ) {
  }

}
