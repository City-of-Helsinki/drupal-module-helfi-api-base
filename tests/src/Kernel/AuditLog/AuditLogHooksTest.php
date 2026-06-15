<?php

declare(strict_types=1);

namespace Drupal\Tests\helfi_api_base\Kernel\AuditLog;

use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\Core\Entity\EntityInterface;
use Drupal\KernelTests\KernelTestBase;
use Drupal\Tests\user\Traits\UserCreationTrait;
use Drupal\entity_test\Entity\EntityTest;
use Drupal\entity_test\Entity\EntityTestRev;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;

/**
 * Tests the entity audit log hooks.
 */
#[Group('helfi_api_base')]
#[RunTestsInSeparateProcesses]
class AuditLogHooksTest extends KernelTestBase {

  use UserCreationTrait;

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'system',
    'user',
    'entity_test',
    'helfi_api_base',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    $this->installSchema('helfi_api_base', ['helfi_audit_logs']);
    $this->installEntitySchema('user');
    $this->installEntitySchema('entity_test');
    $this->installEntitySchema('entity_test_rev');
  }

  /**
   * {@inheritdoc}
   */
  public function register(ContainerBuilder $container): void {
    parent::register($container);

    $container->setParameter('helfi_api_base.audit_log_entity_types', [
      // No 'operations' => default write operations only (no READ).
      ['entity_type' => 'user'],
      // Explicitly opts into READ in addition to the write operations.
      [
        'entity_type' => 'entity_test',
        'operations' => ['READ', 'CREATE', 'UPDATE', 'DELETE'],
      ],
    ]);
  }

  /**
   * Reads all audit log rows from the database.
   *
   * @return array<int, array<string, mixed>>
   *   The decoded audit events.
   */
  private function getAuditEvents(): array {
    $rows = $this->container->get('database')
      ->select('helfi_audit_logs', 'al')
      ->fields('al', ['message'])
      ->execute()
      ->fetchAll();

    return array_map(
      static fn ($row) => json_decode($row->message, TRUE)['audit_event'],
      $rows,
    );
  }

  /**
   * Renders an entity, triggering hook_entity_view.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The entity to view.
   */
  private function viewEntity(EntityInterface $entity): void {
    $build = $this->container->get('entity_type.manager')
      ->getViewBuilder($entity->getEntityTypeId())
      ->view($entity);
    $this->container->get('renderer')->renderInIsolation($build);
  }

  /**
   * Tests that a configured write operation is logged.
   */
  public function testConfiguredWriteOperationIsLogged(): void {
    // Creating a user triggers hook_entity_insert.
    $this->createUser([], 'test-user');

    $events = $this->getAuditEvents();
    $this->assertCount(1, $events);
    $this->assertEquals('CREATE', $events[0]['operation']);
    $this->assertEquals('USER', $events[0]['target']['type']);
  }

  /**
   * Tests that an unconfigured entity type is not logged.
   */
  public function testUnconfiguredEntityIsNotLogged(): void {
    EntityTestRev::create(['name' => 'test'])->save();

    $this->assertEmpty($this->getAuditEvents());
  }

  /**
   * Tests that READ is not logged for a type that did not opt into it.
   */
  public function testReadIsNotLoggedByDefault(): void {
    $user = $this->createUser([], 'viewed-user');
    // Clear the CREATE event from user creation.
    $this->container->get('database')->truncate('helfi_audit_logs')->execute();

    $this->viewEntity($user);

    $this->assertEmpty($this->getAuditEvents());
  }

  /**
   * Tests that READ is logged for a type that opted into it.
   */
  public function testReadIsLoggedWhenOptedIn(): void {
    $entity = EntityTest::create(['name' => 'test']);
    $entity->save();
    // Clear the CREATE event from saving.
    $this->container->get('database')->truncate('helfi_audit_logs')->execute();

    $this->viewEntity($entity);

    $events = $this->getAuditEvents();
    $this->assertCount(1, $events);
    $this->assertEquals('READ', $events[0]['operation']);
  }

}
