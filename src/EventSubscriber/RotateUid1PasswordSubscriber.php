<?php

declare(strict_types=1);

namespace Drupal\helfi_api_base\EventSubscriber;

use Drupal\Core\Password\PasswordGeneratorInterface;
use Drupal\helfi_api_base\Features\FeatureManager;
use Drupal\user\Entity\User;
use Symfony\Contracts\EventDispatcher\Event;

/**
 * Rotates UID1 password on deploy.
 */
final class RotateUid1PasswordSubscriber extends DeployHookEventSubscriberBase {

  /**
   * Constructs a new instance.
   *
   * @param \Drupal\helfi_api_base\Features\FeatureManager $featureManager
   *   The feature manager.
   * @param \Drupal\Core\Password\PasswordGeneratorInterface $passwordGenerator
   *   The password generator.
   */
  public function __construct(
    private readonly FeatureManager $featureManager,
    private readonly PasswordGeneratorInterface $passwordGenerator,
  ) {
  }

  /**
   * {@inheritdoc}
   */
  public function onPostDeploy(Event $event) : void {
    if (!$this->featureManager->isEnabled(FeatureManager::ROTATE_UID1_PASSWORD)) {
      return;
    }
    if (!$account = User::load(1)) {
      return;
    }
    $account->setPassword($this->passwordGenerator->generate(30))
      ->save();
  }

}
