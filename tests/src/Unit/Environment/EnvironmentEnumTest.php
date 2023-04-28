<?php

declare(strict_types=1);

namespace Drupal\Tests\helfi_api_base\Unit\Environment;

use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\helfi_api_base\Environment\EnvironmentEnum;
use Drupal\Tests\UnitTestCase;

/**
 * Tests environment enum.
 *
 * @coversDefaultClass \Drupal\helfi_api_base\Environment\EnvironmentEnum
 * @group helfi_api_base
 */
class EnvironmentEnumTest extends UnitTestCase {

  /**
   * @covers ::label
   */
  public function testLabel() : void {
    foreach (EnvironmentEnum::cases() as $case) {
      $this->assertInstanceOf(TranslatableMarkup::class, $case->label());
    }
  }

}
