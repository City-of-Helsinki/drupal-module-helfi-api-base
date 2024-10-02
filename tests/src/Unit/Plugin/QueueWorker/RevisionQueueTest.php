<?php

declare(strict_types=1);

namespace Drupal\Tests\helfi_api_base\Unit;

use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\Tests\UnitTestCase;
use Drupal\helfi_api_base\Entity\Revision\RevisionManager;
use Drupal\helfi_api_base\Plugin\QueueWorker\RevisionQueue;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;

/**
 * Tests revision queue.
 *
 * @coversDefaultClass \Drupal\helfi_api_base\Plugin\QueueWorker\RevisionQueue
 * @group helfi_api_base
 */
class RevisionQueueTest extends UnitTestCase {

  use ProphecyTrait;

  /**
   * Gets the SUT.
   *
   * @param \Drupal\helfi_api_base\Entity\Revision\RevisionManager $revisionManager
   *   The revision manager.
   *
   * @return \Drupal\helfi_api_base\Plugin\QueueWorker\RevisionQueue
   *   The revision queue.
   */
  private function getSut(RevisionManager $revisionManager) : RevisionQueue {
    $container = new ContainerBuilder();
    $container->set('helfi_api_base.revision_manager', $revisionManager);
    return RevisionQueue::create($container, [], '', []);
  }

  /**
   * Tests that nothing is done for invalid items.
   *
   * @covers ::create
   * @covers ::processItem
   */
  public function testQueueInvalidItem() : void {
    $revisionManager = $this->prophesize(RevisionManager::class);
    $revisionManager->getRevisions(Argument::any(), Argument::any())
      ->shouldNotBeCalled();

    $this->getSut($revisionManager->reveal())->processItem([]);
  }

  /**
   * Make sure nothing is done when an entity does not exist.
   *
   * @covers ::create
   * @covers ::processItem
   */
  public function testNoRevisions() : void {
    $revisionManager = $this->prophesize(RevisionManager::class);
    $revisionManager->deleteRevisions(Argument::any(), Argument::any())
      ->shouldNotBeCalled();
    $revisionManager->getRevisions('node', 1)
      ->shouldBeCalled()
      ->willReturn([]);

    $this->getSut($revisionManager->reveal())->processItem([
      'entity_type' => 'node',
      'entity_id' => 1,
    ]);
  }

  /**
   * Make sure revisions are processed.
   *
   * @covers ::create
   * @covers ::processItem
   */
  public function testDelete() : void {
    $revisionManager = $this->prophesize(RevisionManager::class);
    $revisionManager->deleteRevisions('node', [1, 2, 3])
      ->shouldBeCalled();
    $revisionManager->getRevisions('node', 1)
      ->shouldBeCalled()
      ->willReturn([1, 2, 3]);

    $this->getSut($revisionManager->reveal())->processItem([
      'entity_type' => 'node',
      'entity_id' => 1,
    ]);
  }

}
