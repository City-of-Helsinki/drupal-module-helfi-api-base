<?php

declare(strict_types=1);

namespace Drupal\helfi_api_base;

use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\Core\DependencyInjection\ServiceProviderBase;
use Drupal\monolog\Logger\Formatter\ConditionalFormatter;
use Drupal\monolog\Logger\Handler\ConditionalHandler;
use Drupal\monolog\Logger\Handler\DrupalHandler;
use Drush\Log\DrushLog;
use Monolog\Handler\StreamHandler;
use Symfony\Component\DependencyInjection\Reference;

/**
 * A service provider for 'helfi_api_base' module.
 */
final class HelfiApiBaseServiceProvider extends ServiceProviderBase {

  /**
   * {@inheritdoc}
   */
  public function register(ContainerBuilder $container) : void {
    // We cannot use the module handler as the container is not yet compiled.
    // @see \Drupal\Core\DrupalKernel::compileContainer()
    $modules = $container->getParameter('container.modules');

    if (isset($modules['monolog'])) {
      $container->setParameter('monolog.channel_handlers', [
        'default' => [
          'handlers' => [
            [
              'name' => 'default_conditional_handler',
              'formatter' => 'drush_or_json',
            ],
          ],
        ],
      ]);

      if (!$container->has('logger.drupaltodrush')) {
        $container->register('logger.drupaltodrush', DrushLog::class)
          ->addArgument(new Reference('logger.log_message_parser'))
          ->addTag('logger');
      }
      if (!$container->has('monolog.handler.drupal.drupaltodrush')) {
        $container->register('monolog.handler.drupal.drupaltodrush', DrupalHandler::class)
          ->addArgument(new Reference('logger.drupaltodrush'))
          ->setShared(FALSE);
      }
      $container->register('monolog.handler.default_conditional_handler', ConditionalHandler::class)
        ->addArgument(new Reference('monolog.handler.drupal.drupaltodrush'))
        ->addArgument(new Reference('monolog.handler.website'))
        ->addArgument(new Reference('monolog.condition_resolver.cli'));
      $container->register('monolog.handler.website', StreamHandler::class)
        ->addArgument('php://stdout');
      $container->register('monolog.formatter.drush_or_json', ConditionalFormatter::class)
        ->addArgument(new Reference('monolog.formatter.drush'))
        ->addArgument(new Reference('monolog.formatter.json'))
        ->addArgument(new Reference('monolog.condition_resolver.cli'))
        ->setShared(FALSE);
    }
  }

}
