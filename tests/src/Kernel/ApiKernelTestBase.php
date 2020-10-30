<?php

declare(strict_types = 1);

namespace Drupal\Tests\helfi_api_base\Kernel;

use Drupal\KernelTests\Core\Entity\EntityKernelTestBase;

/**
 * API test base.
 */
abstract class ApiKernelTestBase extends EntityKernelTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'api_tools',
    'helfi_api_base',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();

    $this->installConfig(['helfi_api_base']);
  }

  /**
   * Gets the fixture path.
   *
   * @param string $module
   *   The module.
   * @param string $name
   *   The name.
   *
   * @return string
   *   The fixture path.
   */
  protected function getFixturePath(string $module, string $name) : string {
    $file = sprintf('%s/tests/fixtures/%s', drupal_get_path('module', $module), $name);

    if (!file_exists($file)) {
      throw new \InvalidArgumentException(sprintf('Fixture %s not found', $name));
    }

    return $file;
  }

  /**
   * Gets the fixture data.
   *
   * @param string $module
   *   The module.
   * @param string $name
   *   The fixture name.
   *
   * @return string
   *   The fixture.
   */
  protected function getFixture(string $module, string $name) : string {
    return file_get_contents($this->getFixturePath($module, $name));
  }

}
