<?php

declare(strict_types=1);

namespace Drupal\Tests\helfi_api_base\Kernel\Entity;

use Drupal\Core\Render\RendererInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Tests\helfi_api_base\Kernel\ApiKernelTestBase;
use Drupal\helfi_api_base\Entity\RevisionController;
use Drupal\remote_entity_test\Entity\RemoteEntityTest;
use Prophecy\PhpUnit\ProphecyTrait;
use Symfony\Component\Routing\Route;

/**
 * Tests Revision controller.
 *
 * @group helfi_api_base
 */
class RevisionControllerTest extends ApiKernelTestBase {

  use ProphecyTrait;

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'remote_entity_test',
    'menu_link_content',
    'link',
    'system',
    'user',
  ];

  /**
   * The remote entity to test.
   *
   * @var \Drupal\remote_entity_test\Entity\RemoteEntityTest|null
   */
  private ?RemoteEntityTest $rmt = NULL;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    $this->installEntitySchema('remote_entity_test');
    $this->installEntitySchema('menu_link_content');
    $this->installConfig('system');
    $this->rmt = RemoteEntityTest::create([
      'id' => 1,
      'name' => 'Test 1',
    ]);
    $this->rmt->save();
  }

  /**
   * Tests controller.
   */
  public function testRevisionOverview() : void {
    // Mock renderer service, so we don't have to care about render context.
    $renderer = $this->prophesize(RendererInterface::class);
    $this->container->set('renderer', $renderer->reveal());

    $sut = RevisionController::create($this->container);
    $routeMatch = $this->prophesize(RouteMatchInterface::class);
    $route = $this->prophesize(Route::class);
    $route->getOption('entity_type_id')
      ->shouldBeCalled()
      ->willReturn('remote_entity_test');
    $routeMatch->getParameter('remote_entity_test')
      ->willReturn($this->rmt);
    $routeMatch->getRouteObject()
      ->shouldBeCalled()
      ->willReturn($route->reveal());
    $build = $sut->revisionOverviewController($routeMatch->reveal());
    $this->assertArrayHasKey('remote_entity_test_revisions_table', $build);
  }

}
