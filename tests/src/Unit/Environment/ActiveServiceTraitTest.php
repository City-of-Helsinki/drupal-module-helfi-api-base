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
   * Tests the service addresses of the active environment.
   *
   * @param string|null $project
   *   The active project.
   * @param \Drupal\helfi_api_base\Environment\EnvironmentEnum|null $environment
   *   The active environment.
   * @param array<string, ?string> $expected
   *   The expected addresses, keyed by trait method name.
   */
  #[DataProvider('activeServiceData')]
  public function testActiveService(?string $project, ?EnvironmentEnum $environment, array $expected) : void {
    $sut = new class($this->getEnvironmentResolver($project, $environment)) {

      use ActiveServiceTrait;

      public function __construct(
        protected readonly EnvironmentResolverInterface $environmentResolver,
      ) {
      }

      /**
       * Exposes the given trait method.
       */
      public function call(string $method) : ?Address {
        return $this->{$method}();
      }

    };

    foreach ($expected as $method => $url) {
      $address = $sut->call($method);

      if ($url === NULL) {
        $this->assertNull($address, $method);
        continue;
      }
      $this->assertInstanceOf(Address::class, $address, $method);
      $this->assertSame($url, $address->getAddress(), $method);
    }
  }

  /**
   * Data provider for testActiveService().
   *
   * @return array<string, array{?string, ?\Drupal\helfi_api_base\Environment\EnvironmentEnum, array<string, ?string>}>
   *   The data.
   */
  public static function activeServiceData() : array {
    return [
      'prod environment' => [
        Project::ETUSIVU,
        EnvironmentEnum::Prod,
        [
          'getElastic' => 'https://elasticsearch-etusivu-managed-prod-es-default.hki-kanslia-helfi-etusivu-prod.svc.cluster.local:9200',
          'getPublicElasticProxy' => 'https://helfi-etusivu-elastic-proxy.api.hel.ninja',
        ],
      ],
      'local environment' => [
        Project::ETUSIVU,
        EnvironmentEnum::Local,
        [
          'getElastic' => 'http://helfi-etusivu-elastic:9200',
          'getPublicElasticProxy' => 'https://elastic-proxy-helfi-etusivu.docker.so',
        ],
      ],
      'project without services' => [
        Project::ASUMINEN,
        EnvironmentEnum::Prod,
        [
          'getElastic' => NULL,
          'getPublicElasticProxy' => NULL,
        ],
      ],
      'no active environment' => [
        NULL,
        NULL,
        [
          'getElastic' => NULL,
          'getPublicElasticProxy' => NULL,
        ],
      ],
    ];
  }

}
