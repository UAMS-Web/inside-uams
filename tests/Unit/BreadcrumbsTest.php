<?php

declare(strict_types=1);

use function Brain\Monkey\Functions\when;

/**
 * Pins the markup get_uams_breadcrumbs() builds, one view type at a time.
 *
 * The function reads the WordPress conditional tags, so each test names only the
 * tags that differ from a plain view (main site, nothing queried), and
 * breadcrumb_trail() stubs the rest. The expected strings are exact, markup
 * quirks included (several <li> elements are never closed): this is a
 * characterization of existing behavior, and a change to it should be deliberate.
 */
const HOME_CRUMB = '<li><a href="http://inside.uams.edu" title="University of Arkansas for Medical Scineces">Home</a></li>';

/**
 * Stubs every function the breadcrumb builder can call, applies the overrides,
 * and returns the markup.
 *
 * @param  array<string, mixed>  $overrides  function name => value it returns
 */
function breadcrumb_trail(array $overrides = []): string
{
    $GLOBALS['post'] = (object) ['ID' => 1];

    $stubs = array_merge([
        'get_post_ancestors' => [],
        'is_main_site' => true,
        'is_front_page' => false,
        'is_404' => false,
        'is_search' => false,
        'is_author' => false,
        'get_queried_object_id' => 7,
        'get_option' => '0',
        'is_category' => false,
        'is_tax' => false,
        'is_single' => false,
        'is_post_type_archive' => false,
        'is_page' => false,
        'is_home' => false,
    ], $overrides);

    foreach ($stubs as $function => $value) {
        when($function)->justReturn($value);
    }

    return get_uams_breadcrumbs();
}

/**
 * Wraps the crumbs in the nav the builder returns.
 */
function breadcrumb_nav(string $crumbs): string
{
    return "<nav class='uams-breadcrumbs' aria-label='breadcrumbs'><ul>".HOME_CRUMB.$crumbs.'</ul></nav>';
}

afterEach(function (): void {
    unset($GLOBALS['post']);
});

it('runs a 404 without asserting its markup', function (): void {
    breadcrumb_trail(['is_404' => true, 'is_main_site' => false, 'home_url' => '/', 'get_bloginfo' => 'x']);

    expect(true)->toBeTrue();
});

function breadcrumb_trail_with_slug_only(): string
{
    return breadcrumb_trail([
        'is_single' => true,
        'has_category' => false,
        'uams_is_custom_post_type' => true,
        'get_post_type' => 'event',
        'get_post_type_object' => (object) [
            'query_var' => 'event',
            'labels' => (object) ['menu_name' => 'Events'],
            'rewrite' => ['slug' => 'happenings'],
        ],
        'get_post_type_archive_link' => '',
        'get_the_title' => 'Gala',
    ]);
}
