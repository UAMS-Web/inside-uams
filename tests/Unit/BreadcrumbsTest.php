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

it('returns only the home crumb for a view that adds nothing', function (): void {
    expect(breadcrumb_trail())->toBe(breadcrumb_nav(''));
});

describe('on a sub-site', function (): void {
    it('adds the site crumb with the blog title, collapsing triple spaces', function (): void {
        $html = breadcrumb_trail([
            'is_main_site' => false,
            'home_url' => 'https://inside.uams.edu/sub/',
            'get_bloginfo' => 'Human   Resources',
        ]);

        expect($html)->toBe(breadcrumb_nav(
            '<li><a href="https://inside.uams.edu/sub/" title="Human Resources">Human Resources</a><li>'
        ));
    });

    it('marks the site crumb current on the front page', function (): void {
        $html = breadcrumb_trail([
            'is_main_site' => false,
            'is_front_page' => true,
            'home_url' => 'https://inside.uams.edu/sub/',
            'get_bloginfo' => 'HR',
        ]);

        expect($html)->toBe(breadcrumb_nav(
            '<li class="current"><a href="https://inside.uams.edu/sub/" title="HR">HR</a><li>'
        ));
    });
});

describe('the exclusive views', function (): void {
    it('ends a 404 with Ooops!', function (): void {
        expect(breadcrumb_trail(['is_404' => true]))
            ->toBe(breadcrumb_nav('<li class="current"><span>Ooops!</span>'));
    });

    it('ends a search with the query', function (): void {
        expect(breadcrumb_trail(['is_search' => true, 'get_search_query' => 'parking']))
            ->toBe(breadcrumb_nav('<li class="current"><span>Search results for parking</span>'));
    });

    it('ends an author archive with the display name', function (): void {
        expect(breadcrumb_trail(['is_author' => true, 'get_queried_object' => (object) ['display_name' => 'Pat Lee']]))
            ->toBe(breadcrumb_nav('<li class="current"><span> Author: Pat Lee</span>'));
    });

    it('ends the posts page with its title', function (): void {
        $html = breadcrumb_trail([
            'get_queried_object_id' => 12,
            'get_option' => '12',
            'get_the_title' => 'News',
        ]);

        expect($html)->toBe(breadcrumb_nav('<li class="current"><span> News </span>'));
    });

    it('prefers a 404 over a search and a search over an author archive', function (): void {
        expect(breadcrumb_trail(['is_404' => true, 'is_search' => true, 'is_author' => true]))
            ->toContain('Ooops!')->not->toContain('Search results')->not->toContain('Author:');

        expect(breadcrumb_trail(['is_search' => true, 'is_author' => true, 'get_search_query' => 'q']))
            ->toContain('Search results for q')->not->toContain('Author:');
    });
});

describe('a category archive', function (): void {
    it('ends with the category name', function (): void {
        $html = breadcrumb_trail([
            'is_category' => true,
            'get_query_var' => 3,
            'get_category' => (object) ['term_id' => 3],
            'get_cat_name' => 'Benefits',
        ]);

        expect($html)->toBe(breadcrumb_nav('<li class="current"><span>Benefits</span>'));
    });
});

describe('a post type archive', function (): void {
    it('ends with the post type menu name', function (): void {
        $html = breadcrumb_trail([
            'is_post_type_archive' => true,
            'get_post_type' => 'event',
            'get_post_type_object' => (object) ['labels' => (object) ['menu_name' => 'Events']],
        ]);

        expect($html)->toBe(breadcrumb_nav('<li class="current"><span>Events</span>'));
    });
});

describe('a taxonomy archive', function (): void {
    it('ends with the taxonomy label and term name', function (): void {
        $html = breadcrumb_trail([
            'is_tax' => true,
            'get_query_var' => 'x',
            'get_term_by' => (object) ['name' => 'Nursing'],
            'get_taxonomy' => (object) ['labels' => (object) ['name' => 'Departments']],
        ]);

        expect($html)->toBe(breadcrumb_nav('<li class="current"><span>Departments: Nursing</span>'));
    });

    it('ends with the term name alone inside a post type archive', function (): void {
        $html = breadcrumb_trail([
            'is_tax' => true,
            'is_post_type_archive' => true,
            'get_query_var' => 'x',
            'get_term_by' => (object) ['name' => 'Nursing'],
            'get_post_type' => 'event',
            'get_post_type_object' => (object) ['labels' => (object) ['menu_name' => 'Events']],
        ]);

        expect($html)->toBe(breadcrumb_nav('<li class="current"><span>Nursing</span>'));
    });
});

