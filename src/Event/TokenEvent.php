<?php

declare(strict_types=1);

namespace Drupal\helfi_api_base\Event;

use Drupal\Component\Render\MarkupInterface;
use Drupal\Core\Render\BubbleableMetadata;
use Symfony\Contracts\EventDispatcher\Event;

/**
 * Token event.
 */
class TokenEvent extends Event {

  /**
   * Replacement value.
   */
  private string|MarkupInterface|null $replacement = NULL;

  /**
   * Constructs a new instance.
   *
   * @param string $type
   *   The machine-readable name of the type (group) of token being replaced.
   * @param string $token
   *   Token to be replaced.
   * @param array $data
   *   Data objects to be used when generating replacement values.
   * @param array $options
   *   An associative array of options for token replacement.
   * @param \Drupal\Core\Render\BubbleableMetadata $bubbleable_metadata
   *   The bubbleable metadata.
   */
  public function __construct(
    public readonly string $type,
    public readonly string $token,
    public readonly array $data,
    public readonly array $options,
    public readonly BubbleableMetadata $bubbleable_metadata,
  ) {
  }

  /**
   * Gets the replacement value.
   *
   * @return string|MarkupInterface|null
   *   The replacement value.
   */
  public function getReplacementValue() : string|MarkupInterface|null {
    return $this->replacement;
  }

  /**
   * Sets the replacement value.
   *
   * @param string|MarkupInterface|null $replacement
   *   The replacement value.
   */
  public function setReplacementValue(string|MarkupInterface|null $replacement) : void {
    $this->replacement = $replacement;
  }

  /**
   * Checks if the event has replacement value.
   *
   * @return bool
   *   If event has replacement value.
   */
  public function hasReplacementValue() : bool {
    return $this->replacement !== NULL;
  }

}
