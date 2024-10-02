<?php

declare(strict_types=1);

namespace Drupal\Tests\helfi_api_base\Unit\Environment;

use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\Tests\UnitTestCase;
use Drupal\helfi_api_base\Environment\EnvironmentEnum;

/**
 * Tests environment enum.
 *
 * @group helfi_api_base
 */
class EnvironmentEnumTest extends UnitTestCase {

  /**
   * Tests label() method.
   */
  public function testLabel() : void {
    $found = 0;

    foreach (EnvironmentEnum::cases() as $case) {
      $found++;
      $this->assertInstanceOf(TranslatableMarkup::class, $case->label());
    }
    $this->assertTrue($found > 0);
  }

}
