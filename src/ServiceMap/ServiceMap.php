<?php

declare(strict_types=1);

namespace Drupal\helfi_api_base\ServiceMap;

use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\Utility\Error;
use Drupal\helfi_api_base\ServiceMap\DTO\Address;
use Drupal\helfi_api_base\ServiceMap\DTO\Location;
use Drupal\helfi_api_base\ServiceMap\DTO\StreetName;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\GuzzleException;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

/**
 * Class for interacting with Servicemap API.
 */
final class ServiceMap implements ServiceMapInterface {

  use StringTranslationTrait;

  /**
   * API URL for querying data.
   *
   * @var string
   */
  private const API_URL = 'https://api.hel.fi/servicemap/v2/search/';

  /**
   * Constructs a new instance.
   *
   * @param \GuzzleHttp\ClientInterface $client
   *   The HTTP client.
   * @param \Drupal\Core\Language\LanguageManagerInterface $languageManager
   *   Language manager.
   * @param \Psr\Log\LoggerInterface $logger
   *   Logger.
   */
  public function __construct(
    private readonly ClientInterface $client,
    private readonly LanguageManagerInterface $languageManager,
    #[Autowire(service: 'logger.channel.helfi_api_base')]
    private readonly LoggerInterface $logger,
  ) {
  }

  /**
   * {@inheritdoc}
   */
  public function getAddressData(string $address) : ?Address {
    $results = $this->query($address);

    if ($item = reset($results)) {
      return $item;
    }

    return NULL;
  }

  /**
   * Sanitizes the given address.
   *
   * @param string $address
   *   The address.
   *
   * @return string|null
   *   The sanitized address.
   */
  public function sanitizeAddress(string $address) : ?string {
    // ServiceMap API only allows letters, numbers, spaces and .,'+-&|.
    // \p{L} - Unicode letters (a-z, A-Z, ä, ö, å, etc.)
    // \p{N} - Unicode numbers (0-9).
    return preg_replace("/[^\p{L}\p{N} .,'+\-&|]/u", '', $address);
  }

  /**
   * {@inheritdoc}
   */
  public function query(string $address, int $page_size = 1) : array {
    $address = $this->sanitizeAddress($address);

    try {
      $response = $this->client->request('GET', self::API_URL, [
        'query' => [
          'format' => 'json',
          'municipality' => 'helsinki',
          'page_size' => $page_size,
          'q' => $address,
          'type' => 'address',
          'language' => $this->languageManager->getCurrentLanguage()->getId(),
        ],
      ]);
    }
    catch (GuzzleException $e) {
      Error::logException($this->logger, $e);

      return [];
    }

    $result = json_decode($response->getBody()->getContents(), TRUE);

    if (empty($result['results'])) {
      return [];
    }

    return array_map(function (array $result) : Address {
      return new Address(
        StreetName::createFromArray($result['name']),
        Location::createFromArray($result['location']),
      );
    }, $result['results']);
  }

}
