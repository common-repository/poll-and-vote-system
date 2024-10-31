<?php
namespace PVSystem;

/**
 * Fired during plugin activation
 *
 * @link       http://azizulhasan.com
 * @since      1.0.0
 *
 * @package    Poll_System
 * @subpackage Poll_System/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    Poll_System
 * @subpackage Poll_System/includes
 * @author     Azizul Hasan <azizulhasan.cr@gmail.com>
 */
class Poll_System_Hooks {

    public function __construct() {
        // Hooks.
        add_action('wp_ajax_create_poll', 'handle_create_poll');
        add_action('wp_ajax_get_polls', 'handle_get_polls');
        add_action('wp_ajax_get_poll', 'handle_get_poll');
        add_action('wp_ajax_delete_poll', 'handle_delete_poll');
        add_action('wp_ajax_get_last_poll', 'handle_get_last_poll');
        add_action('wp_ajax_give_vote', 'handle_give_vote');
        add_action('wp_ajax_give_block_vote', 'handle_give_block_vote');
        // add_action('add_meta_boxes', array($this, 'add_custom_meta_box'));
    }

    /**
     * Register MetaBox to add PDF Download Button
     */
    public function add_custom_meta_box() {

        $meta_box_arr = [
            "post",
            "product",
            "page",
        ];
        $settings = (array) get_option('pvs_settings_data');
        $settings['pvs__settings_allow_recording_for_post_type'] = isset($settings['pvs__settings_allow_recording_for_post_type']) ? $settings['pvs__settings_allow_recording_for_post_type'] : ['all'];
        if (isset($settings['pvs__settings_allow_recording_for_post_type'])
            && in_array(get_current_screen()->post_type, $settings['pvs__settings_allow_recording_for_post_type'])
            || (in_array('all', $settings['pvs__settings_allow_recording_for_post_type'])
                && in_array(get_current_screen()->post_type, $meta_box_arr))) {
            add_meta_box(
                'simle-poll-meta-box',
                'Poll  System',
                array(
                    $this,
                    'pvs_meta_box',
                ),
                get_current_screen()->post_type,
                'side',
                'high',
                null
            );
        }

    }

    /**
     * Add meta box for record, re-record, listen content with loud.
     */
    public function pvs_meta_box() {

        $listening = (array) get_option('pvs_listening_settings');
        $listening = json_encode($listening);
        $customize = (array) get_option('pvs_customize_settings');

        // Button style.
        if (isset($customize) && count($customize)) {
            $btn_style = 'background-color:' . $customize['backgroundColor'] . ';color:' . $customize['color'] . ';border:0;';
        }
        $short_code = '[pvs_listen_btn]';
        if (isset($customize['pvs_play_btn_shortcode']) && '' != $customize['pvs_play_btn_shortcode']) {
            $short_code = $customize['pvs_play_btn_shortcode'];
        }
        ?>
        <div class="pvs_metabox">

            <button type="button" id="pvs__start__record"  style='<?php echo esc_attr($btn_style); ?>;cursor: pointer' onclick="startRecording()"><span class="dashicons dashicons-controls-volumeoff"></span>Start</button>
            <button type="button" id="pvs__listen_content" style='<?php echo esc_attr($btn_style); ?>;cursor: pointer' onclick='listenCotentInDashboard("pvs__listen_content","", <?php echo esc_js($listening); ?> )'><span class="dashicons dashicons-controls-play"></span> Play</button>
            <!-- Shortcode text -->
            <input
                type="text"
                name="pvs_play_btn_shortcode"
                id="pvs_play_btn_shortcode"
                value="<?php echo esc_attr($short_code) ?>"
                title="Short code"
            />

            <!-- Copy Button -->
            <button type="button" style='<?php echo esc_attr($btn_style); ?>;cursor: copy;margin-top:10px;padding:6px;' onclick="copyshortcode()">
            <span class="dashicons dashicons-admin-page"></span>
            </button>

            <script>
            /**
             * Copy short Code
             */
            function copyshortcode () {
                /* Get the text field */
                var copyText = document.getElementById("pvs_play_btn_shortcode");

                /* Copy the text inside the text field */
                navigator.clipboard.writeText(copyText.value);

                /* Alert the copied text */
                alert("Copied the text: " + copyText.value);
            };
            </script>
        </div>
        <?php
}

}
