<?php

declare(strict_types=1);

namespace Drupal\helfi_api_base\Azure\PubSub;

use Symfony\Contracts\EventDispatcher\Event;

/**
 * A DTO to represent PubSub message event.
 */
final class PubSubMessage extends Event {

  /**
   * Constructs a new instance.
   *
   * @param array $data
   *   The arbitrary data.
   */
  public function __construct(
    public readonly array $data,
  ) {
  }

  /**
   * Converts the object to json encoded string.
   *
   * @return string
   *   The json encoded object.
   */
  public function __toString() : string {
    return json_encode($this->data);
  }

}
