<?php

declare(strict_types=1);

namespace Drupal\helfi_api_base\Token;

use Drupal\Core\Render\BubbleableMetadata;
use Drupal\helfi_api_base\Event\TokenEvent;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * The token manager.
 */
final class TokenManager {

  /**
   * Constructs a new instance.
   *
   * @param \Symfony\Component\EventDispatcher\EventDispatcherInterface $dispatcher
   *   The event dispatcher.
   */
  public function __construct(
    private readonly EventDispatcherInterface $dispatcher,
  ) {
  }

  /**
   * Provide replacement values for placeholder tokens.
   *
   * @param string $type
   *   The machine-readable name of the type (group) of token being replaced.
   * @param array $tokens
   *   Tokens to be replaced. The keys are the token names, and the values are
   *   the raw [type:token] strings.
   * @param array $data
   *   Data objects to be used when generating replacement values.
   * @param array $options
   *   An associative array of options for token replacement.
   * @param \Drupal\Core\Render\BubbleableMetadata $bubbleable_metadata
   *   The bubbleable metadata.
   *
   * @return array
   *   An associative array of replacement values, keyed by the raw
   *   [type:token] strings.
   *
   * @see hook_tokens()
   */
  public function getReplacements(
    string $type,
    array $tokens,
    array $data,
    array $options,
    BubbleableMetadata $bubbleable_metadata
  ) : array {
    $replacements = [];

    foreach ($tokens as $token => $original) {
      $tokenEvent = $this->dispatcher->dispatch(new TokenEvent(
        $type, $token, $data, $options, $bubbleable_metadata
      ));

      if ($tokenEvent->hasReplacementValue()) {
        $replacements[$original] = $tokenEvent->getReplacementValue();
      }
    }

    return $replacements;
  }

}
