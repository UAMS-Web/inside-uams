<?php

declare(strict_types=1);

use function Brain\Monkey\Functions\when;

/**
 * Stubs the conditional tags a breadcrumb trail reads, all false except the
 * ones a test overrides afterward.
 */
function stub_breadcrumb_context(): void
{
    $GLOBALS['post'] = (object) ['ID' => 1];

    when('get_post_ancestors')->justReturn([]);
    when('is_main_site')->justReturn(true);
    when('is_front_page')->justReturn(false);
    when('is_404')->justReturn(false);
    when('is_search')->justReturn(false);
    when('is_author')->justReturn(false);
    when('get_queried_object_id')->justReturn(7);
    when('get_option')->justReturn('0');
    when('is_category')->justReturn(false);
    when('is_tax')->justReturn(false);
    when('is_single')->justReturn(false);
    when('is_post_type_archive')->justReturn(false);
    when('is_page')->justReturn(false);
}

afterEach(function (): void {
    unset($GLOBALS['post']);
});

it('starts every trail with the Inside UAMS home link inside the breadcrumb nav', function (): void {
    stub_breadcrumb_context();

    expect(get_uams_breadcrumbs())->toBe(
        "<nav class='uams-breadcrumbs' aria-label='breadcrumbs'><ul>"
        .'<li><a href="http://inside.uams.edu" title="University of Arkansas for Medical Scineces">Home</a></li>'
        .'</ul></nav>'
    );
});

it('ends a 404 trail with the current Ooops! item', function (): void {
    stub_breadcrumb_context();
    when('is_404')->justReturn(true);

    expect(get_uams_breadcrumbs())->toContain('<li class="current"><span>Ooops!</span>');
});
