<?php

/**
 * Unit bootstrap for a theme.
 *
 * The plugin template's tests/bootstrap.php with one difference: it loads
 * functions.php, which a theme cannot avoid, instead of an includes/ directory
 * of definitions only. The Integration suite does not come through here; it has
 * its own Composer setup and bootstrap under tests/Integration.
 */

declare(strict_types=1);

use function Brain\Monkey\Functions\when;
use function Brain\Monkey\setUp;
use function Brain\Monkey\tearDown;

require_once __DIR__.'/../vendor/autoload.php';

require_once __DIR__.'/../vendor/antecedent/patchwork/Patchwork.php';

if (! defined('ABSPATH')) {
    define('ABSPATH', sys_get_temp_dir().'/inside-uams-tests-no-wordpress/');
}

/*
 * functions.php registers hooks and calls WordPress at file scope, so it is
 * loaded inside one Brain Monkey session with what it calls at load time
 * stubbed. The functions it defines outlive the session; the hooks it
 * registered are discarded by tearDown(), so every test starts clean.
 *
 * Known gap (UAMS-Web/inside-uams#40): the file-scope add_filter() for
 * `_includes/acf-pro/settings/show_admin` runs here, outside every test, so no
 * test notices if it is removed, and a mutation run without `--covered-only`
 * reports it as the one uncovered mutant. There is no Integration suite to
 * assert it on real WordPress; adding one (PHPUnit 9.6, wp-phpunit, MySQL and
 * a CI job) for a single `has_filter()` check costs more than the line is
 * worth, so the gap is left in place.
 *
 * Add a stub here for each WordPress function the theme's functions.php calls
 * at file scope (wp_get_theme(), get_stylesheet_directory_uri(), ...).
 */
setUp();

when('get_template_directory')->justReturn(dirname(__DIR__));
when('get_stylesheet_directory')->justReturn(dirname(__DIR__));

require_once __DIR__.'/../functions.php';
require_once __DIR__.'/../setup/class.uams-quicklinks.php';

tearDown();

// cspell:ignore ABSPATH stylesheet
