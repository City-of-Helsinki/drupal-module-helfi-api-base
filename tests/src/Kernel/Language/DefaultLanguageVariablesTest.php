<?php

declare(strict_types=1);

namespace Drupal\Tests\helfi_api_base\Kernel\Language;

use Drupal\KernelTests\KernelTestBase;

/**
 * Tests helfi_api_base_template_preprocess_default_variables_alter().
 *
 * @group helfi_api_base
 */
class DefaultLanguageVariablesTest extends KernelTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'helfi_api_base',
  ];

  /**
   * Test helfi_api_base_template_preprocess_default_variables_alter().
   */
  public function testDefaultLanguageVariables(): void {
    // Prepare variables for the page template.
    $render_array = [
      '#theme' => 'page',
      '#content' => ['#markup' => 'Test content'],
    ];
    \Drupal::moduleHandler()->alter('template_preprocess_default_variables', $render_array);
    \Drupal::moduleHandler()->invokeAll('preprocess_page', [&$render_array]);

    // Check if variables are set correctly.
    $this->assertInstanceOf('Drupal\Core\Language\Language', $render_array['language']);
    $this->assertNotNull($render_array['alternative_language']);
  }

}
