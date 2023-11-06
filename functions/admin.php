<?php
	function consults_meta_box() {
	    add_meta_box(
	        'consult_messages_meta_box', // Meta Box ID
	        'Consult Messages', // Title of the Meta Box
	        'consults_meta_box_callback', // Callback defining the plugin's innards
	        'consults', // Screen to which to add the meta box
	        'normal' // Context
	    );
	}
	add_action('add_meta_boxes', 'consults_meta_box');

	function consults_meta_box_callback($post) {
	    // get post ID
	    $consult_id = $post->ID;

	    // query for "consult_messages" with "consult_id" equal to current post's ID
	    $consult_messages = new WP_Query(array(
	        'post_type' => 'consult_messages',
	        'meta_key' => 'consult_id',
	        'meta_value' => $consult_id,
	        'orderby' => 'date', // Order by post date
        	'order' => 'ASC' // ASC for oldest to newest, DESC for newest to oldest
	    ));

	    if($consult_messages->have_posts()) : 
	        while($consult_messages->have_posts()) : 
	            $consult_messages->the_post();
	            $message = get_field('message');
		        $submitted_by = get_field('submitted_by');
		        $user = get_user_by( 'ID', $submitted_by );
		        $date_published = get_the_date('M jS, Y g:ia');
	            // Here you can format how you want the consult messages to appear
	            echo '<p>'.$message.'</p>';
	            echo '<p>'.$user->display_name.'<br />';
	            echo $date_published .'</p>';
	            echo '<hr>';
	        endwhile;
	    else: 
	    	echo '<p style="text-align: center;">No Messages</p>';
	    endif; 
	    wp_reset_postdata();
	}

	function consults_history_meta_box() {
	    add_meta_box(
	        'consult_history_meta_box', // Meta Box ID
	        'History Log', // Title of the Meta Box
	        'consults_history_meta_box_callback', // Callback defining the plugin's innards
	        'consults', // Screen to which to add the meta box
	        'normal' // Context
	    );
	}
	add_action('add_meta_boxes', 'consults_history_meta_box');

	function consults_history_meta_box_callback($post) {
	    // get post ID
	    $consult_id = $post->ID;

	    // query for "consult_history" with "consult_id" equal to current post's ID
	    $consult_history = new WP_Query(array(
	        'post_type' => 'consult_history',
	        'meta_key' => 'consult_id',
	        'meta_value' => $consult_id,
	        'orderby' => 'date', // Order by post date
	        'order' => 'ASC' // ASC for oldest to newest, DESC for newest to oldest
	    ));

	    if($consult_history->have_posts()) : 
	        while($consult_history->have_posts()) : 
	            $consult_history->the_post();
	            $history_log = get_field('history_log');
	            $output = '';
	            $mod_only_notes_exist = false;
	    		$history_exists = false; // New variable to track if any history exists
	            if (have_rows('history_log')) {
	                while (have_rows('history_log')) {
	                	the_row();

	                    $user = get_sub_field('user');
	                    $action_taken = get_sub_field('action_taken');
	                    $date_time = get_sub_field('date_time');
	                    $date_time_formatted = date_i18n( 'M j, Y \a\t g:ia', $date_time );
	                    $mod_only = get_sub_field('mod_only');
	                    $note = get_sub_field('message');

	                    $row_id = get_row_index();

	                    // check if mod_only is true and user is not admin or support_agent
	                    if ($mod_only) {
	                    	$mod_only_notes_exist = true;
	                    	$history_exists = true; // Update history_exists to true
	                    	if($note){
	                    		$output .= '<li><strong>' . $user['display_name'] . '</strong> ' . $action_taken . ' - <span>' . $date_time_formatted . ' (Moderator Only)</span>';
	                    		$output .= '<div>'.$note.'</div>';
	                    		$output .= '</li>';
	                    	}
	                    } elseif(!$mod_only) {
	                    	$history_exists = true; // Update history_exists to true
	                    	$output .= '<li><strong>' . $user['display_name'] . '</strong> ' . $action_taken . ' - <span>' . $date_time_formatted . '</span></li>';
	                    }
	                    
	                }
	            }
	            // Here you can format how you want the consult history to appear
	            echo '<ul>'.$output.'</ul>';
	        endwhile;
	    else: 
	        echo '<p style="text-align: center;">No History</p>';
	    endif; 
	    wp_reset_postdata();
	}


	add_action('acf/save_post', 'notify_on_moderator_change', 5);

	function notify_on_moderator_change($post_id) {
	    // Exit if it's not the 'consults' post type
	    if (get_post_type($post_id) !== 'consults') {
	        return;
	    }

	    // Get previous and new moderator values
	    $prev_moderator = get_field('moderators_assigned', $post_id, false);
	    $new_moderator = isset($_POST['acf']['field_63e27c7daa2e3']) ? sanitize_text_field($_POST['acf']['field_63e27c7daa2e3']) : null;

	    // Get the user the consult is assigned to
	    $user_id = get_submitted_by_user_id($post_id);

	    // If there is a new moderator and it's different from the previous one
	    if ($new_moderator && $new_moderator != $prev_moderator) {
	        // Prepare notifications
	        $moderator_name = get_the_author_meta('display_name', $new_moderator);
	        $user_notification = "Consult #" . $post_id . " has been assigned to " . $moderator_name . " (moderator).";
	        $moderator_notification = "Consult #" . $post_id . " has been assigned to you by an admin.";

	        // Notify the user and the new moderator
	        add_user_notification($user_id, $user_notification);
	        add_user_notification($new_moderator, $moderator_notification);

	        // Notify the previous moderator if there was one
	        if ($prev_moderator) {
	            $old_moderator_notification = "You have been unassigned from Consult #" . $post_id . ".";
	            add_user_notification($prev_moderator, $old_moderator_notification);
	        }

	        // Update history
	        update_history($post_id, 'admin_moderator_change', $prev_moderator, $new_moderator);
	    }
	    // If the moderator was removed
	    elseif (!$new_moderator && $prev_moderator) {
	        $old_moderator_notification = "You have been unassigned from Consult #" . $post_id . ".";
	        add_user_notification($prev_moderator, $old_moderator_notification);

	        // Update history for unassignment
	        update_history($post_id, 'admin_moderator_change', $prev_moderator, $new_moderator);
	    }
	}



