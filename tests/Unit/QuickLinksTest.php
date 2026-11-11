<?php

declare(strict_types=1);

use Brain\Monkey\Functions;

use function Brain\Monkey\Functions\stubTranslationFunctions;
use function Brain\Monkey\Functions\when;

/**
 * An Institute_QuickLinks built without its constructor, so no hook is
 * registered and the methods under test run on a bare instance.
 */
function quicklinks_instance(): Institute_QuickLinks
{
    return (new ReflectionClass(Institute_QuickLinks::class))->newInstanceWithoutConstructor();
}

/**
 * A nav menu item as wp_get_nav_menu_items() returns it, with extra fields the
 * feed must drop.
 *
 * @param  array<int, string>  $classes
 */
function quicklink_item(int $id, array $classes = ['']): object
{
    return (object) [
        'ID' => $id,
        'title' => "Link $id",
        'url' => "https://inside.uams.edu/$id/",
        'classes' => $classes,
        'menu_item_parent' => '0',
        'db_id' => $id,
        'description' => 'dropped',
    ];
}

// Pins Institute_QuickLinks: the hooks it registers, the menu it registers, and
// the JSON feed it builds from the menu items.
it('hooks the menu registration and both AJAX feeds from its constructor', function (): void {
    $links = new Institute_QuickLinks;

    expect(has_action('after_setup_theme', [$links, 'register_quick_links_menu']))->toBe(10)
        ->and(has_action('wp_ajax_quicklinks', [$links, 'uams_quicklinks_feed']))->toBe(10)
        ->and(has_action('wp_ajax_nopriv_quicklinks', [$links, 'uams_quicklinks_feed']))->toBe(10);
});

it('registers the Quick Links nav menu location', function (): void {
    stubTranslationFunctions();
    Functions\expect('register_nav_menu')->once()->with('quick-links', 'Quick Links');

    quicklinks_instance()->register_quick_links_menu();
});

describe('the feed', function (): void {
    it('reads the items of the menu assigned to the location', function (): void {
        when('get_nav_menu_locations')->justReturn(['quick-links' => 42]);
        Functions\expect('wp_get_nav_menu_items')->once()->with(42)->andReturn([quicklink_item(1)]);
        Functions\expect('wp_send_json')->once()->with([
            ['ID' => 1, 'title' => 'Link 1', 'url' => 'https://inside.uams.edu/1/', 'classes' => false, 'menu_item_parent' => '0'],
        ]);

        quicklinks_instance()->uams_quicklinks_feed();
    });

    it('falls back to the menu named after the location', function (): void {
        when('get_nav_menu_locations')->justReturn(['primary' => 5]);
        Functions\expect('wp_get_nav_menu_object')->once()->with('quick-links')->andReturn((object) ['term_id' => 77]);
        Functions\expect('wp_get_nav_menu_items')->once()->with(77)->andReturn([quicklink_item(2)]);
        Functions\expect('wp_send_json')->once()->with([
            ['ID' => 2, 'title' => 'Link 2', 'url' => 'https://inside.uams.edu/2/', 'classes' => false, 'menu_item_parent' => '0'],
        ]);

        quicklinks_instance()->uams_quicklinks_feed();
    });

    it('sends an empty list when no menu exists', function (): void {
        when('get_nav_menu_locations')->justReturn([]);
        when('wp_get_nav_menu_object')->justReturn(false);

        Functions\expect('wp_get_nav_menu_items')->never();
        Functions\expect('wp_send_json')->once()->with([]);

        quicklinks_instance()->uams_quicklinks_feed();
    });
});

describe('parse_menu', function (): void {
    it('returns an empty list before any items are loaded', function (): void {
        expect(quicklinks_instance()->parse_menu())->toBe([]);
    });

    it('returns an empty list when the menu has no items', function (): void {
        when('get_nav_menu_locations')->justReturn(['quick-links' => 1]);
        when('wp_get_nav_menu_items')->justReturn([]);
        when('wp_send_json')->justReturn();

        $links = quicklinks_instance();
        $links->uams_quicklinks_feed();

        expect($links->parse_menu())->toBe([]);
    });

    it('returns an empty list when the menu lookup yields false', function (): void {
        when('get_nav_menu_locations')->justReturn(['quick-links' => 1]);
        when('wp_get_nav_menu_items')->justReturn(false);
        when('wp_send_json')->justReturn();

        $links = quicklinks_instance();
        $links->uams_quicklinks_feed();

        expect($links->parse_menu())->toBe([]);
    });

    it('keeps only the five feed keys, in menu order', function (): void {
        when('get_nav_menu_locations')->justReturn(['quick-links' => 1]);
        when('wp_get_nav_menu_items')->justReturn([quicklink_item(1, ['a', 'b']), quicklink_item(2, ['c'])]);
        when('wp_send_json')->justReturn();

        $links = quicklinks_instance();
        $links->uams_quicklinks_feed();

        expect($links->parse_menu())->toBe([
            ['ID' => 1, 'title' => 'Link 1', 'url' => 'https://inside.uams.edu/1/', 'classes' => ['a', 'b'], 'menu_item_parent' => '0'],
            ['ID' => 2, 'title' => 'Link 2', 'url' => 'https://inside.uams.edu/2/', 'classes' => ['c'], 'menu_item_parent' => '0'],
        ]);
    });

    it('replaces an empty first class with false', function (): void {
        when('get_nav_menu_locations')->justReturn(['quick-links' => 1]);
        when('wp_get_nav_menu_items')->justReturn([quicklink_item(1, ['']), quicklink_item(2, ['0'])]);
        when('wp_send_json')->justReturn();

        $links = quicklinks_instance();
        $links->uams_quicklinks_feed();

        $menu = $links->parse_menu();

        expect($menu[0]['classes'])->toBeFalse()
            ->and($menu[1]['classes'])->toBeFalse();
    });
});
