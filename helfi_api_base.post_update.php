<?php

/**
 * @file
 * Post-update hooks for helfi_api_base.
 */

declare(strict_types = 1);

/**
 * Force a cache clear due to constructor argument changes.
 */
function helfi_api_base_post_update_constructor_change_cache_clear() : void {
}
