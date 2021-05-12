<?php

declare(strict_types = 1);

namespace Drupal\helfi_api_base\Commands;

use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\migrate\MigrateExecutable;
use Drupal\migrate\MigrateMessage;
use Drupal\migrate\Plugin\MigrationPluginManagerInterface;
use Drush\Commands\DrushCommands;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * A Drush commandfile.
 */
class FixtureCommands extends DrushCommands {

  use StringTranslationTrait;

  /**
   * The service container.
   *
   * @var \Symfony\Component\DependencyInjection\ContainerInterface
   */
  protected ContainerInterface $container;

  /**
   * The migration plugin manager.
   *
   * @var \Drupal\migrate\Plugin\MigrationPluginManagerInterface
   */
  protected MigrationPluginManagerInterface $migrationPluginManager;

  /**
   * Constructs a new instance.
   */
  public function __construct(ContainerInterface $container) {
    $this->container = $container;
    $this->migrationPluginManager = $container->get('plugin.manager.migration');
  }

  /**
   * Creates HTTP client stub.
   *
   * @param \Psr\Http\Message\ResponseInterface[] $responses
   *   The expected responses.
   *
   * @return \GuzzleHttp\Client
   *   The client.
   */
  private function createMockHttpClient(array $responses) : Client {
    $mock = new MockHandler($responses);
    $handlerStack = HandlerStack::create($mock);

    return new Client(['handler' => $handlerStack]);
  }

  /**
   * Command to run migrates with mocked fixture data.
   *
   * @param string $migration
   *   The migration name.
   *
   * @command helfi:migrate-fixture
   */
  public function migrateFixtures(string $migration) {
    $serviceName = sprintf('migration_fixture.%s', $migration);

    if (!$this->container->has($serviceName)) {
      throw new \InvalidArgumentException('Migration fixture service not found: ' . $serviceName);
    }
    /** @var \Drupal\helfi_api_base\Fixture\FixtureBase $service */
    $service = $this->container->get($serviceName);

    $this->container->set('http_client', $this->createMockHttpClient($service->getMockResponses()));
    /** @var \Drupal\migrate\Plugin\MigrationInterface $instance */
    $instance = $this->migrationPluginManager->createInstance($migration, $service->getConfiguration());

    (new MigrateExecutable($instance, new MigrateMessage()))->import();
  }

}
