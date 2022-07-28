<?php

declare(strict_types = 1);

namespace Drupal\helfi_api_base\Plugin\Validation\Constraint;

use JsonSchema\Validator;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

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
    $validator = new Validator();
    $validator->validate($value->value, (object) [
      '$ref' => $constraint->schema,
    ]);

    if (!$validator->isValid()) {
      foreach ($validator->getErrors() as $error) {
        $this->context->addViolation('Failed to validate JSON: %message', [
          '%message' => $error['message'],
        ]);
      }
    }
  }

}
