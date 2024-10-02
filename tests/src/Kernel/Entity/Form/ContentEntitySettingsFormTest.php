<?php

declare(strict_types=1);

namespace Drupal\Tests\helfi_api_base\Kernel\Entity\Form;

use Drupal\helfi_api_base\Entity\Form\ContentEntitySettingsForm;
use Drupal\remote_entity_test\Entity\RemoteEntityTest;
use Drupal\Tests\helfi_api_base\Kernel\ApiKernelTestBase;

/**
 * Tests Content entity settings form.
 *
 * @group helfi_api_base
 */
class ContentEntitySettingsFormTest extends ApiKernelTestBase {

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
    $this->rmt = RemoteEntityTest::create([
      'id' => 1,
      'name' => 'Test 1',
    ]);
    $this->rmt->save();
  }

  /**
   * Tests menu form access.
   */
  public function testContentEntitySettingsForm() : void {
    /** @var \Drupal\Core\Form\FormBuilderInterface $formBuilder */
    $formBuilder = $this->container->get('form_builder');
    $form = $formBuilder->getForm(ContentEntitySettingsForm::class);
    $this->assertArrayHasKey('settings', $form);
  }

}
