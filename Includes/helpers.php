<?php

function pvs_log($data) {
    error_log(print_r($data, true));
}



function get_shorcode_content( $attrs ) {
    $poll_id        = isset($attrs['id']) ? esc_attr( $attrs['id'] ) : null;
    $custom_class   = isset( $attrs['customclass'] ) ? esc_attr( $attrs['customclass'] ) : null;
    $custom_css     = isset( $attrs['customcss'] ) ? esc_attr( $attrs['customcss'] ) : null;

    $poll = get_single_poll($poll_id);
    if (isset($poll[0])) {
        $poll                       = $poll[0];
        $current_answer_id          = get_current_user_answer_id($poll['id']);
        $poll['current_answer_id']  = $current_answer_id;

    } else {

        $str = "<div class='poll_system_block'><h3>There is no poll.</h3></div>";
        $arr = array(
            'h3'  => array(),
            'div' => array(  'class' => array() )
        );
        echo wp_kses($str, $arr);
        return;
    }
    ob_start();
    ?>
    <div class="poll_system_block <?php echo esc_attr( $custom_class ) ?> " id="pvs_block_<?php echo esc_attr( $poll['id'] ); ?>">
        <?php do_action('pvs_before_question', $poll, $poll['question'])?>
        <div class="poll_question"><?php echo esc_html__($poll['question'], 'poll-and-vote-system') ?></div>
        <?php do_action('pvs_after_question', $poll,  $poll['question'])?>
        <div class='poll_answers'>
        <?php
        do_action('pvs_before_answer',  $poll, $poll['answers']);
        $poll_answers = apply_filters('pvse_poll_answers', $poll['answers']);
        if (($poll['answers'])) {
            foreach ($poll_answers as $answer) {
                $json_answer        = json_encode($answer);
                json_decode( $json_answer );
                if ( json_last_error() !== JSON_ERROR_NONE ) {
                    $json_answer = '{}';
                }
                $totalvotes         = intval( $poll["totalvotes"] );
                $current_answer_id  = intval( $poll["current_answer_id"] );
                ?>
                    <input
                        type='radio'
                        name='pvs_answers'
                        value="<?php echo esc_attr( $answer->pvs_answers ) ?>"
                        <?php echo ($answer->pvs_aid == $poll['current_answer_id'] ) ? "checked" : ''; ?>
                        onchange='submitVote(<?php echo $json_answer ?>,<?php echo $totalvotes ?>,<?php echo $current_answer_id;?>)'
                        id="pvs_answers_<?php echo esc_attr(  $answer->pvs_aid ); ?>"/>
                    <label for="answer.pvs_answers_<?php echo esc_attr( $answer->pvs_aid ); ?>">
                        <?php echo esc_html__($answer->pvs_answers, 'poll-and-vote-system') ?>;
                    </label>
                    <?php
                }
            }   
            do_action('pvs_after_answer', $poll,  $poll['answers']);
            ?>
		</div>
	</div>
    
    <?php 
    $allowed_html = array( 
        'style' => array()
    );
    if( $custom_css ) {
        $custom_css = '#pvs_block_'.$poll_id.trim($custom_css);
        echo wp_kses( "<style>$custom_css</style>" , $allowed_html );
    }else{
        echo wp_kses( "<style>#pvs_block_$poll_id.poll_system_block .poll_question{font-size:20px;} </style>", $allowed_html );
    }
    $poll = [];
    return ob_get_clean();
}



function pvs_verify_request($data) {
    if (wp_verify_nonce($data['nonce'], POLL_SYSTEM_NONCE)
        && check_ajax_referer(POLL_SYSTEM_NONCE, 'nonce')
        && is_user_logged_in()
        && current_user_can('administrator')
    ) {
        return true;
    }

    return false;
}

function pvs_verify_nonce($data) {
    if (wp_verify_nonce($data['nonce'], POLL_SYSTEM_NONCE)
        && check_ajax_referer(POLL_SYSTEM_NONCE, 'nonce')
    ) {
        return true;
    }

    return false;
}

