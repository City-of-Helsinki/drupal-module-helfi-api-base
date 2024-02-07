<?php

declare(strict_types = 1);

namespace Drupal\helfi_api_base\Environment;

/**
 * A value object to store environment metadata.
 */
final class EnvironmentMetadata {

  /**
   * Constructs a new instance.
   *
   * @param string $openshiftConsoleLink
   *   The OpenShift console link.
   */
  public function __construct(
    private readonly string $openshiftConsoleLink,
  ) {
  }

  /**
   * Construct a new instance from array.
   *
   * @param array $data
   *   The data.
   *
   * @return \Drupal\helfi_api_base\Environment\EnvironmentMetadata|null
   *   The self or null.
   */
  public static function createFromArray(array $data) : ? self {
    if (empty($data)) {
      return NULL;
    }

    $required = [
      'openshift_console_link',
    ];

    foreach ($required as $key) {
      if (!isset($data[$key])) {
        throw new \InvalidArgumentException(sprintf('Missing required "%s".', $key));
      }
    }

    [
      'openshift_console_link' => $openShiftConsoleLink,
    ] = $data;

    return new self($openShiftConsoleLink);
  }

  /**
   * Gets the link to OpenShift console.
   *
   * @return string
   *   The OpenShift console link.
   */
  public function getOpenshiftConsoleLink(): string {
    return $this->openshiftConsoleLink;
  }

}
