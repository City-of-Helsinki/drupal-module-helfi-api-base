<?php

declare(strict_types = 1);

namespace Drupal\helfi_api_base\Plugin\Validation\Constraint;

use Drupal\Core\Field\FieldItemListInterface;
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
      $this->context->addViolation('Failed to load JSON schema: "@schema"', [
        '@schema' => $constraint->schema,
      ]);
      return;
    }

    if ($value instanceof FieldItemListInterface) {
      $propertyName = $value->getFieldDefinition()
        ->getMainPropertyName();
      $value = $value->{$propertyName};
    }

    if (!is_object($value)) {
      try {
        $value = \GuzzleHttp\json_decode($value);
      }
      catch (\InvalidArgumentException $e) {
        $this->context->addViolation('Failed to parse JSON: @message', [
          '@message' => $e->getMessage(),
        ]);
        return;
      }
    }
    $validator = new Validator();
    $validator->validate($value, (object) [
      '$ref' => $constraint->schema,
    ]);

    if (!$validator->isValid()) {
      foreach ($validator->getErrors() as $error) {
        $message = sprintf('%s (property: "%s")', $error['message'], $error['property']);
        $this->context->addViolation('Failed to validate JSON: @message', [
          '@message' => $message,
        ]);
      }
    }
  }

}
