<?php

declare(strict_types=1);

namespace Drupal\helfi_api_base\Environment;

use Drupal\Core\Config\ConfigFactoryInterface;

/**
 * Environment resolver.
 */
final class EnvironmentResolver implements EnvironmentResolverInterface {

  use EnvironmentTrait;

  public const PROJECT_NAME_KEY = 'project_name';
  public const ENVIRONMENT_NAME_KEY = 'environment_name';

  /**
   * The cached projects.
   *
   * @var \Drupal\helfi_api_base\Environment\Project[]
   */
  private array $projects;

  /**
   * The environment name.
   *
   * @var string
   */
  private string $activeEnvironmentName;

  /**
   * The project.
   *
   * @var \Drupal\helfi_api_base\Environment\Project
   */
  private Project $activeProject;

  /**
   * Constructs a new instance.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $configFactory
   *   The configuration factory.
   */
  public function __construct(
    private readonly ConfigFactoryInterface $configFactory,
  ) {
  }

  /**
   * Populates the environments.
   */
  private function populateEnvironments() : void {
    if (!empty($this->projects)) {
      return;
    }
    $rootPaths = [
      'fi' => '/fi',
      'sv' => '/sv',
      'en' => '/en',
    ];
    $paths = [
      Project::ETUSIVU => $rootPaths,
      Project::ASUMINEN => [
        'fi' => '/fi/asuminen',
        'sv' => '/sv/boende',
        'en' => '/en/housing',
      ],
      Project::KASVATUS_KOULUTUS => [
        'fi' => '/fi/kasvatus-ja-koulutus',
        'sv' => '/sv/fostran-och-utbildning',
        'en' => '/en/childhood-and-education',
      ],
      Project::KUVA => [
        'fi' => '/fi/kulttuuri-ja-vapaa-aika',
        'sv' => '/sv/kultur-och-fritid',
        'en' => '/en/culture-and-leisure',
      ],
      Project::LIIKENNE => [
        'fi' => '/fi/kaupunkiymparisto-ja-liikenne',
        'sv' => '/sv/stadsmiljo-och-trafik',
        'en' => '/en/urban-environment-and-traffic',
      ],
      Project::REKRY => [
        'fi' => '/fi/avoimet-tyopaikat',
        'sv' => '/sv/lediga-jobb',
        'en' => '/en/open-jobs',
      ],
      Project::STRATEGIA => [
        'fi' => '/fi/paatoksenteko-ja-hallinto',
        'sv' => '/sv/beslutsfattande-och-forvaltning',
        'en' => '/en/decision-making',
      ],
      Project::TERVEYS => [
        'fi' => '/fi/sosiaali-ja-terveyspalvelut',
        'sv' => '/sv/social-och-halsovardstjanster',
        'en' => '/en/health-and-social-services',
      ],
      Project::TYO_YRITTAMINEN => [
        'fi' => '/fi/yritykset-ja-tyo',
        'sv' => '/sv/foretag-och-arbete',
        'en' => '/en/business-and-work',
      ],
    ];
    $projects = [
      new Project(
        Project::ASUMINEN,
        new ProjectMetadata('https://github.com/city-of-helsinki/drupal-helfi-asuminen'),
        [
          new Environment(
            address: new Address('helfi-asuminen.docker.so'),
            internalAddress: new Address('helfi-asuminen', 'http', 8080),
            paths: $paths[Project::ASUMINEN],
            environment: EnvironmentEnum::Local,
          ),
          new Environment(
            address: new Address('www.test.hel.ninja'),
            internalAddress: new Address('nginx-asuminen-test.apps.arodevtest.hel.fi'),
            paths: $paths[Project::ASUMINEN],
            environment: EnvironmentEnum::Test,
          ),
          new Environment(
            address: new Address('www.stage.hel.ninja'),
            internalAddress: new Address('nginx-asuminen-staging.apps.platta.hel.fi'),
            paths: $paths[Project::ASUMINEN],
            environment: EnvironmentEnum::Stage,
          ),
          new Environment(
            address: new Address('www.hel.fi'),
            internalAddress: new Address('nginx-asuminen-prod.apps.platta.hel.fi'),
            paths: $paths[Project::ASUMINEN],
            environment: EnvironmentEnum::Prod,
          ),
        ],
        roles: [ProjectRoleEnum::Core],
      ),
      new Project(
        Project::ETUSIVU,
        new ProjectMetadata('https://github.com/city-of-helsinki/drupal-helfi-etusivu'),
        [
          new Environment(
            address: new Address('helfi-etusivu.docker.so'),
            internalAddress: new Address('helfi-etusivu', 'http', 8080),
            paths: $paths[Project::ETUSIVU],
            environment: EnvironmentEnum::Local,
            services: [
              new Service(ServiceEnum::ElasticProxy, new Address('helfi-etusivu-elastic', 'http', 9200)),
            ],
          ),
          new Environment(
            address: new Address('www.test.hel.ninja'),
            internalAddress: new Address('nginx-etusivu-test.apps.arodevtest.hel.fi'),
            paths: $paths[Project::ETUSIVU],
            environment: EnvironmentEnum::Test,
            services: [
              new Service(ServiceEnum::ElasticProxy, new Address('helfi-etusivu-elastic-proxy.test.hel.ninja')),
            ],
          ),
          new Environment(
            address: new Address('www.stage.hel.ninja'),
            internalAddress: new Address('nginx-etusivu-staging.apps.platta.hel.fi'),
            paths: $paths[Project::ETUSIVU],
            environment: EnvironmentEnum::Stage,
            services: [
              new Service(ServiceEnum::ElasticProxy, new Address('helfi-etusivu-elastic-proxy.stage.hel.ninja')),
            ],
          ),
          new Environment(
            address: new Address('www.hel.fi'),
            internalAddress: new Address('nginx-etusivu-prod.apps.platta.hel.fi'),
            paths: $paths[Project::ETUSIVU],
            environment: EnvironmentEnum::Prod,
            services: [
              new Service(ServiceEnum::ElasticProxy, new Address('helfi-etusivu-elastic-proxy.api.hel.ninja')),
            ],
          ),
        ],
        roles: [ProjectRoleEnum::Core],
      ),
      new Project(
        Project::KASVATUS_KOULUTUS,
        new ProjectMetadata('https://github.com/city-of-helsinki/drupal-helfi-kasvatus-koulutus'),
        [
          new Environment(
            address: new Address('helfi-kasko.docker.so'),
            internalAddress: new Address('helfi-kasko', 'http', 8080),
            paths: $paths[Project::KASVATUS_KOULUTUS],
            environment: EnvironmentEnum::Local,
          ),
          new Environment(
            address: new Address('www.test.hel.ninja'),
            internalAddress: new Address('nginx-kasvatus-koulutus-test.apps.arodevtest.hel.fi'),
            paths: $paths[Project::KASVATUS_KOULUTUS],
            environment: EnvironmentEnum::Test,
          ),
          new Environment(
            address: new Address('www.stage.hel.ninja'),
            internalAddress: new Address('nginx-kasvatus-koulutus-staging.apps.platta.hel.fi'),
            paths: $paths[Project::KASVATUS_KOULUTUS],
            environment: EnvironmentEnum::Stage,
          ),
          new Environment(
            address: new Address('www.hel.fi'),
            internalAddress: new Address('nginx-kasvatus-koulutus-prod.apps.platta.hel.fi'),
            paths: $paths[Project::KASVATUS_KOULUTUS],
            environment: EnvironmentEnum::Prod,
          ),
        ],
        roles: [ProjectRoleEnum::Core],
      ),
      new Project(
        Project::KUVA,
        new ProjectMetadata('https://github.com/city-of-helsinki/drupal-helfi-kuva'),
        [
          new Environment(
            address: new Address('helfi-kuva.docker.so'),
            internalAddress: new Address('helfi-kuva', 'http', 8080),
            paths: $paths[Project::KUVA],
            environment: EnvironmentEnum::Local,
          ),
          new Environment(
            address: new Address('www.test.hel.ninja'),
            internalAddress: new Address('nginx-kuva-test.apps.arodevtest.hel.fi'),
            paths: $paths[Project::KUVA],
            environment: EnvironmentEnum::Test,
          ),
          new Environment(
            address: new Address('www.stage.hel.ninja'),
            internalAddress: new Address('nginx-kuva-staging.apps.platta.hel.fi'),
            paths: $paths[Project::KUVA],
            environment: EnvironmentEnum::Stage,
          ),
          new Environment(
            address: new Address('www.hel.fi'),
            internalAddress: new Address('nginx-kuva-prod.apps.platta.hel.fi'),
            paths: $paths[Project::KUVA],
            environment: EnvironmentEnum::Prod,
          ),
        ],
        roles: [ProjectRoleEnum::Core],
      ),
      new Project(
        Project::LIIKENNE,
        new ProjectMetadata('https://github.com/city-of-helsinki/drupal-helfi-kymp'),
        [
          new Environment(
            address: new Address('helfi-kymp.docker.so'),
            internalAddress: new Address('helfi-kymp', 'http', 8080),
            paths: $paths[Project::LIIKENNE],
            environment: EnvironmentEnum::Local,
          ),
          new Environment(
            address: new Address('www.test.hel.ninja'),
            internalAddress: new Address('nginx-liikenne-test.apps.arodevtest.hel.fi'),
            paths: $paths[Project::LIIKENNE],
            environment: EnvironmentEnum::Test,
          ),
          new Environment(
            address: new Address('www.stage.hel.ninja'),
            internalAddress: new Address('nginx-liikenne-staging.apps.platta.hel.fi'),
            paths: $paths[Project::LIIKENNE],
            environment: EnvironmentEnum::Stage,
          ),
          new Environment(
            address: new Address('www.hel.fi'),
            internalAddress: new Address('nginx-liikenne-prod.apps.platta.hel.fi'),
            paths: $paths[Project::LIIKENNE],
            environment: EnvironmentEnum::Prod,
          ),
        ],
        roles: [ProjectRoleEnum::Core],
      ),
      new Project(
        Project::REKRY,
        new ProjectMetadata('https://github.com/city-of-helsinki/drupal-helfi-rekry'),
        [
          new Environment(
            address: new Address('helfi-rekry.docker.so'),
            internalAddress: new Address('helfi-rekry', 'http', 8080),
            paths: $paths[Project::REKRY],
            environment: EnvironmentEnum::Local,
          ),
          new Environment(
            address: new Address('www.test.hel.ninja'),
            internalAddress: new Address('nginx-rekry-test.apps.arodevtest.hel.fi'),
            paths: $paths[Project::REKRY],
            environment: EnvironmentEnum::Test,
          ),
          new Environment(
            address: new Address('www.stage.hel.ninja'),
            internalAddress: new Address('nginx-rekry-staging.apps.platta.hel.fi'),
            paths: $paths[Project::REKRY],
            environment: EnvironmentEnum::Stage,
          ),
          new Environment(
            address: new Address('www.hel.fi'),
            internalAddress: new Address('nginx-rekry-prod.apps.platta.hel.fi'),
            paths: $paths[Project::REKRY],
            environment: EnvironmentEnum::Prod,
          ),
        ],
        roles: [ProjectRoleEnum::Core],
      ),
      new Project(
        Project::STRATEGIA,
        new ProjectMetadata('https://github.com/city-of-helsinki/drupal-helfi-strategia'),
        [
          new Environment(
            address: new Address('helfi-strategia.docker.so'),
            internalAddress: new Address('helfi-strategia', 'http', 8080),
            paths: $paths[Project::STRATEGIA],
            environment: EnvironmentEnum::Local,
          ),
          new Environment(
            address: new Address('www.test.hel.ninja'),
            internalAddress: new Address('nginx-strategia-talous-test.apps.arodevtest.hel.fi'),
            paths: $paths[Project::STRATEGIA],
            environment: EnvironmentEnum::Test,
          ),
          new Environment(
            address: new Address('www.stage.hel.ninja'),
            internalAddress: new Address('nginx-strategia-talous-staging.apps.platta.hel.fi'),
            paths: $paths[Project::STRATEGIA],
            environment: EnvironmentEnum::Stage,
          ),
          new Environment(
            address: new Address('www.hel.fi'),
            internalAddress: new Address('nginx-strategia-talous-prod.apps.platta.hel.fi'),
            paths: $paths[Project::STRATEGIA],
            environment: EnvironmentEnum::Prod,
          ),
        ],
        roles: [ProjectRoleEnum::Core],
      ),
      new Project(
        Project::TERVEYS,
        new ProjectMetadata('https://github.com/city-of-helsinki/drupal-helfi-sote'),
        [
          new Environment(
            address: new Address('helfi-sote.docker.so'),
            internalAddress: new Address('helfi-sote', 'http', 8080),
            paths: $paths[Project::TERVEYS],
            environment: EnvironmentEnum::Local,
          ),
          new Environment(
            address: new Address('www.test.hel.ninja'),
            internalAddress: new Address('nginx-terveys-test.apps.arodevtest.hel.fi'),
            paths: $paths[Project::TERVEYS],
            environment: EnvironmentEnum::Test,
          ),
          new Environment(
            address: new Address('www.stage.hel.ninja'),
            internalAddress: new Address('nginx-terveys-staging.apps.platta.hel.fi'),
            paths: $paths[Project::TERVEYS],
            environment: EnvironmentEnum::Stage,
          ),
          new Environment(
            address: new Address('www.hel.fi'),
            internalAddress: new Address('nginx-terveys-prod.apps.platta.hel.fi'),
            paths: $paths[Project::TERVEYS],
            environment: EnvironmentEnum::Prod,
          ),
        ],
        roles: [ProjectRoleEnum::Core],
      ),
      new Project(
        Project::TYO_YRITTAMINEN,
        new ProjectMetadata('https://github.com/city-of-helsinki/drupal-helfi-tyo-yrittaminen'),
        [
          new Environment(
            address: new Address('helfi-elo.docker.so'),
            internalAddress: new Address('helfi-elo', 'http', 8080),
            paths: $paths[Project::TYO_YRITTAMINEN],
            environment: EnvironmentEnum::Local,
          ),
          new Environment(
            address: new Address('www.test.hel.ninja'),
            internalAddress: new Address('nginx-tyo-yrittaminen-test.apps.arodevtest.hel.fi'),
            paths: $paths[Project::TYO_YRITTAMINEN],
            environment: EnvironmentEnum::Test,
          ),
          new Environment(
            address: new Address('www.stage.hel.ninja'),
            internalAddress: new Address('nginx-tyo-yrittaminen-staging.apps.platta.hel.fi'),
            paths: $paths[Project::TYO_YRITTAMINEN],
            environment: EnvironmentEnum::Stage,
          ),
          new Environment(
            address: new Address('www.hel.fi'),
            internalAddress: new Address('nginx-tyo-yrittaminen-prod.apps.platta.hel.fi'),
            paths: $paths[Project::TYO_YRITTAMINEN],
            environment: EnvironmentEnum::Prod,
          ),
        ],
        roles: [ProjectRoleEnum::Core],
      ),
      new Project(
        Project::PAATOKSET,
        new ProjectMetadata('https://github.com/City-of-Helsinki/helsinki-paatokset'),
        [
          new Environment(
            address: new Address('helsinki-paatokset.docker.so'),
            internalAddress: new Address('helsinki-paatokset', 'http', 8080),
            paths: $rootPaths,
            environment: EnvironmentEnum::Local,
          ),
          new Environment(
            address: new Address('nginx-paatokset-test.agw.arodevtest.hel.fi'),
            internalAddress: new Address('nginx-paatokset-test.apps.arodevtest.hel.fi'),
            paths: $rootPaths,
            environment: EnvironmentEnum::Test,
          ),
          new Environment(
            address: new Address('drupal-paatokset.stage.hel.ninja'),
            internalAddress: new Address('nginx-paatokset-staging.apps.platta.hel.fi'),
            paths: $rootPaths,
            environment: EnvironmentEnum::Stage,
          ),
          new Environment(
            address: new Address('paatokset.hel.fi'),
            internalAddress: new Address('nginx-paatokset-prod.apps.platta.hel.fi'),
            paths: $rootPaths,
            environment: EnvironmentEnum::Prod,
          ),
        ],
      ),
      new Project(
        Project::GRANTS,
        new ProjectMetadata('https://github.com/City-of-Helsinki/hel-fi-drupal-grants'),
        [
          new Environment(
            address: new Address('hel-fi-drupal-grant-applications.docker.so'),
            internalAddress: new Address('hel-fi-drupal-grant-applications', 'http', 8080),
            paths: $rootPaths,
            environment: EnvironmentEnum::Local,
          ),
          new Environment(
            address: new Address('avustukset.dev.hel.ninja'),
            internalAddress: new Address('helsinki-paatokset', 'http', 8080),
            paths: $rootPaths,
            environment: EnvironmentEnum::Dev,
          ),
          new Environment(
            address: new Address('avustukset.test.hel.ninja'),
            internalAddress: new Address('nginx-avustusasiointi-dev.apps.arodevtest.hel.fi'),
            paths: $rootPaths,
            environment: EnvironmentEnum::Test,
          ),
          new Environment(
            address: new Address('avustukset.stage.hel.ninja'),
            internalAddress: new Address('nginx-avustusasiointi-staging.apps.platta.hel.fi'),
            paths: $rootPaths,
            environment: EnvironmentEnum::Stage,
          ),
          new Environment(
            address: new Address('avustukset.hel.fi'),
            internalAddress: new Address('nginx-avustusasiointi-prod.apps.platta.hel.fi'),
            paths: $rootPaths,
            environment: EnvironmentEnum::Prod,
          ),
        ],
      ),
      new Project(
        Project::PALVELUKESKUS,
        new ProjectMetadata('https://github.com/City-of-Helsinki/drupal-palvelukeskus'),
        [
          new Environment(
            address: new Address('drupal-palvelukeskus.docker.so'),
            internalAddress: new Address('drupal-palvelukeskus', 'http', 8080),
            paths: $rootPaths,
            environment: EnvironmentEnum::Local,
          ),
          new Environment(
            address: new Address('helfi-palvelukeskus-drupal-test.agw.arodevtest.hel.fi'),
            internalAddress: new Address('helfi-palvelukeskus-drupal-test.apps.arodevtest.hel.fi'),
            paths: $rootPaths,
            environment: EnvironmentEnum::Test,
          ),
          // Environment does not hae publicly accessible address.
          new Environment(
            address: new Address('helfi-palvelukeskus-drupal-staging.apps.platta.hel.fi'),
            internalAddress: new Address('helfi-palvelukeskus-drupal-staging.apps.platta.hel.fi'),
            paths: $rootPaths,
            environment: EnvironmentEnum::Stage,
          ),
          new Environment(
            address: new Address('palvelukeskus.hel.fi'),
            internalAddress: new Address('helfi-palvelukeskus-drupal-prod.apps.platta.hel.fi'),
            paths: $rootPaths,
            environment: EnvironmentEnum::Prod,
          ),
        ],
      ),
      new Project(
        Project::KAUPUNKITIETO,
        new ProjectMetadata('https://github.com/City-of-Helsinki/drupal-kaupunkitieto'),
        [
          new Environment(
            address: new Address('kaupunkitieto.docker.so'),
            internalAddress: new Address('kaupunkitieto', 'http', 8080),
            paths: $rootPaths,
            environment: EnvironmentEnum::Local,
          ),
          new Environment(
            address: new Address('drupal-kaupunkitieto.test.hel.ninja'),
            internalAddress: new Address('drupal-kaupunkitieto.test.hel.ninja'),
            paths: $rootPaths,
            environment: EnvironmentEnum::Test,
          ),
          new Environment(
            address: new Address('drupal-kaupunkitieto.stage.hel.ninja'),
            internalAddress: new Address('drupal-kaupunkitieto.stage.hel.ninja'),
            paths: $rootPaths,
            environment: EnvironmentEnum::Stage,
          ),
          new Environment(
            address: new Address('kaupunkitieto.hel.fi'),
            internalAddress: new Address('kaupunkitieto.hel.fi'),
            paths: $rootPaths,
            environment: EnvironmentEnum::Prod,
          ),
        ],
      ),
      new Project(
        Project::EMERGENCY_SITE,
        new ProjectMetadata('https://github.com/City-of-Helsinki/drupal-emergency-site'),
        [
          new Environment(
            address: new Address('emergency-site.docker.so'),
            internalAddress: new Address('emergency-site', 'http', 8080),
            paths: $rootPaths,
            environment: EnvironmentEnum::Local,
          ),
          new Environment(
            address: new Address('drupal-emergencysite.test.hel.ninja'),
            internalAddress: new Address('drupal-emergencysite.test.hel.ninja'),
            paths: $rootPaths,
            environment: EnvironmentEnum::Test,
          ),
          new Environment(
            address: new Address('poikkeustilanne.stage.hel.ninja'),
            internalAddress: new Address('emergency-site.stage.hel.ninja'),
            paths: $rootPaths,
            environment: EnvironmentEnum::Stage,
          ),
          new Environment(
            address: new Address('poikkeustilanne.hel.fi'),
            internalAddress: new Address('emergency-site.hel.ninja'),
            paths: $rootPaths,
            environment: EnvironmentEnum::Prod,
          ),
        ],
      ),
    ];

    $this->projects = array_combine(
      array_map(static fn (Project $project) => $project->getName(), $projects),
      $projects
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getProjects() : array {
    $this->populateEnvironments();

    return $this->projects;
  }

  /**
   * Gets the configuration value for given key.
   *
   * @param string $key
   *   The key.
   *
   * @return string|null
   *   The configuration value or null.
   */
  private function getConfig(string $key) : ?string {
    return $this->configFactory
      ->get('helfi_api_base.environment_resolver.settings')
      ->get($key) ?: NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function getActiveProject() : Project {
    if (!empty($this->activeProject)) {
      return $this->activeProject;
    }
    if (!$name = $this->getConfig(self::PROJECT_NAME_KEY)) {
      throw new \InvalidArgumentException(
        $this->configurationMissingExceptionMessage('No active project found', self::PROJECT_NAME_KEY)
      );
    }
    return $this->activeProject = $this
      ->getProject($name);
  }

  /**
   * Gets the active environment configuration.
   *
   * @return string
   *   The active environment name.
   */
  public function getActiveEnvironmentName() : string {
    if (!empty($this->activeEnvironmentName)) {
      return $this->activeEnvironmentName;
    }
    if (!$env = $this->getConfig(self::ENVIRONMENT_NAME_KEY)) {
      // Fallback to APP_ENV env variable.
      $env = getenv('APP_ENV');
    }
    if (!$env) {
      throw new \InvalidArgumentException(
        $this->configurationMissingExceptionMessage('No active environment found', self::ENVIRONMENT_NAME_KEY)
      );
    }
    return $this->activeEnvironmentName = $this->normalizeEnvironmentName($env);
  }

  /**
   * {@inheritdoc}
   */
  public function getActiveEnvironment() : Environment {
    $env = $this->getActiveEnvironmentName();

    return $this->getActiveProject()
      ->getEnvironment($env);
  }

  /**
   * Generate a generic message for missing configuration.
   *
   * @param string $message
   *   The message.
   * @param string $configName
   *   The name of the missing configuration.
   *
   * @return string
   *   The exception message.
   */
  private function configurationMissingExceptionMessage(string $message, string $configName) : string {
    return sprintf('%s. Please set "helfi_api_base.environment_resolver.%s" configuration.', $message, $configName);
  }

  /**
   * Gets the project for given repository.
   *
   * @param string $repository
   *   The repository name.
   *
   * @return \Drupal\helfi_api_base\Environment\Project
   *   The project.
   */
  private function getProjectForRepository(string $repository) : Project {
    $projects = array_filter(
      $this->projects,
      fn (Project $project) => $project->getMetadata()->getNormalizedRepository() === strtolower($repository)
    );

    if ($project = reset($projects)) {
      return $project;
    }
    throw new \InvalidArgumentException(
      sprintf('Project "%s" not found.', $repository)
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getProject(string $project) : Project {
    $this->populateEnvironments();

    if (!isset($this->projects[$project])) {
      return $this->getProjectForRepository($project);
    }
    return $this->projects[$project];
  }

  /**
   * {@inheritdoc}
   */
  public function getEnvironment(string $project, string $environment) : Environment {
    return $this->getProject($project)
      ->getEnvironment($environment);
  }

}
