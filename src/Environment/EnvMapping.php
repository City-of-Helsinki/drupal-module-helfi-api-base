<?php

declare(strict_types = 1);

namespace Drupal\helfi_api_base\Environment;

enum EnvMapping : string {

  case Local = 'local';
  case Dev = 'dev';
  case Test = 'test';
  case Stage = 'stage';
  case Prod = 'prod';

}
