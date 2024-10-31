<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              http://azizulhasan.com
 * @since             1.0.0
 * @package           poll_system
 *
 * @wordpress-plugin
 * Plugin Name:       Poll And Vote System
 * Description:       Poll system in WordPress block enabled. Add a poll to post throw shortcode and get all poll throw rest API.
 * Version:           1.0.0
 * Author:            Azizul Hasan
 * Author URI:        http://azizulhasan.com
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       poll-and-vote-system
 * Domain Path:       /languages
 */
include 'vendor/autoload.php';

use PVSystem\Poll_System;
use PVSystem\Poll_System_Activator;
use PVSystem\Poll_System_Deactivator;
use PVSystem_Api\Poll_System_Api;

global $wpdb;
$wpdb->pvs_question = $wpdb->prefix . 'pvs_question';
$wpdb->pvs_answer = $wpdb->prefix . 'pvs_answer';
$wpdb->pvs_votes = $wpdb->prefix . 'pvs_votes';

// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

// Absolute path to the WordPress directory.
if (!defined('ABSPATH')) {
    define('ABSPATH', dirname(__FILE__) . '/');
}

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */

if (!defined('POLL_SYSTEM_VERSION')) {

    define('POLL_SYSTEM_VERSION', '1.0.0');
}

if (!defined('POLL_SYSTEM_NONCE')) {

    define('POLL_SYSTEM_NONCE', plugin_dir_url(__FILE__));
}

if (!defined('POLL_SYSTEM_TEXT_DOMAIN')) {

    define('POLL_SYSTEM_TEXT_DOMAIN', 'poll-and-vote-system');
}

if (!defined('POLL_SYSTEM_PLUGIN_DIR_URL')) {

    define('POLL_SYSTEM_PLUGIN_DIR_URL', plugin_dir_url(__FILE__));
}

// require_once 'Include/helpers.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */

class PVS_Init {

    public function __construct() {

        add_action('init', function () {
            global $current_user;
            new Poll_System_Api( $current_user );
        });
        $this->run_poll_system();
    }

    public function run_poll_system() {
        $plugin = new Poll_System();
        $plugin->run();
    }

    /**
     * The code that runs during plugin activation.
     * This action is documented in includes/Poll_System_Activator.php
     */
    public function activate_poll_system() {
        Poll_System_Activator::activate();
    }

    /**
     * The code that runs during plugin deactivation.
     * This action is documented in includes/Poll_System_Deactivator.php
     */
    public function deactivate_poll_system() {
        Poll_System_Deactivator::deactivate();
    }
}

$PVSystem = new PVS_Init();

register_activation_hook(__FILE__, [$PVSystem, 'activate_poll_system']);
register_deactivation_hook(__FILE__, [$PVSystem, 'deactivate_poll_system']);



// Add short code.
function create_poll_shortcode($attrs) {
    return get_shorcode_content( $attrs );
}
add_shortcode('pvs_poll', 'create_poll_shortcode');

