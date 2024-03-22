<?php

declare(strict_types=1);

namespace Drupal\Tests\helfi_api_base\Kernel\Entity\Form;

use Drupal\remote_entity_test\Entity\RemoteEntityTest;
use Drupal\Tests\helfi_api_base\Kernel\ApiKernelTestBase;

/**
 * Tests menu link form.
 *
 * @todo Improve these tests.
 *
 * @group helfi_api_base
 */
class MenuLinkFormTest extends ApiKernelTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'remote_entity_test',
    'menu_link_content',
    'link',
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
    $this->installEntitySchema('user');
    $this->rmt = RemoteEntityTest::create([
      'id' => 1,
      'title' => 'Test 1',
    ]);
    $this->rmt->save();
  }

  /**
   * Tests menu form access.
   */
  public function testMenuLinkFormAccess() : void {
    /** @var \Drupal\Core\Entity\EntityFormBuilderInterface $formBuilder */
    $formBuilder = $this->container->get('entity.form_builder');
    $form = $formBuilder->getForm($this->rmt, 'default');

    // Make sure a user without permission has no access.
    $this->assertFalse($form['menu']['#access']);

    $this->drupalSetUpCurrentUser(permissions: ['administer menu']);
    $form = $formBuilder->getForm($this->rmt, 'default');
    // Make sure a user with permission has access.
    $this->assertTrue($form['menu']['#access']);
  }

}
