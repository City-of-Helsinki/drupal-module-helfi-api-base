<?php

declare(strict_types=1);

namespace Drupal\helfi_api_base\Plugin\ElasticSearch\Connector;

use Drupal\elasticsearch_connector\Plugin\ElasticSearch\Connector\BasicAuthConnector;
use Elastic\Elasticsearch\Client;
use Elastic\Elasticsearch\ClientBuilder;

/**
 * Provides an ElasticSearch connector that accepts self-signed certificates.
 *
 * @ElasticSearchConnector(
 *   id = "helfi_connector",
 *   label = @Translation("Helfi Connector"),
 *   description = @Translation("ElasticSearch connector with HTTP Basic Auth that accepts self signed certificates.")
 * )
 */
class HelfiConnector extends BasicAuthConnector {

  /**
   * {@inheritdoc}
   */
  public function getClient(): Client {
    return ClientBuilder::create()
      ->setHosts([$this->configuration['url']])
      ->setBasicAuthentication($this->configuration['username'], $this->configuration['password'])
      ->setSSLVerification(FALSE)
      ->build();
  }

}
