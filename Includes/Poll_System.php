<?php

namespace PVSystem;

use PVSystem\Poll_System_i18n;
use PVSystem\Poll_System_Loader;
use PVSystem_Admin\Poll_System_Admin;

/**
 * The file that defines the core plugin class
 *
 * A class definition that Includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       http://azizulhasan.com
 * @since      1.0.0
 *
 * @package    Poll_System
 * @subpackage Poll_System/Includes
 */

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    Poll_System
 * @subpackage Poll_System/Includes
 * @author     Azizul Hasan <azizulhasan.cr@gmail.com>
 */

class Poll_System {

    /**
     * The loader that's responsible for maintaining and registering all hooks that power
     * the plugin.
     *
     * @since    1.0.0
     * @access   protected
     * @var      Poll_System_Loader    $loader    Maintains and registers all hooks for the plugin.
     */
    protected $loader;

    /**
     * The unique identifier of this plugin.
     *
     * @since    1.0.0
     * @access   protected
     * @var      string    $plugin_name    The string used to uniquely identify this plugin.
     */
    protected $plugin_name;

    /**
     * The current version of the plugin.
     *
     * @since    1.0.0
     * @access   protected
     * @var      string    $version    The current version of the plugin.
     */
    protected $version;

    /**
     * Define the core functionality of the plugin.
     *
     * Set the plugin name and the plugin version that can be used throughout the plugin.
     * Load the dependencies, define the locale, and set the hooks for the admin area and
     * the public-facing side of the site.
     *
     * @since    1.0.0
     */
    public function __construct() {
        if (defined('POLL_SYSTEM_VERSION')) {
            $this->version = POLL_SYSTEM_VERSION;
        } else {
            $this->version = '1.0.0';
        }
        $this->plugin_name = 'wp-pvs-poll';

        $this->load_dependencies();
        $this->set_locale();
        $this->define_hooks();
    }

    /**
     * Load the required dependencies for this plugin.
     *
     * @since    1.0.0
     * @access   private
     */
    private function load_dependencies() {

        require_once plugin_dir_path(dirname(__FILE__)) . 'Includes/helpers.php';
        require_once plugin_dir_path(dirname(__FILE__)) . 'Includes/Poll_System_Hooks.php';
        new Poll_System_Hooks();
        $this->loader = new Poll_System_Loader();

    }

    /**
     * Define the locale for this plugin for internationalization.
     *
     * Uses the Poll_System_i18n class in order to set the domain and to register the hook
     * with WordPress.
     *
     * @since    1.0.0
     * @access   private
     */
    private function set_locale() {

        $plugin_i18n = new Poll_System_i18n();
        $this->loader->add_action('plugins_loaded', $plugin_i18n, 'load_plugin_textdomain');

    }

    /**
     * Register all of the hooks related to the admin area functionality
     * of the plugin.
     *
     * @since    1.0.0
     * @access   private
     */
    private function define_hooks() {

        $plugin_admin = new Poll_System_Admin($this->get_plugin_name(), $this->get_version());

        $this->loader->add_action('admin_enqueue_scripts', $plugin_admin, 'enqueue_styles');
        $this->loader->add_action('admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts');
        $this->loader->add_action('init', $plugin_admin, 'engueue_block_scripts');
        $this->loader->add_action('admin_menu', $plugin_admin, 'poll_system_menu');

    }

    /**
     * Run the loader to execute all of the hooks with WordPress.
     *
     * @since    1.0.0
     */
    public function run() {
        $this->loader->run();
    }

    /**
     * The name of the plugin used to uniquely identify it within the context of
     * WordPress and to define internationalization functionality.
     *
     * @since     1.0.0
     * @return    string    The name of the plugin.
     */
    public function get_plugin_name() {
        return $this->plugin_name;
    }

    /**
     * The reference to the class that orchestrates the hooks with the plugin.
     *
     * @since     1.0.0
     * @return    Poll_System_Loader    Orchestrates the hooks of the plugin.
     */
    public function get_loader() {
        return $this->loader;
    }

    /**
     * Retrieve the version number of the plugin.
     *
     * @since     1.0.0
     * @return    string    The version number of the plugin.
     */
    public function get_version() {
        return $this->version;
    }

}
