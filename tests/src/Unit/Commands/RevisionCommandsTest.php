<?php

declare(strict_types = 1);

namespace Drupal\Tests\helfi_api_base\Unit\Commands;

use Drupal\Core\Database\Connection;
use Drupal\Core\Database\Query\Select;
use Drupal\Core\Database\StatementInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\helfi_api_base\Commands\RevisionCommands;
use Drupal\helfi_api_base\Entity\Revision\RevisionManager;
use Drupal\Tests\UnitTestCase;
use Drush\Commands\DrushCommands;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Tests revision commands.
 *
 * @group helfi_api_base
 */
class RevisionCommandsTest extends UnitTestCase {

  use ProphecyTrait;

  /**
   * Gets the SUT.
   *
   * @param \Drupal\helfi_api_base\Entity\Revision\RevisionManager $revisionManager
   *   The revision manager.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface|null $entityTypeManager
   *   The entity type manager.
   * @param \Drupal\Core\Database\Connection|null $connection
   *   The connection.
   * @param \Prophecy\Prophecy\ObjectProphecy|null $io
   *   The IO prophecy.
   *
   * @return \Drupal\helfi_api_base\Commands\RevisionCommands
   *   The SUT.
   */
  private function getSut(
    RevisionManager $revisionManager,
    EntityTypeManagerInterface $entityTypeManager = NULL,
    Connection $connection = NULL,
    ObjectProphecy $io = NULL,
  ) : RevisionCommands {
    if (!$entityTypeManager) {
      $definition = $this->prophesize(EntityTypeInterface::class);
      $definition->getBaseTable()
        ->willReturn('base_table');
      $definition->getKey('id')
        ->willReturn('id');
      $entityTypeManager = $this->prophesize(EntityTypeManagerInterface::class);
      $entityTypeManager->getDefinition('node')
        ->willReturn($definition->reveal());
      $entityTypeManager = $entityTypeManager->reveal();
    }
    if (!$connection) {
      $connection = $this->prophesize(Connection::class)->reveal();
    }
    $sut = new RevisionCommands($revisionManager, $entityTypeManager, $connection);

    if ($io) {
      $output = $this->prophesize(OutputInterface::class);
      $input = $this->prophesize(InputInterface::class);
      $sut->restoreState($input->reveal(), $output->reveal(), $io->reveal());
    }
    return $sut;
  }

  /**
   * Mocks the connection object.
   *
   * @param array $expected
   *   The expected return value.
   *
   * @return \Drupal\Core\Database\Connection
   *   The connection mock.
   */
  private function getConnectionMock(array $expected) : Connection {
    $statement = $this->prophesize(StatementInterface::class);

    $statement->fetchCol(Argument::any())
      ->willReturn($expected);

    $select = $this->prophesize(Select::class);
    $select->fields('t', Argument::any())
      ->willReturn($select->reveal());

    $select->execute()
      ->willReturn($statement);

    $database = $this->prophesize(Connection::class);
    $database->select(Argument::any(), 't')
      ->willReturn($select);

    return $database->reveal();
  }

  /**
   * Test command with invalid entity type.
   */
  public function testInvalidEntityType() : void {
    $io = $this->prophesize(SymfonyStyle::class);
    $io->writeln(Argument::containingString('Given entity type is not supported.'))
      ->shouldBeCalled();

    $revisionManager = $this->prophesize(RevisionManager::class);
    $revisionManager->entityTypeIsSupported('node')->willReturn(FALSE);

    $sut = $this->getSut($revisionManager->reveal(), io: $io);

    $this->assertEquals(DrushCommands::EXIT_SUCCESS, $sut->delete('node'));
  }

  /**
   * Test delete without any entities.
   */
  public function testNoEntities() : void {
    $io = $this->prophesize(SymfonyStyle::class);
    $io->writeln(Argument::containingString('Found 0 node entities'))
      ->shouldBeCalled();

    $revisionManager = $this->prophesize(RevisionManager::class);
    $revisionManager->entityTypeIsSupported('node')->willReturn(TRUE);
    $database = $this->getConnectionMock([]);

    $sut = $this->getSut($revisionManager->reveal(), connection: $database, io: $io);

    $this->assertEquals(DrushCommands::EXIT_SUCCESS, $sut->delete('node'));
  }

  /**
   * Tests delete method with proper data.
   */
  public function testDelete() : void {
    $io = $this->prophesize(SymfonyStyle::class);
    $io->writeln(Argument::containingString('Found 2 node entities'))
      ->shouldBeCalled();
    $io->writeln(Argument::containingString('[2/2] Entity has less than 5 revisions. Skipping ...'))
      ->shouldBeCalled();
    $io->writeln(Argument::containingString('[1/2] Deleting 6 revisions ...'))
      ->shouldBeCalled();

    $revisionManager = $this->prophesize(RevisionManager::class);
    $revisionManager->entityTypeIsSupported('node')->willReturn(TRUE);
    $revisionManager->getRevisions('node', Argument::any(), Argument::any())
      ->willReturn([], [1, 2, 3, 4, 5, 6]);
    $revisionManager->deleteRevisions('node', Argument::any())
      ->shouldBeCalledTimes(2);
    $database = $this->getConnectionMock([1, 2]);

    $sut = $this->getSut($revisionManager->reveal(), connection: $database, io: $io);

    $this->assertEquals(DrushCommands::EXIT_SUCCESS, $sut->delete('node'));
  }

}
