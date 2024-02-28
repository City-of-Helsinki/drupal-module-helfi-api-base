<?php

declare(strict_types=1);

namespace Drupal\helfi_api_base\Exception;

use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

/**
 * Exception to indicate invalid package.
 */
final class InvalidPackageException extends BadRequestHttpException {
}
