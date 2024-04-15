<?php

declare(strict_types=1);

namespace Drupal\Tests\helfi_api_base\Kernel\Features;

use Drupal\helfi_api_base\Features\FeatureManager;
use Drupal\helfi_api_base\Features\FeatureManagerInterface;
use Drupal\KernelTests\KernelTestBase;

/**
 * Tests feature toggle.
 *
 * @group helfi_api_base
 */
class FeatureToggleTest extends KernelTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'system',
    'user',
    'migrate',
    'helfi_api_base',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    $this->installEntitySchema('user');
    $this->installEntitySchema('action');
    $this->installConfig('helfi_api_base');
  }

  /**
   * Gets the SUT.
   *
   * @return \Drupal\helfi_api_base\Features\FeatureManager
   *   The sut.
   */
  public function getSut() : FeatureManager {
    return $this->container->get(FeatureManagerInterface::class);
  }

  /**
   * Make sure we cannot check non-existent features.
   */
  public function testNonExistentIsEnabled() : void {
    $this->expectException(\InvalidArgumentException::class);
    $this->expectExceptionMessage('Invalid feature: "non-existent".');
    $this->getSut()->isEnabled('non-existent');
  }

  /**
   * Make sure we cannot enable non-existent features.
   */
  public function testNonExistentEnableFeature() : void {
    $this->expectException(\InvalidArgumentException::class);
    $this->expectExceptionMessage('Invalid feature: "non-existent".');
    $this->getSut()->enableFeature('non-existent');
  }

  /**
   * Make sure we cannot disable non-existent features.
   */
  public function testNonExistentDisableFeature() : void {
    $this->expectException(\InvalidArgumentException::class);
    $this->expectExceptionMessage('Invalid feature: "non-existent".');
    $this->getSut()->disableFeature('non-existent');
  }

  /**
   * Tests default features.
   */
  public function testDefaults() : void {
    $features = $this->getSut()->getFeatures();

    $this->assertEquals([
      FeatureManagerInterface::ROTATE_UID1_PASSWORD => TRUE,
      FeatureManagerInterface::LOGGER => TRUE,
    ], $features);
  }

}
