<?php

/**
 * The bbPress Plugin
 *
 * bbPress is forum software with a twist from the creators of WordPress.
 *
 * $Id$
 *
 * @package bbPress
 * @subpackage Main
 */

/**
 * Plugin Name:       bbPress
 * Plugin URI:        https://bbpress.org
 * Description:       bbPress is forum software with a twist from the creators of WordPress.
 * Author:            The bbPress Contributors
 * Author URI:        https://bbpress.org
 * License:           GNU General Public License v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       bbpress
 * Domain Path:       /languages/
 * Requires PHP:      5.6.20
 * Requires at least: 6.0
 * Tested up to:      6.5
 * Version:           2.7.0-alpha-2
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

// Assume you want to load from build
$bbp_loader = __DIR__ . '/build/bbpress.php';

// Load from source if no build exists
if ( ! file_exists( $bbp_loader ) || defined( 'BBP_LOAD_SOURCE' ) ) {
	$bbp_loader = __DIR__ . '/src/bbpress.php';
}

// Include bbPress
include $bbp_loader;

// Unset the loader, since it's loaded in global scope
unset( $bbp_loader );