add_action('show_user_profile', 'my_show_extra_profile_fields');
add_action('edit_user_profile', 'my_show_extra_profile_fields');

function my_show_extra_profile_fields($user)
{
    echo '<h3>Extra profile information</h3>

    <table class="form-table">

        <tr>
            <th><label for="profession">Profession</label></th>

            <td>
                <input type="text" name="profession" id="profession" value="' . esc_attr(get_the_author_meta('profession', $user->ID)) . '" class="regular-text" /><br />
            </td>
        </tr>
        <tr>
            <th><label for="contactid">ContactID</label></th>

            <td>
                <input type="text" name="contactid" id="ContactID" value="' . esc_attr(get_the_author_meta('contactid', $user->ID)) . '" class="regular-text" /><br />
            </td>
        </tr>
        <tr>
            <th><label for="apaid">APAID</label></th>

            <td>
                <input type="text" name="apaid" id="apaid" value="' . esc_attr(get_the_author_meta('apaid', $user->ID)) . '" class="regular-text" /><br />
            </td>
        </tr>
        <tr>
	        <th><label for="phone_number">Phone Number</label></th>
	        <td>
	            <input type="text" name="phone_number" id="phone_number" value="' . esc_attr(get_the_author_meta('phone_number', $user->ID)) . '" class="regular-text" /><br />
	        </td>
	    </tr>
	    <tr>
	        <th><label for="organization">Organization</label></th>
	        <td>
	            <input type="text" name="organization" id="organization" value="' . esc_attr(get_the_author_meta('organization', $user->ID)) . '" class="regular-text" /><br />
	        </td>
	    </tr>
	    <tr>
	        <th><label for="organization_zip_code">Organization Zip Code</label></th>
	        <td>
	            <input type="text" name="organization_zip_code" id="organization_zip_code" value="' . esc_attr(get_the_author_meta('organization_zip_code', $user->ID)) . '" class="regular-text" /><br />
	        </td>
	    </tr>
    </table>';
}

