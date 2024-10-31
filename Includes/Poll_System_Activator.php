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
class Poll_System_Activator {

    /**
     * Short Description. (use period)
     *
     * Long Description.
     *
     * @since    1.0.0
     */
    public static function activate() {
        global $wpdb;
        $wpdb->pvs_question = $wpdb->prefix . 'pvs_question';
        $wpdb->pvs_answer = $wpdb->prefix . 'pvs_answer';
        $wpdb->pvs_votes = $wpdb->prefix . 'pvs_votes';

        if (@is_file(ABSPATH . '/wp-admin/includes/upgrade.php')) {
            include_once ABSPATH . '/wp-admin/includes/upgrade.php';
        } elseif (@is_file(ABSPATH . '/wp-admin/upgrade-functions.php')) {
            include_once ABSPATH . '/wp-admin/upgrade-functions.php';
        } else {
            die('We have problem finding your \'/wp-admin/upgrade-functions.php\' and \'/wp-admin/includes/upgrade.php\'');
        }

        // Create Poll Tables (3 Tables)
        $charset_collate = $wpdb->get_charset_collate();

        $create_table = array();
        $create_table['pvs_question'] = "CREATE TABLE $wpdb->pvs_question (" .
            "pvs_qid int(10) NOT NULL auto_increment," .
            "pvs_question varchar(200) character set utf8 NOT NULL default ''," .
            "pvs_timestamp varchar(20) NOT NULL default ''," .
            "pvs_totalvotes int(10) NOT NULL default '0'," .
            "PRIMARY KEY  (pvs_qid)" .
            ") $charset_collate;";
        $create_table['pvs_answer'] = "CREATE TABLE $wpdb->pvs_answer (" .
            "pvs_aid int(10) NOT NULL auto_increment," .
            "pvs_qid int(10) NOT NULL default '0'," .
            "pvs_answers varchar(200) character set utf8 NOT NULL default ''," .
            "pvs_votes int(10) NOT NULL default '0'," .
            "PRIMARY KEY  (pvs_aid)" .
            ") $charset_collate;";

        $create_table['pvs_votes'] = "CREATE TABLE $wpdb->pvs_votes (" .
            "pvs_vid int(10) NOT NULL auto_increment," .
            "pvs_qid int(10) NOT NULL default '0'," .
            "pvs_aid int(10) NOT NULL default '0'," .
            "pvs_ip varchar(100) NOT NULL default ''," .
            "pvs_host VARCHAR(200) NOT NULL default ''," .
            "pvs_timestamp int(10) NOT NULL default '0'," .
            "pvs_user tinytext NOT NULL," .
            "pvs_userid int(10) NOT NULL default '0'," .
            "PRIMARY KEY  (pvs_vid)," .
            "KEY pvs_ip (pvs_ip)," .
            "KEY pvs_qid (pvs_qid)," .
            "KEY pvs_ip_qid (pvs_ip, pvs_qid)" .
            ") $charset_collate;";

        if (!in_array('pvs_question', $wpdb->tables)) {
            dbDelta($create_table['pvs_question']);

        }
        if (!in_array('pvs_answer', $wpdb->tables)) {
            dbDelta($create_table['pvs_answer']);

        }
        if (!in_array('pvs_votes', $wpdb->tables)) {
            dbDelta($create_table['pvs_votes']);
        }

        // Check Whether It is Install Or Upgrade
        $first_poll = $wpdb->get_var("SELECT pvs_qid FROM $wpdb->pvs_question LIMIT 1");
        // If Install, Insert 1st Poll Question With 5 Poll Answers
        if (empty($first_poll)) {
            // Insert Poll Question (1 Record)
            $insert_poll_qid = $wpdb->insert($wpdb->pvs_question, array('pvs_question' => __('How Is My Site?', 'poll-and-vote-system'), 'pvs_timestamp' => current_time('timestamp')), array('%s', '%s'));
            if ($insert_poll_qid) {
                // Insert Poll Answers  (3 Records)
                $wpdb->insert($wpdb->pvs_answer, array('pvs_qid' => $insert_poll_qid, 'pvs_answers' => __('Good', 'poll-and-vote-system')), array('%d', '%s'));
                $wpdb->insert($wpdb->pvs_answer, array('pvs_qid' => $insert_poll_qid, 'pvs_answers' => __('Excellent', 'poll-and-vote-system')), array('%d', '%s'));
                $wpdb->insert($wpdb->pvs_answer, array('pvs_qid' => $insert_poll_qid, 'pvs_answers' => __('Bad', 'poll-and-vote-system')), array('%d', '%s'));
            }
        }

    }

}
