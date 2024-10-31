<?php
namespace PVSystem;
/**
 * Define the internationalization functionality
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @link       http://azizulhasan.com
 * @since      1.0.0
 *
 * @package    Poll_System
 * @subpackage Poll_System/includes
 */

/**
 * Define the internationalization functionality.
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @since      1.0.0
 * @package    Poll_System
 * @subpackage Poll_System/includes
 * @author     Azizul Hasan <azizulhasan.cr@gmail.com>
 */
class Poll_System_i18n {

    /**
     * Load the plugin text domain for translation.
     *
     * @since    1.0.0
     */
    public function load_plugin_textdomain() {

        load_plugin_textdomain(
            'wp-pvs-poll',
            false,
            dirname(dirname(plugin_basename(__FILE__))) . '/languages/'
        );

    }

}
