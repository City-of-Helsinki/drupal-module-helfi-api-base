<?php

declare(strict_types = 1);

namespace Drupal\helfi_api_base\Plugin\Validation\Constraint;

use JsonSchema\Validator;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * Provides a validator to validate JSON against the given schema.
 */
final class JsonSchemaConstraintValidator extends ConstraintValidator {

  /**
   * {@inheritdoc}
   */
  public function validate($value, Constraint $constraint) : void {
    if (!file_exists($constraint->schema)) {
      $this->context->addViolation('Failed to load JSON schema: "%schema"', [
        '%schema' => $constraint->schema,
      ]);
    }
    try {
      $content = \GuzzleHttp\json_decode($value->value, TRUE);
    }
    catch (\InvalidArgumentException $e) {
      $this->context->addViolation('Failed to parse JSON: %message', [
        '%message' => $e->getMessage(),
      ]);
    }
    $validator = new Validator();
    $validator->validate($content, (object) [
      '$ref' => $constraint->schema,
    ]);

    if (!$validator->isValid()) {
      foreach ($validator->getErrors() as $error) {
        $message = sprintf('%s (property: "%s")', $error['message'], $error['property']);
        $this->context->addViolation('Failed to validate JSON: %message', [
          '%message' => $message,
        ]);
      }
    }
  }

}
