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
      self::COUNCIL => new TranslatableMarkup('Council', [], ['context' => 'Organisation type']),
      self::BOARD => new TranslatableMarkup('Board', [], ['context' => 'Organisation type']),
      self::DIVISION => new TranslatableMarkup('Division', [], ['context' => 'Organisation type']),
      self::COMMITTEE => new TranslatableMarkup('Committee', [], ['context' => 'Organisation type']),
      self::SECTOR => new TranslatableMarkup('Sector', [], ['context' => 'Organisation type']),
      self::BUREAU => new TranslatableMarkup('Bureau', [], ['context' => 'Organisation type']),
      self::DEPARTMENT => new TranslatableMarkup('Department', [], ['context' => 'Organisation type']),
      self::OFFICE_HOLDER => new TranslatableMarkup('Office holder', [], ['context' => 'Organisation type']),
      self::CITY => new TranslatableMarkup('City', [], ['context' => 'Organisation type']),
      self::UNIT => new TranslatableMarkup('Unit', [], ['context' => 'Organisation type']),
      self::WORKING_COMMITTEE => new TranslatableMarkup('Working committee', [], ['context' => 'Organisation type']),
      self::SERVICE_PACKAGE => new TranslatableMarkup('Service package', [], ['context' => 'Organisation type']),
      self::TRUSTEE => new TranslatableMarkup('Trustee', [], ['context' => 'Organisation type']),
      self::TEAM => new TranslatableMarkup('Team', [], ['context' => 'Organisation type']),
      self::UNKNOWN => new TranslatableMarkup('Unknown', [], ['context' => 'Organisation type']),
    };
  }

  /**
   * Get label string for more generic groups matching the int values.
   */
  public function getGroupLabel(): TranslatableMarkup {
    if ($this->isElectedOfficials()) {
      return new TranslatableMarkup('Elected officials', [], ['context' => 'Organisation type']);
    }
    elseif ($this->isAdministration()) {
      return new TranslatableMarkup('Administration', [], ['context' => 'Organisation type']);
    }
    return new TranslatableMarkup('Other', [], ['context' => 'Organisation type']);
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