describe('a single post', function (): void {
    it('ends with the post title', function (): void {
        $html = breadcrumb_trail([
            'is_single' => true,
            'has_category' => false,
            'uams_is_custom_post_type' => false,
            'get_the_title' => 'Welcome',
        ]);

        expect($html)->toBe(breadcrumb_nav('<li class="current"><span>Welcome</span>'));
    });

    it('links the first category before the title', function (): void {
        $html = breadcrumb_trail([
            'is_single' => true,
            'has_category' => true,
            'get_the_category' => [(object) ['term_id' => 4], (object) ['term_id' => 5]],
            'get_category_link' => 'https://inside.uams.edu/category/news/',
            'get_cat_name' => 'News',
            'uams_is_custom_post_type' => false,
            'get_the_title' => 'Welcome',
        ]);

        expect($html)->toBe(breadcrumb_nav(
            '<li><a href="https://inside.uams.edu/category/news/" title="News">News</a>'
            .'<li class="current"><span>Welcome</span>'
        ));
    });

    it('links the custom post type archive when it has one', function (): void {
        $html = breadcrumb_trail([
            'is_single' => true,
            'has_category' => false,
            'uams_is_custom_post_type' => true,
            'get_post_type' => 'event',
            'get_post_type_object' => (object) [
                'query_var' => 'event',
                'labels' => (object) ['menu_name' => 'Events'],
                'rewrite' => ['slug' => 'events'],
            ],
            'get_post_type_archive_link' => 'https://inside.uams.edu/events/',
            'get_the_title' => 'Gala',
        ]);

        expect($html)->toBe(breadcrumb_nav(
            '<li><a href="https://inside.uams.edu/events/" title="Events">Events</a>'
            .'<li class="current"><span>Gala</span>'
        ));
    });

    it('falls back to the rewrite slug when the post type has no archive', function (): void {
        $html = breadcrumb_trail([
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
            'site_url' => 'https://inside.uams.edu/happenings/',
            'get_the_title' => 'Gala',
        ]);

        expect($html)->toBe(breadcrumb_nav(
            '<li><a href="https://inside.uams.edu/happenings/" title="Events">Events</a>'
            .'<li class="current"><span>Gala</span>'
        ));
    });

    it('passes the slug to site_url() wrapped in slashes', function (): void {
        $received = null;

        when('site_url')->alias(function (string $path) use (&$received): string {
            $received = $path;

            return 'https://inside.uams.edu'.$path;
        });

        breadcrumb_trail_with_slug_only();

        expect($received)->toBe('/happenings/');
    });

    it('adds no post type crumb when there is neither an archive nor a slug', function (): void {
        $html = breadcrumb_trail([
            'is_single' => true,
            'has_category' => false,
            'uams_is_custom_post_type' => true,
            'get_post_type' => 'event',
            'get_post_type_object' => (object) [
                'query_var' => 'event',
                'labels' => (object) ['menu_name' => 'Events'],
                'rewrite' => ['slug' => ''],
            ],
            'get_post_type_archive_link' => null,
            'get_the_title' => 'Gala',
        ]);

        expect($html)->toBe(breadcrumb_nav('<li class="current"><span>Gala</span>'));
    });
});

describe('a page', function (): void {
    /**
     * Stubs get_post() and get_permalink() for pages 10 (Parent) and 1 (Child).
     */
    $pages = static function (): void {
        $titles = [10 => 'Parent', 1 => 'Child'];

        when('get_post')->alias(fn (int $id): object => (object) ['ID' => $id, 'post_title' => $titles[$id]]);
        when('get_permalink')->alias(fn (int $id): string => "https://inside.uams.edu/page-$id/");
        when('esc_attr')->returnArg();
    };

    it('lists the ancestors as links, then the page as the current crumb', function () use ($pages): void {
        $pages();

        $html = breadcrumb_trail(['is_page' => true, 'get_post_ancestors' => [10]]);

        expect($html)->toBe(breadcrumb_nav(
            '<li><a href="https://inside.uams.edu/page-10/" title="Parent">Parent</a></li>'
            .'<li  class="current" ><span>Child</span></li>'
        ));
    });

    it('lists only the page itself when it has no ancestors', function () use ($pages): void {
        $pages();

        $html = breadcrumb_trail(['is_page' => true]);

        expect($html)->toBe(breadcrumb_nav('<li  class="current" ><span>Child</span></li>'));
    });

    it('reverses the ancestors WordPress returns nearest-first', function (): void {
        when('get_post')->alias(fn (int $id): object => (object) ['ID' => $id, 'post_title' => "P$id"]);
        when('get_permalink')->alias(fn (int $id): string => "/p$id/");
        when('esc_attr')->returnArg();

        $html = breadcrumb_trail(['is_page' => true, 'get_post_ancestors' => [20, 30]]);

        expect($html)->toBe(breadcrumb_nav(
            '<li><a href="/p30/" title="P30">P30</a></li>'
            .'<li><a href="/p20/" title="P20">P20</a></li>'
            .'<li  class="current" ><span>P1</span></li>'
        ));
    });

    it('adds no crumbs on the front page', function () use ($pages): void {
        $pages();

        expect(breadcrumb_trail(['is_page' => true, 'is_front_page' => true, 'get_post_ancestors' => [10]]))
            ->toBe(breadcrumb_nav(''));
    });

    it('still adds the page when it is the home view but not the front page', function () use ($pages): void {
        $pages();

        $html = breadcrumb_trail(['is_page' => true, 'is_home' => true]);

        expect($html)->toBe(breadcrumb_nav('<li  class="current" ><span>Child</span></li>'));
    });

    it('escapes the title attribute of an ancestor link', function (): void {
        when('get_post')->alias(fn (int $id): object => (object) ['ID' => $id, 'post_title' => $id === 10 ? 'A & B' : 'Child']);
        when('get_permalink')->justReturn('/x/');
        when('esc_attr')->alias(fn (string $text): string => htmlspecialchars($text));

        $html = breadcrumb_trail(['is_page' => true, 'get_post_ancestors' => [10]]);

        expect($html)->toContain('title="A &amp; B">A & B</a>');
    });
});

/**
 * A single post of a custom post type that has only a rewrite slug, for the
 * test that watches what is passed to site_url().
 */
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