// save poll.
function handle_create_poll() {
    global $wpdb;
    $response['status'] = true;
    if (pvs_verify_request($_POST)) {

        $question = isset($_POST['question']) ? sanitize_text_field($_POST['question']) : '';

        // update question if id exists.
        if (isset($_POST['id'])) {
            $post_id = intval( $_POST['id'] );
            $res = $wpdb->update($wpdb->pvs_question, array('pvs_qid' => $post_id, 'pvs_question' => __($question, 'poll-and-vote-system')), array('pvs_qid' => $post_id ) );

            //TODO update all answers;
            // if (isset($_POST['question_answers'])) {
            //     $answers = explode(',', $_POST['question_answers']);
            //     $results = $wpdb->get_results("SELECT pvs_aid, pvs_qid, pvs_answers FROM $wpdb->pvs_answer WHERE pvs_qid = $uid ");
            // }
            $response['data'] = get_polls_data();

        } else {

            $res = $wpdb->insert($wpdb->pvs_question, array('pvs_question' => __($question, 'poll-and-vote-system'), 'pvs_timestamp' => current_time('timestamp'), 'pvs_totalvotes' => 0), array('%s', '%s', '%d'));

            $qid = $wpdb->insert_id;
            if (isset($_POST['question_answers'])) {
                $answers = sanitize_text_field( $_POST['question_answers'] );
                $answers = explode(',', $answers);
                foreach ($answers as $answer) {
                    $answer = sanitize_text_field($answer);
                    $wpdb->insert($wpdb->pvs_answer, array('pvs_qid' => $qid, 'pvs_answers' => __($answer, 'poll-and-vote-system'), 'pvs_votes' => 0), array('%d', '%s', '%d'));
                }
            }

            if ($res) {
                $response['data'] = get_polls_data();
            } else {
                $response['data'] = $GLOBALS['wp_query']->request;
            }
        }

    } else {
        $response['status'] = false;
        $response['data']   = __('Nonce verified request');
    }

    echo json_encode($response);
    wp_die();
}

// Get all poll data.
function handle_get_polls() {
    $response['status'] = true;
    if (pvs_verify_request($_POST)) {
        $response['data'] = get_polls_data();
    } else {
        $response['status'] = false;
        $response['data']   = __('Nonce verified request');
    }

    echo json_encode($response);
    wp_die();
}

// Get poll data.
function handle_get_poll() {
    $response['status'] = true;
    if (pvs_verify_nonce($_POST)) {
        $id = isset($_POST['id']) ? intval( $_POST['id'] ) : null;
        $response['data'] = get_single_poll($id);
    } else {
        $response['status'] = false;
        $response['data']   = __('Nonce verified request');
    }

    echo json_encode($response);
    wp_die();
}

// Delete single poll
function handle_delete_poll() {
    global $wpdb;
    $response['status'] = true;
    if (pvs_verify_request($_POST)) {
        $id = intval( $_POST['id'] );
        $wpdb->delete($wpdb->pvs_question, array('pvs_qid' => $id), array('%d'));

        $wpdb->query("DELETE FROM $wpdb->pvs_answer WHERE pvs_qid = $id ");

        $response['data'] = get_polls_data();
    } else {
        $response['status'] = false;
        $response['data']   = __('Nonce verified request');
    }

    echo json_encode($response);
    wp_die();
}

// Get all polls data.
function get_polls_data() {
    global $wpdb;
    $questions = $wpdb->get_results("SELECT * from $wpdb->pvs_question");
    foreach ($questions as $question) {
        $answers = $wpdb->get_results("SELECT * from $wpdb->pvs_answer WHERE  pvs_qid = $question->pvs_qid");
        $data[] = [
            'id' => $question->pvs_qid,
            'question' => $question->pvs_question,
            'answers' => $answers,
            'totalvotes' => $question->pvs_totalvotes,
        ];
    }

    return $data;
}


// Get single poll.
function get_single_poll($question_id = null) {
    global $wpdb;
    if ($question_id) {
        $questions = $wpdb->get_results("SELECT * from $wpdb->pvs_question WHERE pvs_qid = $question_id");
    } else {
        $questions = $wpdb->get_results("SELECT * from $wpdb->pvs_question  ORDER BY pvs_qid DESC LIMIT 1");
    }

    $data = [];
    if (count($questions)) {
        foreach ($questions as $question) {
            $answers = $wpdb->get_results("SELECT * from $wpdb->pvs_answer WHERE  pvs_qid = $question->pvs_qid");
            $data[] = [
                'id' => $question->pvs_qid,
                'question' => $question->pvs_question,
                'answers' => $answers,
                'totalvotes' => $question->pvs_totalvotes,
            ];
        }
    }

    return $data;
}



function get_current_user_answer_id($qid) {
    global $wpdb;
    $ip = sanitize_text_field( $_SERVER['REMOTE_ADDR'] );
    $vote = $wpdb->get_results("SELECT * from $wpdb->pvs_votes WHERE pvs_ip = '" . $ip . "' AND pvs_qid = $qid LIMIT 1");
    if (isset($vote[0])) {
        return $vote[0]->pvs_aid;
    }

    return '';
}

