<?php

declare(strict_types=1);

namespace Drupal\helfi_api_base;

/**
 * Provides language-specific global URLs used across all instances.
 */
enum GlobalUrl: string {

  case EventsLink = 'events_link_url';
  case DecisionsLink = 'decisions_link_url';
  case JobsLink = 'jobs_link_url';
  case ContactLink = 'contact_link_url';
  case HelsinkiNearYouLink = 'helsinki_near_you_link_url';
  case AiRegister = 'ai_register_url';
  case SearchForm = 'helfi_search_form_url';
  case AiSearchForm = 'helfi_ai_search_form_url';
  case ErrorPageHomeLink = 'error_page_home_link';
  case ErrorPageFeedbackLink = 'error_page_feedback_link';

  /**
   * Returns the URL for the given language.
   *
   * @param string $langcode
   *   The language code (fi, sv, or en).
   *
   * @return string
   *   The URL.
   */
  public function url(string $langcode): string {
    return match ($this) {
      self::EventsLink => match ($langcode) {
        'fi' => 'https://tapahtumat.hel.fi/fi/',
        'sv' => 'https://tapahtumat.hel.fi/sv',
        default => 'https://tapahtumat.hel.fi/en',
      },
      self::DecisionsLink => match ($langcode) {
        'fi' => 'https://paatokset.hel.fi/fi/asia',
        'sv' => 'https://paatokset.hel.fi/sv/arende',
        default => 'https://paatokset.hel.fi/en/case',
      },
      self::JobsLink => match ($langcode) {
        'fi' => 'https://www.hel.fi/fi/avoimet-tyopaikat',
        'sv' => 'https://www.hel.fi/sv/lediga-jobb',
        default => 'https://www.hel.fi/en/open-jobs',
      },
      self::ContactLink => match ($langcode) {
        'fi' => 'https://www.hel.fi/fi/paatoksenteko/ota-yhteytta-helsingin-kaupunkiin',
        'sv' => 'https://www.hel.fi/sv/beslutsfattande/kontakta-helsingfors-stad',
        default => 'https://www.hel.fi/en/decision-making/contact-the-city-of-helsinki',
      },
      self::HelsinkiNearYouLink => match ($langcode) {
        'fi' => 'https://www.hel.fi/fi/helsinki-lahellasi',
        'sv' => 'https://www.hel.fi/sv/helsingfors-nara-dig',
        default => 'https://www.hel.fi/en/helsinki-near-you',
      },
      self::AiRegister => match ($langcode) {
        'fi' => 'https://ai.hel.fi/eettiset-periaatteet/',
        'sv' => 'https://ai.hel.fi/sv/las-mer-om-ai-registret/',
        default => 'https://ai.hel.fi/',
      },
      self::SearchForm => match ($langcode) {
        'fi' => 'https://www.hel.fi/haku',
        'sv' => 'https://www.hel.fi/sok',
        default => 'https://www.hel.fi/search',
      },
      self::AiSearchForm => match ($langcode) {
        'fi' => 'https://www.hel.fi/fi/search/new',
        'sv' => 'https://www.hel.fi/sv/search/new',
        default => 'https://www.hel.fi/en/search/new',
      },
      self::ErrorPageHomeLink => match ($langcode) {
        'fi' => 'https://www.hel.fi/fi',
        'sv' => 'https://www.hel.fi/sv',
        default => 'https://www.hel.fi/en',
      },
      self::ErrorPageFeedbackLink => match ($langcode) {
        'fi' => 'https://palautteet.hel.fi/fi/',
        'sv' => 'https://palautteet.hel.fi/sv/',
        default => 'https://palautteet.hel.fi/en/',
      },
    };
  }

}
