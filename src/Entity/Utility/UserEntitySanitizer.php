<?php

declare(strict_types=1);

namespace Drupal\helfi_api_base\Entity\Utility;

use Drupal\Component\Utility\Random;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\user\UserInterface;

/**
 * A class to sanitize user entity values.
 */
final class UserEntitySanitizer {

  use StringTranslationTrait;

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
   * @param array $fields
   *   User fields to be sanitized.
   *
   * @return int
   *   Returns int depending on the operation performed.
   *
   * @throws \Exception
   *   Thrown when user object cannot be found or when
   *   user is found but is active.
   */
  public function sanitizeUserEntity(UserInterface|int $user, array $fields): int {
    if (!$user instanceof UserInterface) {
      $uid = $user;
      $user = $this->entityTypeManager->getStorage('user')->load($user);

      // Throw exception if no user was found.
      if (empty($user)) {
        throw new \Exception(
          sprintf('Unable to find a matching user entity for id "%s".', $uid)
        );
      }
    }

    // Only handle blocked user accounts.
    if ($user->isActive()) {
      throw new \Exception(
        sprintf('Cannot sanitize active users. Block user "%s" before sanitizing it.', $user->id())
      );
    }

    $random = new Random();
    $save = FALSE;

    foreach ($fields as $field) {
      switch ($field) {
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
