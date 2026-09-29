<?php

declare(strict_types=1);

namespace Drupal\Tests\helfi_api_base\Unit\Environment;

use Drupal\helfi_api_base\Environment\ActiveServiceTrait;
use Drupal\helfi_api_base\Environment\Address;
use Drupal\helfi_api_base\Environment\EnvironmentEnum;
use Drupal\helfi_api_base\Environment\EnvironmentResolverInterface;
use Drupal\helfi_api_base\Environment\Project;
use Drupal\Tests\helfi_api_base\Traits\EnvironmentResolverTrait;
use Drupal\Tests\UnitTestCase;
use PHPUnit\Framework\Attributes\CoversTrait;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;

/**
 * Tests the active service trait.
 */
#[CoversTrait(ActiveServiceTrait::class)]
#[Group('helfi_api_base')]
final class ActiveServiceTraitTest extends UnitTestCase {

  use EnvironmentResolverTrait;

  /**
   * Tests the Elastic proxy address of the active environment.
   */
  #[DataProvider('elasticProxyUrlData')]
  public function testGetElasticProxyUrl(?string $project, ?EnvironmentEnum $environment, ?string $expected) : void {
    $sut = new class($this->getEnvironmentResolver($project, $environment)) {

      use ActiveServiceTrait;

      public function __construct(
        protected readonly EnvironmentResolverInterface $environmentResolver,
      ) {
      }

      /**
       * Exposes the trait method.
       */
      public function publicElasticProxy() : ?Address {
        return $this->getPublicElasticProxy();
      }

    };
    $address = $sut->publicElasticProxy();

    if ($expected === NULL) {
      $this->assertNull($address);
      return;
    }
    $this->assertInstanceOf(Address::class, $address);
    $this->assertSame($expected, $address->getAddress());
  }

  /**
   * Data provider for testGetElasticProxyUrl().
   *
   * @return array<string, array{?string, ?\Drupal\helfi_api_base\Environment\EnvironmentEnum, ?string}>
   *   The data.
   */
  public static function elasticProxyUrlData() : array {
    return [
      'active environment' => [
        Project::ETUSIVU,
        EnvironmentEnum::Prod,
        'https://helfi-etusivu-elastic-proxy.api.hel.ninja',
      ],
      'project without proxy' => [Project::ASUMINEN, EnvironmentEnum::Prod, NULL],
      'no active environment' => [NULL, NULL, NULL],
    ];
  }

}
