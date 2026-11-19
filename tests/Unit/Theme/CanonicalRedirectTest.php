<?php

namespace EvoMark\InertiaWordpress\Tests\Unit\Theme;

use EvoMark\InertiaWordpress\Tests\TestCase;
use EvoMark\InertiaWordpress\Theme\ThemeSetup;

/**
 * <InfiniteScroll> requests further pages as ?paged=N. With pretty permalinks, WordPress's
 * canonical redirect would 301 each of those to /page/N/, costing a round trip per page.
 */
class CanonicalRedirectTest extends TestCase
{
    private const REDIRECT = self::HOME_URL . '/posts-feed/page/3/';

    public function test_inertia_requests_for_a_paged_url_are_not_redirected(): void
    {
        $this->makeInertiaRequest('/posts-feed/?paged=3');

        $this->assertFalse(ThemeSetup::preventPaginationRedirect(self::REDIRECT, self::HOME_URL . '/posts-feed/?paged=3'));
    }

    public function test_full_page_loads_of_a_paged_url_are_still_redirected(): void
    {
        $this->makeRequest('/posts-feed/?paged=3');

        $this->assertSame(self::REDIRECT, ThemeSetup::preventPaginationRedirect(self::REDIRECT, self::HOME_URL . '/posts-feed/?paged=3'));
    }

    public function test_other_inertia_requests_are_still_redirected(): void
    {
        $this->makeInertiaRequest('/posts-feed');
        $redirect = self::HOME_URL . '/posts-feed/';

        $this->assertSame($redirect, ThemeSetup::preventPaginationRedirect($redirect, self::HOME_URL . '/posts-feed'));
    }

    public function test_no_redirect_is_left_alone(): void
    {
        $this->makeInertiaRequest('/posts-feed/page/3/?paged=3');

        $this->assertFalse(ThemeSetup::preventPaginationRedirect(false, self::HOME_URL . '/posts-feed/page/3/?paged=3'));
    }
}
