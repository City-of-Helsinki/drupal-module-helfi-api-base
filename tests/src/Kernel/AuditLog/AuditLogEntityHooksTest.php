<?php

declare(strict_types=1);

namespace Drupal\Tests\helfi_api_base\Kernel\AuditLog;

use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\Core\DestructableInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\KernelTests\KernelTestBase;
use Drupal\Tests\user\Traits\UserCreationTrait;
use Drupal\entity_test\Entity\EntityTest;
use Drupal\entity_test\Entity\EntityTestMul;
use Drupal\entity_test\Entity\EntityTestRev;
use Drupal\helfi_api_base\AuditLog\AuditLogServiceInterface;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;

/**
 * Tests the entity audit log hooks.
 */
#[Group('helfi_api_base')]
#[RunTestsInSeparateProcesses]
class AuditLogEntityHooksTest extends KernelTestBase {

  use UserCreationTrait;

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'system',
    'user',
    'entity_test',
    'diff',
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
    $this->installEntitySchema('entity_test_mul');
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
        'operations' => ['ENTITY_READ', 'ENTITY_CREATE', 'ENTITY_UPDATE', 'ENTITY_DELETE'],
      ],
      // No 'operations' => default write operations only, used to test
      // that updates include a content diff.
      ['entity_type' => 'entity_test_rev'],
    ]);
  }

  /**
   * Flushes the queued audit log events to the database.
   *
   * AuditLogService only queues events until it is destructed (normally by
   * DrupalKernel::terminate() at the end of the request), so tests must
   * flush the queue manually before reading the database.
   */
  private function flushAuditLog(): void {
    $service = $this->container->get(AuditLogServiceInterface::class);
    $this->assertInstanceOf(DestructableInterface::class, $service);
    $service->destruct();
  }

  /**
   * Reads all audit log rows from the database.
   *
   * @return array<int, array<string, mixed>>
   *   The decoded audit events.
   */
  private function getAuditEvents(): array {
    $this->flushAuditLog();

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
    $this->assertEquals('ENTITY_CREATE', $events[0]['operation']);
    $this->assertEquals('USER', $events[0]['target']['type']);
  }

  /**
   * Tests that an unconfigured entity type is not logged.
   */
  public function testUnconfiguredEntityIsNotLogged(): void {
    EntityTestMul::create(['name' => 'test'])->save();

    $this->assertEmpty($this->getAuditEvents());
  }

  /**
   * Tests that READ is not logged for a type that did not opt into it.
   */
  public function testReadIsNotLoggedByDefault(): void {
    $user = $this->createUser([], 'viewed-user');
    $this->assertInstanceOf(EntityInterface::class, $user);
    // Clear the CREATE event from user creation.
    $this->flushAuditLog();
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
    $this->flushAuditLog();
    $this->container->get('database')->truncate('helfi_audit_logs')->execute();

    $this->viewEntity($entity);

    $events = $this->getAuditEvents();
    $this->assertCount(1, $events);
    $this->assertEquals('ENTITY_READ', $events[0]['operation']);
  }

  /**
   * Tests that updating a revisionable entity attaches a content diff.
   */
  public function testUpdateOperationIncludesContentDiff(): void {
    // The diff comparison reads entity field values through field access
    // checks, which entity_test's access handler gates behind this
    // permission.
    $this->setUpCurrentUser([], ['view test entity']);

    $entity = EntityTestRev::create(['name' => 'before']);
    $entity->save();
    // Clear the CREATE event from saving.
    $this->flushAuditLog();
    $this->container->get('database')->truncate('helfi_audit_logs')->execute();

    // Deliberately does not force a new revision: getEntityDiff() must
    // compare against the in-memory original entity, since the update's
    // revision ID is otherwise identical to the current one.
    $entity = EntityTestRev::load($entity->id());
    $entity->set('name', 'after')->save();

    $events = $this->getAuditEvents();
    $this->assertCount(1, $events);
    $this->assertEquals('ENTITY_UPDATE', $events[0]['operation']);
    $this->assertArrayHasKey('extra', $events[0]);
    $this->assertArrayHasKey('ContentDiff', $events[0]['extra']);
    $this->assertNotEmpty($events[0]['extra']['ContentDiff']);
    $nameDiff = $events[0]['extra']['ContentDiff'][$entity->id() . ':entity_test_rev.name'];
    $this->assertStringContainsString('before', $nameDiff);
    $this->assertStringContainsString('after', $nameDiff);
  }

}