add_action('personal_options_update', 'my_save_extra_profile_fields');
add_action('edit_user_profile_update', 'my_save_extra_profile_fields');

function my_save_extra_profile_fields($user_id)
{
    if (current_user_can('edit_user', $user_id)) {
        // Save the profession field
        if (isset($_POST['profession'])) {
            update_user_meta($user_id, 'profession', sanitize_text_field($_POST['profession']));
        }

        // Save the contactid field
        if (isset($_POST['contactid'])) {
            update_user_meta($user_id, 'contactid', sanitize_text_field($_POST['contactid']));
        }

        // Save the apaid field
        if (isset($_POST['apaid'])) {
            update_user_meta($user_id, 'apaid', sanitize_text_field($_POST['apaid']));
        }

        // Save the new fields
	    if (isset($_POST['phone_number'])) {
	        update_user_meta($user_id, 'phone_number', sanitize_text_field($_POST['phone_number']));
	    }
	    if (isset($_POST['organization'])) {
	        update_user_meta($user_id, 'organization', sanitize_text_field($_POST['organization']));
	    }
	    if (isset($_POST['organization_zip_code'])) {
	        update_user_meta($user_id, 'organization_zip_code', sanitize_text_field($_POST['organization_zip_code']));
	    }
    }
}

function import_old_tickets() {
    // Get the path to the CSV file
    $csv_file_path = get_template_directory() . '/old-tickets.csv'; // Assuming the file is located in the parent directory

    if (!file_exists($csv_file_path)) {
        return; // The CSV file doesn't exist, nothing to import
    }

    // Open the CSV file
    $csv_file = fopen($csv_file_path, 'r');

    // Skip the first row (header) since it contains column names
    fgetcsv($csv_file);

    // Helper function to check if a post with a specific legacy ID already exists
    function post_with_legacy_id_exists($legacy_id) {
        $existing_post = get_posts(array(
            'post_type' => 'consults',
            'meta_key' => 'legacy_id',
            'meta_value' => $legacy_id,
            'posts_per_page' => 1,
            'fields' => 'ids',
        ));

        return !empty($existing_post);
    }

    // Loop through the rows and create/update "consults" posts
    while (($data = fgetcsv($csv_file)) !== false) {
        // Extract data from the row
        $post_title = $data[0];
        $legacy_id = $data[1];
        $datetime = $data[2];
        $topic = $data[3];
        $subtopic = $data[4];
        $state = $data[5];
        $agent = $data[6];
        $submitted_by = $data[7];
        $description = $data[8];
        $contact_number = $data[9];
        $organization = $data[10];
        $organization_zip_code = $data[11];
        $country = $data[12];
        $referral = $data[13];
        $profession = $data[14];

        // Prepare data for ACF fields
        $datetime = strtotime($datetime); // Convert to UNIX timestamp
        $topic_term = get_term_by('name', $topic, 'consult_topic');
        $subtopic_term = get_term_by('name', $subtopic, 'consult_subtopic');

        // Check if a post with the same legacy ID already exists
        if (post_with_legacy_id_exists($legacy_id)) {
            // If the post already exists, update it
            $consult_id = get_posts(array(
                'post_type' => 'consults',
                'meta_key' => 'legacy_id',
                'meta_value' => $legacy_id,
                'posts_per_page' => 1,
                'fields' => 'ids',
            ));

            if ($consult_id && is_array($consult_id)) {
			    $consult_id = $consult_id[0]; // Extract the post ID from the array
			}

            if ($consult_id) {
                // Update ACF fields
                if ($topic_term && is_object($topic_term)) {
				    update_field('topic', $topic_term->term_id, $consult_id);
				}
				if ($subtopic_term && is_object($subtopic_term)) {
				    update_field('subtopic', $subtopic_term->term_id, $consult_id);
				}
                update_field('subject', $post_title, $consult_id);

                if ($state === 'closed') {
                    update_field('status', 'closed', $consult_id);
                } else {
                    update_field('status', 'in-progress', $consult_id);
                }

                $agent_user = get_user_by('login', $agent);
                if ($agent_user) {
                    update_field('moderators_assigned', array($agent_user->ID), $consult_id);
                } else {
                	$agent_user = get_user_by('ID', '3862');
                	update_field('moderators_assigned', array($agent_user->ID), $consult_id);
                }

                // Use the first name and last name to construct submitted_by_nicename
                $submitted_user = get_user_by('login', $submitted_by);
                if ($submitted_user) {
                    $submitted_by_nicename = $submitted_user->display_name;
                    $submitted_by_email = $submitted_user->user_email;
                    update_field('field_63e27c44aa2e1', $submitted_by_nicename, $consult_id);
                    update_field('submitted_email', $submitted_by_email, $consult_id);
                }
                update_field('description', $description, $consult_id);
                update_field('contact_phone_number', $contact_number, $consult_id);
                update_field('organization', $organization, $consult_id);
                update_field('organization_zip_code', $organization_zip_code, $consult_id);
                update_field('country', $country, $consult_id);
                update_field('referral', $referral, $consult_id);
                update_field('profession', $profession, $consult_id);
            }
        } else {
            $submitted_user = get_user_by('login', $submitted_by);

            // Create a new "consults" post
            $post_data = array(
                'post_title' => $post_title,
                'post_status' => 'publish',
                'post_type' => 'consults',
                'post_date' => date('Y-m-d H:i:s', $datetime),
                'post_author'  => $submitted_user->ID,
            );

            $consult_id = wp_insert_post($post_data);

            if ($consult_id) {
                // Update ACF fields
                if ($topic_term && is_object($topic_term)) {
				    update_field('topic', $topic_term->term_id, $consult_id);
				}
				if ($subtopic_term && is_object($subtopic_term)) {
				    update_field('subtopic', $subtopic_term->term_id, $consult_id);
				}
                update_field('subject', $post_title, $consult_id);

                if ($state === 'closed') {
                    update_field('status', 'closed', $consult_id);
                } else {
                    update_field('status', 'in-progress', $consult_id);
                }

                $agent_user = get_user_by('login', $agent);
                if ($agent_user) {
                    update_field('moderators_assigned', array($agent_user->ID), $consult_id);
                } else {
                	$agent_user = get_user_by('ID', '3862');
                	update_field('moderators_assigned', array($agent_user->ID), $consult_id);
                }

                // Use the first name and last name to construct submitted_by_nicename
                $submitted_user = get_user_by('login', $submitted_by);
                if ($submitted_user) {
                    $submitted_by_nicename = $submitted_user->display_name;
                    $submitted_by_email = $submitted_user->user_email;
                    update_field('field_63e27c44aa2e1', $submitted_by_nicename, $consult_id);
                    update_field('submitted_email', $submitted_by_email, $consult_id);
                }
                update_field('description', $description, $consult_id);
                update_field('contact_phone_number', $contact_number, $consult_id);
                update_field('organization', $organization, $consult_id);
                update_field('organization_zip_code', $organization_zip_code, $consult_id);
                update_field('country', $country, $consult_id);
                update_field('referral', $referral, $consult_id);
                update_field('profession', $profession, $consult_id);

                // Set the legacy_id ACF field with the provided $legacy_id
                update_field('legacy_id', $legacy_id, $consult_id);
            }
        }
    }

    // Close the CSV file
    fclose($csv_file);
}
function phpspecs(){
	echo phpinfo();
}