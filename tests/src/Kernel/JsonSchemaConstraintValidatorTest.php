<?php

declare(strict_types=1);

namespace Drupal\Tests\helfi_api_base\Kernel;

use Drupal\Core\TypedData\DataDefinition;
use Drupal\KernelTests\KernelTestBase;
use Symfony\Component\Validator\ConstraintViolationListInterface;

/**
 * Tests JsonSchemaConstraintValidator.
 *
 * @coversDefaultClass \Drupal\helfi_api_base\Plugin\Validation\Constraint\JsonSchemaConstraintValidator
 * @group helfi_api_base
 */
class JsonSchemaConstraintValidatorTest extends KernelTestBase {

  /**
   * The typed data manager to use.
   *
   * @var \Drupal\Core\TypedData\TypedDataManager
   */
  protected $typedData;

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'helfi_api_base',
    'field',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    $this->typedData = $this->container->get('typed_data_manager');
  }

  /**
   * Asserts given value against the JSON schema.
   *
   * @param mixed $value
   *   The value to validate.
   * @param string|null $schema
   *   The schema.
   *
   * @return \Symfony\Component\Validator\ConstraintViolationListInterface
   *   The violations.
   */
  private function assertViolations(mixed $value, ?string $schema = NULL) : ConstraintViolationListInterface {
    if (!$schema) {
      $schema = 'file://' . realpath(__DIR__ . '/../../fixtures/schema.json');
    }
    $definition = DataDefinition::create('string')
      ->addConstraint('JsonSchema', [
        'schema' => $schema,
      ]);
    $typed_data = $this->typedData->create($definition, $value);
    return $typed_data->validate();
  }

  /**
   * Tests invalid json schema.
   */
  public function testInvalidJsonSchema() : void {
    $violations = $this->assertViolations((object) ['value' => 'test'], 'file://nonexistent.json');
    $this->assertEquals('Failed to load JSON schema: "file://nonexistent.json"', $violations[0]->getMessage());
  }

  /**
   * Tests invalid json.
   */
  public function testInvalidJson() : void {
    $violations = $this->assertViolations('-');
    $this->assertEquals('Failed to parse JSON: Syntax error', $violations[0]->getMessage());
  }

  /**
   * Tests the schema validation.
   */
  public function testValidation() : void {
    $violations = $this->assertViolations('{}');
    $this->assertStringStartsWith('Failed to validate JSON:', (string) $violations[0]->getMessage());

    $violations = $this->assertViolations(json_encode([
      'id' => 1,
    ]));
    $this->assertEquals(0, $violations->count());
  }

}
