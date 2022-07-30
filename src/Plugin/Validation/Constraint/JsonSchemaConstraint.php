<?php

declare(strict_types = 1);

namespace Drupal\helfi_api_base\Plugin\Validation\Constraint;

use Symfony\Component\Validator\Constraint;

/**
 * Checks that the submitted value validates against given json schema.
 *
 * @Constraint(
 *   id = "JsonSchema",
 *   label = @Translation("Json Schema", context = "Validation"),
 *   type = {"json", "string"}
 * )
 */
class JsonSchemaConstraint extends Constraint {

  /**
   * Path to json schema.
   *
   * Example: file://path/to/module/schema.json.
   *
   * @var string
   */
  public string $schema;

  /**
   * {@inheritdoc}
   */
  public function getRequiredOptions() : array {
    return ['schema'];
  }

}
