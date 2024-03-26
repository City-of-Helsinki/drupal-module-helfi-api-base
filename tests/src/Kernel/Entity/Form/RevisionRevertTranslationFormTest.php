<?php

declare(strict_types=1);

namespace Drupal\Tests\helfi_api_base\Kernel\Entity\Form;

use Drupal\Core\Form\FormState;
use Drupal\helfi_api_base\Entity\Form\RevisionRevertTranslationForm;
use Drupal\remote_entity_test\Entity\RemoteEntityTest;
use Drupal\Tests\helfi_api_base\Kernel\ApiKernelTestBase;
use Symfony\Component\HttpFoundation\Request;

/**
 * Tests Revision revert translation form.
 *
 * @group helfi_api_base
 */
class RevisionRevertTranslationFormTest extends ApiKernelTestBase {

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
   * Tests the revision form without langcode.
   */
  public function testBuildFormException() : void {
    $request = Request::createFromGlobals();
    $sut = RevisionRevertTranslationForm::create($this->container);
    $this->expectException(\LogicException::class);
    $sut->buildForm([], new FormState(), $this->rmt, $request);
  }

  /**
   * Tests revert form.
   */
  public function testBuildForm() : void {
    $request = Request::createFromGlobals();
    $request->attributes->set('langcode', 'en');
    $sut = RevisionRevertTranslationForm::create($this->container);
    $form = [];
    $formState = new FormState();
    $sut->buildForm($form, $formState, $this->rmt, $request);
    $this->assertNotEmpty($sut->getFormId());
    $this->assertNotEmpty($sut->getDescription());
    $this->assertStringStartsWith('Are you sure you want to revert English translation to the revision from ', (string) $sut->getQuestion());
    $sut->submitForm($form, $formState);
  }

}
