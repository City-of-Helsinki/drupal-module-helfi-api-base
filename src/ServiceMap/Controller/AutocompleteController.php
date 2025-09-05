<?php

declare(strict_types=1);

namespace Drupal\helfi_api_base\ServiceMap\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\DependencyInjection\AutowireTrait;
use Drupal\helfi_api_base\ServiceMap\ServiceMapInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Returns autocomplete results.
 */
final class AutocompleteController extends ControllerBase {

  use AutowireTrait;

  public function __construct(private readonly ServiceMapInterface $serviceMap) {
  }

  /**
   * Serves autocomplete suggestions.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   The request.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   The result as JSON.
   */
  public function addressSuggestions(Request $request) : JsonResponse {
    $q = $request->query->get('q');
    $suggestions = [];

    $results = $this->serviceMap->query($q, 10);

    foreach ($results as $result) {
      $name = $result->streetName->getName($this->languageManager()->getCurrentLanguage()->getId());

      $suggestions[] = [
        'label' => $name,
        'value' => $name,
      ];
    }

    return new JsonResponse($suggestions);
  }

}
