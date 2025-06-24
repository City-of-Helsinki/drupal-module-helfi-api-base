<?php

declare(strict_types=1);

namespace Drupal\helfi_api_base\Drush\Commands;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityPublishedInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\helfi_api_base\Plugin\migrate\destination\TranslatableEntity;
use Drupal\migrate\MigrateExecutable;
use Drupal\migrate\MigrateMessage;
use Drupal\migrate\Plugin\MigrationPluginManagerInterface;
use Drush\Attributes\Argument;
use Drush\Attributes\Command;
use Drush\Attributes\Option;
use Drush\Commands\AutowireTrait;
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
  use AutowireTrait;

  /**
   * Constructs a new instance.
   *
   * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
   *   The service container.
   * @param \Drupal\migrate\Plugin\MigrationPluginManagerInterface $migrationPluginManager
   *   The migration plugin manager.
   */
  public function __construct(
    private readonly ContainerInterface $container,
    private readonly MigrationPluginManagerInterface $migrationPluginManager,
  ) {
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
   */
  #[Command(name: 'helfi:migrate-fixture')]
  #[Argument(name: 'migration', description: 'The migration name.')]
  #[Option(name: 'publish', description: 'Publish data.')]
  public function migrateFixtures(string $migration, array $options = ['publish' => FALSE]): void {
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

    $destinationPlugin = $instance->getDestinationPlugin();

    if ($destinationPlugin instanceof TranslatableEntity) {
      array_map(function (ContentEntityInterface $entity) use ($options) {
        if (!$options['publish'] || !$entity instanceof EntityPublishedInterface) {
          return;
        }
        foreach ($entity->getTranslationLanguages() as $language) {
          $entity->getTranslation($language->getId())
            ->setPublished()
            ->save();
        }
      }, $destinationPlugin->getStorage()->loadMultiple());
    }
  }

}
