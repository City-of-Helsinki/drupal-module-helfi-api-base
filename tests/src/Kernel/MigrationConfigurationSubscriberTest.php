<?php

declare(strict_types=1);

namespace Drupal\Tests\helfi_api_base\Kernel;

use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\helfi_api_base\Event\MigrationConfigurationEvent;
use Drupal\helfi_api_base\Plugin\migrate\source\HttpSourcePluginBase;
use Drupal\remote_entity_test\Plugin\migrate\source\DummySource;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Tests migration configuration subscriber.
 *
 * @group helfi_tpr
 */
class MigrationConfigurationSubscriberTest extends MigrationTestBase implements EventSubscriberInterface {

  /**
   * Track caught events in a property for testing.
   *
   * @var array
   */
  private array $caughtEvents = [];

  /**
   * {@inheritdoc}
   */
  protected static $modules = ['remote_entity_test'];

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() : array {
    return [
      MigrationConfigurationEvent::class => [
        ['catchEvents'],
      ],
    ];
  }

  /**
   * Catch events.
   *
   * @param \Drupal\helfi_api_base\Event\MigrationConfigurationEvent $event
   *   The event.
   */
  public function catchEvents(MigrationConfigurationEvent $event): void {
    $this->caughtEvents[] = $event;
  }

  /**
   * {@inheritdoc}
   */
  public function register(ContainerBuilder $container): void {
    parent::register($container);
    $container
      ->register('testing.migration_configuration_subscriber', self::class)
      ->addTag('event_subscriber');
    $container->set('testing.migration_configuration_subscriber', $this);
  }

  /**
   * Make sure event subscriber is run.
   */
  public function testEventsCaught(): void {
    self::assertCount(0, $this->caughtEvents);
    $migrate = $this->getMigration('dummy_migrate');
    $plugin = DummySource::create($this->container, ['url' => 'http://localhost'], '', [], $migrate);
    $this->assertInstanceOf(HttpSourcePluginBase::class, $plugin);

    self::assertCount(1, $this->caughtEvents);
  }

}
