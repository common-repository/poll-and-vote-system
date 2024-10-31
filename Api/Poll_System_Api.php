<?php
namespace PVSystem_Api;



/**
 * This class is for getting all plugin's data  through api.
 * This is applied for tracker menu.
 * @since      1.0.0
 * @package    Poll_System
 * @subpackage Poll_System/api
 * @author     Azizul Hasan <azizulhasan.cr@gmail.com>
 */
class Poll_System_Api {

    protected $namespace;
    protected $woocommerce;
    protected $version;
    public $current_user;

    public function __construct(    $current_user = null ) {
        
        $this->version = 'v1';
        $this->namespace = 'pvs/' . $this->version;
        $this->current_user = $current_user;
        add_action('rest_api_init', [ $this, 'pvs_speech_register_routes' ] );
    }

    /**
     * Register Routes
     */
    public function pvs_speech_register_routes() {
        // Register polls route.
        register_rest_route(
            $this->namespace,
            '/polls',
            array(
                array(
                    'methods'               => \WP_REST_Server::READABLE,
                    'callback'              => array( $this, 'pvs_get_polls'),
                    'permission_callback'   => array( $this, 'get_route_access'),
                    'args'                  => array(),
                ),
            )
        );

        // Register single poll route.
        register_rest_route(
            $this->namespace,
            '/poll/(?P<id>[\d]+)',
            array(
                array(
                    'methods'               => \WP_REST_Server::READABLE,
                    'callback'              => array( $this, 'pvs_get_single_poll'),
                    'permission_callback'   => array( $this, 'get_route_access'),
                    'args'                  => array(),
                ),
            )
        );

        // Register single poll route.
        register_rest_route(
            $this->namespace,
            '/poll/(?P<id>[\d]+)/votes',
            array(
                array(
                    'methods'               => \WP_REST_Server::READABLE,
                    'callback'              => array( $this, 'pvs_get_question_votes'),
                    'permission_callback'   => array( $this, 'get_route_access'),
                    'args'                  => array(),
                ),
            )
        );

        // Register votes route.
        register_rest_route(
            $this->namespace,
            '/votes',
            array(
                array(
                    'methods'               => \WP_REST_Server::READABLE,
                    'callback'              => array( $this, 'pvs_get_votes'),
                    'permission_callback'   => array( $this, 'get_route_access'),
                    'args'                  => array(),
                ),
            )
        );

    }
    /**
     * Manage polls data.
     */
    public function pvs_get_polls( $request ) {
        $response['status'] = true;
        $response['data'] = get_polls_data();

        return rest_ensure_response( $response );
    }

    /**
     * Manage single poll data.
     */
    public function pvs_get_single_poll( $request ) {

        $id = $request->get_params()['id'];
        
        $response['status'] = true;
        $response['data'] = get_single_poll( $id );

        return rest_ensure_response( $response );
    }

    /**
     * Manage single poll vote data.
     */
    public function pvs_get_question_votes( $request ) {
        global $wpdb;
        $wpdb->pvs_votes = $wpdb->prefix . 'pvs_votes';
        $id = $request->get_params()['id'];
        
        $response['status'] = true;
        $poll = get_single_poll( $id );
        $voters = $wpdb->get_results("SELECT * from $wpdb->pvs_votes WHERE pvs_qid=$id");

        $response['data'] = [
            'totalvotes' => $poll[0]['totalvotes'],
            'voters'     => $voters,
            
        ];

        return rest_ensure_response( $response );
    }

    /**
     * Manage single poll vote data.
     */
    public function pvs_get_votes( $request ) {
        global $wpdb;
        $wpdb->pvs_votes = $wpdb->prefix . 'pvs_votes';
        
        $response['status'] = true;
        $vote = $wpdb->get_results("SELECT * from $wpdb->pvs_votes");

        $response['data'] = $vote;

        return rest_ensure_response( $response );
    }


   
    /*
     * Get route access if request is valid.
     */

    public function get_route_access() {

        return true;

    }
}
