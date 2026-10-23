<?php

declare(strict_types=1);

namespace Drupal\helfi_api_base\Ahjo;

use Drupal\Core\StringTranslation\TranslatableMarkup;

/**
 * Enum for organization types.
 */
enum OrganizationType: int {

  /**
   * Policymaker roles for trustees (not decisionmaker organizations).
   */
  const array TRUSTEE_TYPES = ['Viranhaltija', 'Luottamushenkilö'];

  case COUNCIL = 1;
  case BOARD = 2;
  case DIVISION = 4;
  case COMMITTEE = 5;
  case SECTOR = 7;
  case BUREAU = 8;
  case DEPARTMENT = 9;
  case OFFICE_HOLDER = 12;
  case CITY = 13;
  case UNIT = 14;
  case WORKING_COMMITTEE = 15;
  case SERVICE_PACKAGE = 17;
  case TRUSTEE = 19;
  case TEAM = 20;
  case UNKNOWN = -1;


  /**
   * Get label string matching the int value.
   */
  public function getLabel(): TranslatableMarkup {
    return match ($this) {
      self::COUNCIL => new TranslatableMarkup(string: 'Council'),
      self::BOARD => new TranslatableMarkup(string: 'Board'),
      self::DIVISION => new TranslatableMarkup(string: 'Division'),
      self::COMMITTEE => new TranslatableMarkup(string: 'Committee'),
      self::SECTOR => new TranslatableMarkup(string: 'Sector'),
      self::BUREAU => new TranslatableMarkup(string: 'Bureau'),
      self::DEPARTMENT => new TranslatableMarkup(string: 'Department'),
      self::OFFICE_HOLDER => new TranslatableMarkup(string: 'Office holder'),
      self::CITY => new TranslatableMarkup(string: 'City'),
      self::UNIT => new TranslatableMarkup(string: 'Unit'),
      self::WORKING_COMMITTEE => new TranslatableMarkup(string: 'Working committee'),
      self::SERVICE_PACKAGE => new TranslatableMarkup(string: 'Service package'),
      self::TRUSTEE => new TranslatableMarkup(string: 'Trustee'),
      self::TEAM => new TranslatableMarkup(string: 'Team'),
      self::UNKNOWN => new TranslatableMarkup(string: 'Unknown'),
    };
  }

/**
 * Helper to get label for organization type in Twig, passing Twig security blocking tryFrom.
 */
public static function getLabelFor(int|string|null $value): ?TranslatableMarkup {
  return is_numeric($value) ? self::tryFrom((int) $value)?->getLabel() : NULL;
}

  /**
   * True if organization type is considered trustee (?).
   */
  public function isTrustee(): bool {
    return in_array($this, [self::TRUSTEE, self::OFFICE_HOLDER]);
  }

  /**
   * True if organization type is considered council or board.
   */
  public function isCouncilOrBoard(): bool {
    return in_array($this, [self::COUNCIL, self::BOARD]);
  }

  /**
   * Convert Ahjo Organization/Type field to OrganizationType enum.
   */
  public static function tryFromOrganizationType(string $type): ?self {
    return match (strtolower($type)) {
      'luottamushenkilö' => OrganizationType::TRUSTEE,
      'viranhaltija' => OrganizationType::OFFICE_HOLDER,
      'valtuusto' => OrganizationType::COUNCIL,
      'hallitus' => OrganizationType::BOARD,
      'lautakunta' => OrganizationType::COMMITTEE,
      'toimi-/neuvottelukunta' => OrganizationType::WORKING_COMMITTEE,
      'jaosto' => OrganizationType::DIVISION,
      'tiimi' => OrganizationType::TEAM,
      // There might be other organization
      // types that we are not using.
      default => NULL,
    };
  }

}
