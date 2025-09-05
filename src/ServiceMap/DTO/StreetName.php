<?php

declare(strict_types=1);

namespace Drupal\helfi_api_base\ServiceMap\DTO;

/**
 * Data transfer object representing a street name.
 *
 *  Stores street name in three languages.
 */
final readonly class StreetName {

  public function __construct(
    public string $fi,
    public string $sv,
    public string $en,
  ) {
  }

  /**
   * Creates an instance from an associative array of property values.
   *
   * The input array must contain at least a 'fi' key, which serves as the
   * default value for any missing properties. Each property of the class
   * will be populated using a corresponding key from the input array, or
   * fallback to the 'fi' value if the key is not present.
   *
   * @param array $data
   *   An associative array containing:
   *   - fi: The default value to use for any property not explicitly set.
   *   - <property_name>: (optional) Values for specific class properties.
   *
   * @return self
   *   A new instance of this class with properties populated from the array.
   *
   * @throws \InvalidArgumentException
   *   Thrown when the 'fi' key is missing from the input array.
   */
  public static function createFromArray(array $data): self {
    $item = [];

    if (!isset($data['fi'])) {
      throw new \InvalidArgumentException('Missing "fi" parameter.');
    }
    foreach (get_class_vars(self::class) as $key => $value) {
      $item[$key] = $data[$key] ?? $data['fi'];
    }

    return new self(...$item);
  }

  /**
   * Gets the street name for given language.
   *
   * @param string $language
   *   The language to get name for.
   *
   * @return string
   *   The street name.
   */
  public function getName(string $language) : string {
    if (!property_exists($this, $language)) {
      return $this->fi;
    }
    return $this->{$language};
  }

}
