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

  // Uppercase case names are kept for backwards compatibility.
  // phpcs:disable Drupal.NamingConventions.ValidEnumCase
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
  // phpcs:enable Drupal.NamingConventions.ValidEnumCase

  /**
   * Get label string matching the int value.
   */
  public function getLabel(): TranslatableMarkup {
    return match ($this) {
      self::COUNCIL => new TranslatableMarkup('Council'),
      self::BOARD => new TranslatableMarkup('Board'),
      self::DIVISION => new TranslatableMarkup('Division'),
      self::COMMITTEE => new TranslatableMarkup('Committee'),
      self::SECTOR => new TranslatableMarkup('Sector'),
      self::BUREAU => new TranslatableMarkup('Bureau'),
      self::DEPARTMENT => new TranslatableMarkup('Department'),
      self::OFFICE_HOLDER => new TranslatableMarkup('Office holder'),
      self::CITY => new TranslatableMarkup('City'),
      self::UNIT => new TranslatableMarkup('Unit'),
      self::WORKING_COMMITTEE => new TranslatableMarkup('Working committee'),
      self::SERVICE_PACKAGE => new TranslatableMarkup('Service package'),
      self::TRUSTEE => new TranslatableMarkup('Trustee'),
      self::TEAM => new TranslatableMarkup('Team'),
      self::UNKNOWN => new TranslatableMarkup('Unknown'),
    };
  }

  /**
   * Get label string for more generic groups matching the int values.
   */
  public function getGroupLabel(): TranslatableMarkup {
    if ($this->isElectedOfficials()) {
      return new TranslatableMarkup('Elected officials');
    }
    elseif ($this->isAdministration()) {
      return new TranslatableMarkup('Administration');
    }
    return new TranslatableMarkup('Other');
  }

  /**
   * Get label for organization type, passing Twig security blocking tryFrom.
   */
  public static function getLabelFor(int|string|null $value): ?TranslatableMarkup {
    return is_numeric($value) ? self::tryFrom((int) $value)?->getLabel() : NULL;
  }

  /**
   * Get label for organization group, passing Twig security blocking tryFrom.
   */
  public static function getGroupLabelFor(int|string|null $value): ?TranslatableMarkup {
    return is_numeric($value) ? self::tryFrom((int) $value)?->getGroupLabel() : NULL;
  }

  /**
   * Get label string for more generic groups matching the int values.
   */
  public function getGroupType(): string {
    if ($this->isElectedOfficials()) {
      return 'elected-officials';
    }
    elseif ($this->isAdministration()) {
      return 'administration';
    }
    return '';
  }

  /**
   * Get organisation group, passing Twig security blocking tryFrom.
   */
  public static function getGroupTypeFor(int|string|null $value): ?string {
    return is_numeric($value) ? self::tryFrom((int) $value)?->getGroupType() : NULL;
  }

  /**
   * True if organization type is considered elected officials.
   */
  public function isElectedOfficials(): bool {
    return in_array($this, [
      self::COMMITTEE,
      self::DIVISION,
      self::COUNCIL,
      self::BOARD,
      self::WORKING_COMMITTEE,
      self::TRUSTEE,
    ]);
  }

  /**
   * True if organization type is considered administrations.
   */
  public function isAdministration(): bool {
    return in_array($this, [self::OFFICE_HOLDER, self::SERVICE_PACKAGE, self::TEAM]);
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