// Give vote.
function handle_give_vote() {
    global $wpdb;
    global $current_user;
    $response['status'] = true;
    if (pvs_verify_nonce($_POST)) {

        $ip         = sanitize_text_field( $_SERVER['REMOTE_ADDR'] );
        $host       = sanitize_text_field( $_SERVER['HTTP_HOST'] );
        $qid        = intval( $_POST['pvs_qid'] );
        $aid        = intval( $_POST['pvs_aid'] );
        $totalvotes = absint( $_POST['totalvotes'] );

        if (is_user_logged_in()) { // current logged in user.
            global $current_user;
            $username = $current_user->data->user_login;
            $id = $current_user->ID;

            // if current user already given answer then update the answer. else insert answer.
            $vote = $wpdb->get_results("SELECT * from $wpdb->pvs_votes WHERE pvs_user = '" . $username . "' AND pvs_ip = '" . $ip . "' AND pvs_qid = $qid LIMIT 1");
            if (count($vote) && $vote[0]->pvs_aid != $aid) {

                $wpdb->update($wpdb->pvs_votes,
                    array(
                        'pvs_aid'       => $aid,
                        'pvs_timestamp' => current_time('timestamp')),

                    array('pvs_vid' => $vote[0]->pvs_vid));

                update_vote($wpdb, $vote, $aid);

            } else if (count($vote) == 0) {
                $wpdb->insert($wpdb->pvs_votes,
                    array(
                        'pvs_qid'       => $qid,
                        'pvs_aid'       => $aid,
                        'pvs_ip'        => $ip,
                        'pvs_host'      => $host,
                        'pvs_timestamp' => current_time('timestamp'),
                        'pvs_user'      => $username,
                        'pvs_userid'    => $id,
                    ),
                    array('%d', '%d', '%s', '%s', '%s', '%s', '%d'));

                // update total vote.
                $totalvotes = ++$totalvotes;
                $wpdb->update($wpdb->pvs_question, array('pvs_qid' => $qid, 'pvs_totalvotes' => (string) $totalvotes), array('pvs_qid' => $qid));

                update_vote($wpdb, $vote, $aid);

            }

        } else { // geust user.
            // if current user already given answer then update the answer. else insert answer.
            $vote = $wpdb->get_results("SELECT * from $wpdb->pvs_votes WHERE pvs_ip = '" . $ip . "' AND pvs_qid = $qid LIMIT 1");
            if (count($vote) && $vote[0]->pvs_aid != $aid) {
                $wpdb->update($wpdb->pvs_votes,
                    array(
                        'pvs_aid'       => $aid,
                        'pvs_timestamp' => current_time('timestamp')),

                    array('pvs_vid' => $vote[0]->pvs_vid));
                update_vote($wpdb, $vote, $aid);
            } else if (count($vote) == 0) {
                $wpdb->insert($wpdb->pvs_votes,
                    array(
                        'pvs_qid'       => $qid,
                        'pvs_aid'       => $aid,
                        'pvs_ip'        => $ip,
                        'pvs_host'      => $host,
                        'pvs_timestamp' => current_time('timestamp'),
                        'pvs_user'      => '',
                        'pvs_userid'    => ''),

                    array('%d', '%d', '%s', '%s', '%s', '%s', '%d'));

                // update total vote.
                $totalvotes = ++$totalvotes;
                $wpdb->update($wpdb->pvs_question, array('pvs_qid' => $qid, 'pvs_totalvotes' => (string) $totalvotes), array('pvs_qid' => $qid));

                update_vote($wpdb, $vote, $aid);
            }
        }

        $response['data'] = true;

    } else {
        $response['status'] = false;
        $response['data']   = __('Nonce verified request');
    }

    echo json_encode($response);
    wp_die();
}

function update_vote($wpdb, $vote, $aid) {
    // Decrease vote from previous answer.
    if (isset($vote[0])) {
        $prev_aid = $vote[0]->pvs_aid;
        $previous_answer = $wpdb->get_results("SELECT * from $wpdb->pvs_answer WHERE pvs_aid = $prev_aid LIMIT 1");
        $previous_answer_votes = (int) $previous_answer[0]->pvs_votes;
        if ($previous_answer_votes) {
            $previous_answer_votes = --$previous_answer_votes;
            $wpdb->update($wpdb->pvs_answer,
                array(
                    'pvs_votes' => (string) $previous_answer_votes),
                array('pvs_aid' => $prev_aid));
        }
    }

    // increase current answer votes.
    $current_answer = $wpdb->get_results("SELECT * from $wpdb->pvs_answer WHERE pvs_aid = $aid LIMIT 1");
    if (isset($current_answer[0])) {
        $current_answer_votes = (int) $current_answer[0]->pvs_votes;
        if ($current_answer_votes) {
            $current_answer_votes = ++$current_answer_votes;
            $wpdb->update($wpdb->pvs_answer,
                array(
                    'pvs_votes' => (string) $current_answer_votes),
                array('pvs_aid' => $prev_aid));
        }
    }
}
