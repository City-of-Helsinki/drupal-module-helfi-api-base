<?php

declare(strict_types = 1);

namespace Drupal\Tests\helfi_api_base\Unit\Azure\PubSub;

use Drupal\helfi_api_base\Azure\PubSub\Settings;
use Drupal\helfi_api_base\Azure\PubSub\SettingsFactory;
use Drupal\Tests\UnitTestCase;

/**
 * @coversDefaultClass \Drupal\helfi_api_base\Azure\PubSub\SettingsFactory
 * @group helfi_api_base
 */
class SettingsTest extends UnitTestCase {

  /**
   * @covers \Drupal\helfi_api_base\Azure\PubSub\Settings::__construct
   * @covers ::create
   * @covers ::__construct
   * @dataProvider settingsData
   */
  public function testSettings(array $values, array $expectedValues) : void {
    $configFactory = $this->getConfigFactoryStub([
      'helfi_api_base.pubsub.settings' => $values,
    ]);
    $sut = new SettingsFactory($configFactory);
    $settings = $sut->create();
    $this->assertInstanceOf(Settings::class, $settings);
    $this->assertSame($settings->hub, $expectedValues['hub']);
    $this->assertSame($settings->group, $expectedValues['group']);
    $this->assertSame($settings->endpoint, $expectedValues['endpoint']);
    $this->assertSame($settings->accessToken, $expectedValues['access_token']);
  }

  /**
   * A data provider.
   *
   * @return array[]
   *   The data.
   */
  public function settingsData() : array {
    $values = [
      [
        [
          'hub' => 'hub',
          'group' => 'group',
          'endpoint' => 'endpoint',
          'access_token' => 'access_token',
        ],
        [
          'hub' => 'hub',
          'group' => 'group',
          'endpoint' => 'endpoint',
          'access_token' => 'access_token',
        ],
      ],
    ];
    // Make sure invalid values fallback to empty string.
    foreach ([FALSE, NULL, ''] as $value) {
      $values[] = [
        [
          'hub' => $value,
          'group' => $value,
          'endpoint' => $value,
          'access_token' => $value,
        ],
        [
          'hub' => '',
          'group' => '',
          'endpoint' => '',
          'access_token' => '',
        ],
      ];
    }
    return $values;
  }

}
