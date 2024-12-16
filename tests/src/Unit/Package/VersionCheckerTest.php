<?php

declare(strict_types=1);

namespace Drupal\Tests\helfi_api_base\Unit\Package;

use Drupal\helfi_api_base\Exception\VersionCheckException;
use Drupal\helfi_api_base\Package\ComposerOutdatedProcess;
use Drupal\helfi_api_base\Package\Version;
use Drupal\helfi_api_base\Package\VersionChecker;
use Drupal\Tests\UnitTestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Symfony\Component\Process\Exception\ProcessFailedException;

/**
 * Tests Version checker.
 *
 * @group helfi_api_base
 */
class VersionCheckerTest extends UnitTestCase {

  use ProphecyTrait;

  /**
   * Tests missing composer file.
   */
  public function testMissingComposerFile(): void {
    $process = $this->prophesize(ComposerOutdatedProcess::class);
    $process->run(Argument::any())->willReturn([]);
    $sut = new VersionChecker('nonexistent.lock', $process->reveal());

    $this->expectException(VersionCheckException::class);
    $sut->getOutdated();
  }

  /**
   * Tests composer command failure.
   */
  public function testProcessFailure(): void {
    $process = $this->prophesize(ComposerOutdatedProcess::class);
    $process
      ->run(Argument::any())
      ->shouldBeCalled()
      ->willThrow($this->prophesize(ProcessFailedException::class)->reveal());

    $sut = new VersionChecker(__DIR__ . '/../../../fixtures/composer.lock', $process->reveal());

    $this->expectException(VersionCheckException::class);
    $sut->getOutdated();
  }

  /**
   * Tests getOutdated().
   */
  public function testGetOutdated(): void {
    $process = $this->prophesize(ComposerOutdatedProcess::class);
    $process
      ->run(Argument::any())
      ->shouldBeCalled()
      ->willReturn([
        'installed' => [
          [
            'name' => 'drupal/helfi_api_base',
            'version' => '1.0.18',
            'latest' => '1.1.0',
          ],
        ],
      ]);

    $sut = new VersionChecker(__DIR__ . '/../../../fixtures/composer.lock', $process->reveal());

    $outdated = $sut->getOutdated();

    $this->assertNotEmpty($outdated);
    $outdated = reset($outdated);
    $this->assertInstanceOf(Version::class, $outdated);
    $this->assertEquals('drupal/helfi_api_base', $outdated->name);
    $this->assertEquals('1.0.18', $outdated->version);
    $this->assertEquals('1.1.0', $outdated->latestVersion);
    $this->assertFalse($outdated->isLatest);

  }

}
