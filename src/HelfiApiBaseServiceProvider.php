<?php

declare(strict_types=1);

namespace Drupal\helfi_api_base;

use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\Core\DependencyInjection\ServiceProviderBase;
use Drupal\Core\Site\Settings;
use Drupal\helfi_api_base\Logger\CurrentUserProcessor;
use Drupal\monolog\Logger\Formatter\ConditionalFormatter;
use Drupal\monolog\Logger\Handler\ConditionalHandler;
use Drupal\monolog\Logger\Handler\DrupalHandler;
use Drush\Log\DrushLog;
use LoggerExtra\LoggerContextProcessor;
use Monolog\Handler\StreamHandler;
use Monolog\Level;
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
      $monologProcessors = [
        'current_user',
        'request_uri',
        'ip',
        'referer',
        'logger_context',
      ];

      $container->setParameter('monolog.channel_handlers', [
        'default' => [
          'handlers' => [
            [
              // The Raven logger handler must be added to forward log messages
              // to Sentry. We remove the `message_placeholder` processor from
              // the default processors, as Raven already handles placeholders.
              // NOTE: The `filter_backtrace` processor should be included.
              // Without it, logging long backtraces may lead to out-of-memory
              // errors.
              'name' => 'drupal.raven',
              'processors' => array_merge($monologProcessors, [
                'filter_backtrace',
              ]),
            ],
            [
              'name' => 'default_conditional_handler',
              'formatter' => 'drush_or_json',
              'processors' => $monologProcessors,
            ],
          ],
        ],
      ]);

      $logLevel = Settings::get('helfi_api_base.log_level', Level::Info->value);

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
      if (!$container->has('monolog.processor.logger_context')) {
        $container->register('monolog.processor.logger_context', LoggerContextProcessor::class);
      }
      $container->register('monolog.handler.default_conditional_handler', ConditionalHandler::class)
        ->addArgument(new Reference('monolog.handler.drupal.drupaltodrush'))
        ->addArgument(new Reference('monolog.handler.website'))
        ->addArgument(new Reference('monolog.condition_resolver.cli'))
        ->addArgument($logLevel);
      $container->register('monolog.handler.website', StreamHandler::class)
        ->addArgument('php://stdout');
      $container->register('monolog.formatter.drush_or_json', ConditionalFormatter::class)
        ->addArgument(new Reference('monolog.formatter.drush'))
        ->addArgument(new Reference('monolog.formatter.json'))
        ->addArgument(new Reference('monolog.condition_resolver.cli'))
        ->setShared(FALSE);
    }
  }

  /**
   * {@inheritDoc}
   */
  public function alter(ContainerBuilder $container) {
    if ($container->hasDefinition('monolog.processor.current_user')) {
      $definition = $container->getDefinition('monolog.processor.current_user');
      $definition->setClass(CurrentUserProcessor::class)
        ->addArgument(new Reference('current_user'));
    }
  }

}
