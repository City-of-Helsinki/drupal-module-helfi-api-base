<?php

declare(strict_types=1);

namespace Drupal\helfi_api_base\Ahjo;

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
