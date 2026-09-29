<?php

declare(strict_types=1);

namespace Drupal\helfi_api_base\Environment;

/**
 * Thrown when the environment resolver cannot find a project or environment.
 */
final class EnvironmentResolverException extends \InvalidArgumentException {
}
