<?php

declare(strict_types=1);

namespace Drupal\helfi_api_base\Entity\Utility;

use Drupal\Component\Utility\Random;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\user\UserInterface;

/**
 * A class to sanitize user entity values.
 */
final class UserEntitySanitizer {

  /**
   * Constructs a new instance.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   The entity type manager.
   */
  public function __construct(
    protected readonly EntityTypeManagerInterface $entityTypeManager,
  ) {
  }

  /**
   * Method to get a sanitized string value.
   *
   * @param \Drupal\user\UserInterface|int $user
   *   User object or user entity as an integer.
   * @param array $values
   *   User field values to be sanitized.
   *
   * @return int
   *   Returns int depending on the operation performed.
   */
  public function sanitizeUserEntity(UserInterface|int $user, array $values): int {
    if (!$user instanceof UserInterface) {
      $user = $this->entityTypeManager->getStorage('user')->load($user);
    }

    // Only handle blocked user accounts.
    if ($user->isActive()) {
      return 0;
    }

    $random = new Random();
    $save = FALSE;

    foreach ($values as $name => $value) {
      if (!$value) {
        continue;
      }
      switch ($name) {
        case 'username':
          $user->setUsername($random->word(20));
          break;

        case 'email':
          $user->setEmail("{$random->word(20)}@drupal.hel.ninja");
          break;

        case 'password':
          $user->setPassword($random->string(32));
          break;
      }
      $save = TRUE;
    }

    // Save the user if the values were changed and return the operation result.
    return $save ? $user->save() : 0;
  }

}
