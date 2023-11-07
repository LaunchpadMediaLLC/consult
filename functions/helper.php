<?php 
	function create_history_post($consult_id){
		// create consult history post
        $post_title = '#' . $consult_id . ' - Consult History';
        $post_content = '';
        $post_status = 'publish';
        $post_type = 'consult_history';

        $new_history_post = array(
            'post_title' => $post_title,
            'post_content' => $post_content,
            'post_status' => $post_status,
            'post_type' => $post_type,
        );

        $new_history_post_id = wp_insert_post($new_history_post);

        return $new_history_post_id;

	}
	function get_history_posts($consult_id){
		// find corresponding consult_history and add history log entry
	    $args = array(
	        'post_type' => 'consult_history',
	        'meta_query' => array(
	            array(
	                'key' => 'consult_id',
	                'value' => $consult_id,
	                'compare' => '=',
	            )
	        )
	    );
	    $consult_history = get_posts($args);

        return $consult_history;

	}
	function create_history_log($user, $action, $datetime, $mod=FALSE, $note=''){

		$history_log = array(
            'user' => $user,
            'action_taken' => $action,
            'date_time' => $datetime,
            'mod_only' => $mod,
            'message' => $note,
        );

        return $history_log;

	}
	function delete_history_log( $row_id, $history_id){

		delete_row('history_log', $row_id, $history_id);

	}
	add_action( 'save_post_consults', 'create_consult_history', 10, 3 );
	function create_consult_history( $post_id, $post, $update ) {
		// Make sure we only run this for new consult posts
		if ( ! $update ) {
			// Call the update_history function with the 'create' action
			update_history( $post_id, 'create' );
		}
	}
	function get_attachments($file_upload) {
	    $attachments = explode(',', $file_upload);
	    $attachments = array_map('trim', $attachments);

	    $output = '';

	    foreach ($attachments as $attachment) {
	        $url = $attachment;
	        $file_name = basename($attachment);
	        $file_extension = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

	        // Set the icon based on the file extension
	        $icon = ($file_extension === 'jpg' || $file_extension === 'png') ? get_icon('image') : get_icon('file');

	        $output .= '<a href="' . $url . '" class="hover:text-blue inline-flex w-full items-center transition-all ease-in-out duration-200" target="_blank"><span class="inline-block mr-2">' . $icon . '</span>' .  $file_name . '</a>';
	    }

	    return $output;
	}
	function get_icon($icon){
		$output = '';
		switch ($icon) {
			case 'trash':
				$output = '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-trash-2"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path><line x1="10" y1="11" x2="10" y2="17"></line><line x1="14" y1="11" x2="14" y2="17"></line></svg>';
				break;
			case 'history':
				$output = '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-calendar"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg>';
				break;
			case 'share':
				$output = '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-share-2"><circle cx="18" cy="5" r="3"></circle><circle cx="6" cy="12" r="3"></circle><circle cx="18" cy="19" r="3"></circle><line x1="8.59" y1="13.51" x2="15.42" y2="17.49"></line><line x1="15.41" y1="6.51" x2="8.59" y2="10.49"></line></svg>';
				break;
			case 'note':
				$output = '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-message-square"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path></svg>';
				break;
			case 'edit':
				$output = '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-edit"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg>';
				break;
			case 'alert':
				$output = '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-alert-octagon"><polygon points="7.86 2 16.14 2 22 7.86 22 16.14 16.14 22 7.86 22 2 16.14 2 7.86 7.86 2"></polygon><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>';
				break;
			case 'clipboard':
				$output = '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-clipboard"><path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"></path><rect x="8" y="2" width="8" height="4" rx="1" ry="1"></rect></svg>';
				break;
			case 'plus':
				$output = '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-plus-circle"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="16"></line><line x1="8" y1="12" x2="16" y2="12"></line></svg>';
				break;
			case 'home':
				$output = '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-home"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path><polyline points="9 22 9 12 15 12 15 22"></polyline></svg>';
				break;
			case 'edit-2':
				$output = '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-edit-3"><path d="M12 20h9"></path><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"></path></svg>';
				break;
			case 'settings':
				$output = '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-settings"><circle cx="12" cy="12" r="3"></circle><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"></path></svg>';
				break;
			case 'logout':
				$output = '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-log-out"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path><polyline points="16 17 21 12 16 7"></polyline><line x1="21" y1="12" x2="9" y2="12"></line></svg>';
				break;
			case 'info':
				$output = '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-info"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="16" x2="12" y2="12"></line><line x1="12" y1="8" x2="12.01" y2="8"></line></svg>';
				break;
			case 'help':
				$output = '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-help-circle"><circle cx="12" cy="12" r="10"></circle><path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"></path><line x1="12" y1="17" x2="12.01" y2="17"></line></svg>';
				break;	
			case 'book':
				$output = '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-book-open"><path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"></path><path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"></path></svg>';
				break;
			case 'bell':
				$output = '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-bell"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path><path d="M13.73 21a2 2 0 0 1-3.46 0"></path></svg>';
				break;
			case 'filter':
				$output = '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-filter"><polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"></polygon></svg>';
				break;
				case 'search':
				$output = '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-search"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>';
				break;
				case 'more':
				$output = '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-more-horizontal"><circle cx="12" cy="12" r="1"></circle><circle cx="19" cy="12" r="1"></circle><circle cx="5" cy="12" r="1"></circle></svg>';
				break;
				case 'save':
				$output = '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-save"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path><polyline points="17 21 17 13 7 13 7 21"></polyline><polyline points="7 3 7 8 15 8"></polyline></svg>';
				break;
				case 'image':
				$output = '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-image"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect><circle cx="8.5" cy="8.5" r="1.5"></circle><polyline points="21 15 16 10 5 21"></polyline></svg>';
				break;
				case 'file':
				$output = '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-file-text"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line><polyline points="10 9 9 9 8 9"></polyline></svg>';
				break;
				case 'x':
				$output = '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-x"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>';
				break;
				case 'menu':
				$output = '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-menu"><line x1="3" y1="12" x2="21" y2="12"></line><line x1="3" y1="6" x2="21" y2="6"></line><line x1="3" y1="18" x2="21" y2="18"></line></svg>';
				break;
				case 'archive':
				$output = '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-archive"><polyline points="21 8 21 21 3 21 3 8"></polyline><rect x="1" y="3" width="22" height="5"></rect><line x1="10" y1="12" x2="14" y2="12"></line></svg>';
				break;
				case 'load':
				$output = '<svg xmlns="http://www.w3.org/2000/svg" width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-loader animate-spin"><line x1="12" y1="2" x2="12" y2="6"></line><line x1="12" y1="18" x2="12" y2="22"></line><line x1="4.93" y1="4.93" x2="7.76" y2="7.76"></line><line x1="16.24" y1="16.24" x2="19.07" y2="19.07"></line><line x1="2" y1="12" x2="6" y2="12"></line><line x1="18" y1="12" x2="22" y2="12"></line><line x1="4.93" y1="19.07" x2="7.76" y2="16.24"></line><line x1="16.24" y1="7.76" x2="19.07" y2="4.93"></line></svg>';
				break;
			default:
				// code...
				break;
		}

		return $output;
	}
	function is_user_allowed_to_view_message_tools($submitted_by) {
	    $current_user = wp_get_current_user();

	    // Check if the current user is the user who submitted the message
	    if ($current_user->ID == $submitted_by) {
	        return true;
	    }

	    // // Check if the current user is an admin or support agent
	    // if (in_array('administrator', $current_user->roles) || in_array('support_agent', $current_user->roles)) {
	    //     return true;
	    // }

	    return false;
	}
	function is_user_mod($user_id = null) {
	    // If $user_id is provided, check if the user with that ID is a moderator
	    if ($user_id !== null) {
	        $user = get_userdata($user_id);

	        if ($user && (in_array('administrator', $user->roles) || in_array('support_agent', $user->roles))) {
	            return true;
	        }
	    } else {
	        // If $user_id is not provided, check if the current user is a moderator
	        $current_user = wp_get_current_user();

	        if (in_array('administrator', $current_user->roles) || in_array('support_agent', $current_user->roles)) {
	            return true;
	        }
	    }

	    return false;
	}
	function is_current_user_collaborator($consult_id, $user = NULL) {
	    // Check if a user ID is provided, if not, get the current user ID
	    $user_id_to_check = $user ? $user : get_current_user_id();

	    // Get the 'collaborators' field from the consult (assuming it returns an array of user IDs)
	    $collaborators = get_field('collaborators', $consult_id);

	    // Check if the user ID to check is in the collaborators array
	    if (is_array($collaborators) && in_array($user_id_to_check, $collaborators)) {
	        return true;
	    }

	    return false;
	}

	function get_consult_topic_by_id($term_id) {
	    $term = get_term($term_id, 'consult_topic');
	    if (!$term || is_wp_error($term)) {
	        return false;
	    }
	    return array($term->name, $term->ID);
	}
	function is_not_closed($status) {
	  return strtolower($status) !== 'closed';
	}
	function create_user_notification_post($user_id) {
	    // Create a new consult_notification post
	    $post_args = array(
	        'post_title'   => 'User #' . $user_id . ' - Notification Log',
	        'post_type'    => 'consult_notification',
	        'post_status'  => 'publish',
	        'post_content' => '',
	    );

	    // Insert the post and get the post ID
	    $post_id = wp_insert_post($post_args);

	    // Set the user field value
	    update_field('user', $user_id, $post_id);
	}

	function add_user_notification($user_id, $notification) {

		// Get the current timestamp
	    $timestamp = date('M j, Y \a\t g:ia');

	    // Append the timestamp to the notification
	    $notification .= ' - ' . $timestamp;

	    // Check if consult_notification post exists for the user
	    $args = array(
	        'post_type'      => 'consult_notification',
	        'meta_query'     => array(
	            array(
	                'key'   => 'user',
	                'value' => $user_id,
	            ),
	        ),
	        'posts_per_page' => 1,
	    );

	    $query = new WP_Query($args);

	    if ($query->have_posts()) {
	        $query->the_post();

	        // Get the existing notifications and add the new one
	        $notifications_field = get_field('notifications');
	        $notifications_field[] = array(
	            'notification' => $notification,
	            'is_read'      => false,
	        );

	        // Update the notifications repeater field
	        update_field('notifications', $notifications_field, get_the_ID());
	    } else {
	        // Create a new consult_notification post and add the notification
	        create_user_notification_post($user_id);

	        // Re-run the query to fetch the newly created post
	        $query = new WP_Query($args);

	        if ($query->have_posts()) {
	            $query->the_post();

	            // Get the existing notifications and add the new one
	            $notifications_field = get_field('notifications');
	            $notifications_field[] = array(
	                'notification' => $notification,
	                'is_read'      => false,
	            );

	            // Update the notifications repeater field
	            update_field('notifications', $notifications_field, get_the_ID());
	        }
	    }

	    wp_reset_postdata();
	}

	function get_user_notifications($user_id) {
	    // Query consult_notification posts for the given user
	    $args = array(
	        'post_type'      => 'consult_notification',
	        'meta_query'     => array(
	            array(
	                'key'   => 'user',
	                'value' => $user_id,
	            ),
	        ),
	        'posts_per_page' => 1,
	    );

	    $query = new WP_Query($args);

	    // Check if any consult_notification post is found
	    if ($query->have_posts()) {
	        $query->the_post();

	        // Get the notifications repeater field
	        $notifications_field = get_field('notifications');

	        $notification_id = get_the_ID(); // Use the post ID as the notification ID

	        // Check if the notifications field is empty or has only read notifications
	        if (empty($notifications_field) || is_all_notifications_read($notifications_field)) {
	            return '<div class="p-3 text-xs text-center">No Notifications</div>';
	        } else {
	            $output = '';
	            // Loop through the notifications in reverse order
            	$notifications_field = array_reverse($notifications_field);
	            // Loop through the notifications
	            foreach ($notifications_field as $index => $notification) {
	                $notification_text = $notification['notification'];
	                $is_read = $notification['is_read'];
	                $notification_id = get_the_ID(); // Use the post ID as the notification ID
        			$notification_row = $index; // Use the repeater row index as the notification row

	                // Check if the notification is unread
	                if (!$is_read) {
	                    $output .= '<li class="even:bg-gray-100 p-3 text-xs flex items-center justify-between" x-data="{ hovered: false }" @mouseenter="hovered = true" @mouseleave="hovered = false"><span class="w-11/12 inline-block">' . esc_html($notification_text) . '</span><button x-on:click="markNotificationAsRead(' . $notification_id . ', ' . $notification_row . ');" x-show="hovered" x-transition:enter="transition-opacity ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-50" x-transition:leave="transition-opacity ease-in duration-200" x-transition:leave-start="opacity-50" x-transition:leave-end="opacity-0" class="px-2 hover:text-pink focus:text-pink transition-all duration-200 ease-in-out opacity-50 flex-none">'.get_icon('x').'</button></li>';
	                }
	            }

	            // Check if there are no unread notifications
	            if (empty($output)) {
	                return '<div class="p-3 text-xs text-center">No Notifications</div>';
	            }

	            // Append the "Mark All As Read" button
            	$output .= '<li class="even:bg-gray-100 p-3 text-xs flex items-center justify-end"><button x-on:click="markAllNotificationsAsRead(' . $notification_id . ');" class="text-text hover:text-blue focus:text-blue transition-all duration-200 ease-in-out">Clear All</button></li>';

	            // Return the formatted string of notifications
	            return '<ul class="divide-y">' . $output . '</ul>';
	        }
	    } else {
	        // No consult_notification post found for the user, create a new one
	        create_user_notification_post($user_id);
	        return '<div class="p-3 text-xs text-center">No Notifications</div>';
	    }

	    wp_reset_postdata();
	}

	function is_all_notifications_read($notifications) {
	    foreach ($notifications as $notification) {
	        if (!$notification['is_read']) {
	            return false;
	        }
	    }

	    return true;
	}
	function get_unread_notifications_count($user_id) {
	    $count = 0;

	    // Query consult_notification posts for the given user
	    $args = array(
	        'post_type'      => 'consult_notification',
	        'meta_query'     => array(
	            array(
	                'key'   => 'user',
	                'value' => $user_id,
	            ),
	        ),
	        'posts_per_page' => -1,
	    );

	    $query = new WP_Query($args);

	    // Loop through the consult_notification posts
	    while ($query->have_posts()) {
	        $query->the_post();

	        // Get the notifications repeater field
	        $notifications_field = get_field('notifications');

	        // Loop through the repeater entries
	        if ($notifications_field) {
	            foreach ($notifications_field as $notification) {
	                // Check if the notification is marked as not read
	                if (!$notification['is_read']) {
	                    $count++;
	                }
	            }
	        }
	    }

	    wp_reset_postdata();

	    // Modify the count representation
	    if ($count > 10) {
	        $count = '10+';
	    }

	    return $count;
	}
	function handle_message_notifications($submitted_by, $moderator_ids, $consult_id) {
	    if (is_user_mod($submitted_by)) {
	        foreach ($moderator_ids as $moderator_id) {
	            if ($moderator_id != $submitted_by) {
	                $notification = get_the_author_meta('display_name', $submitted_by) . " (Moderator) has replied to consult #" . $consult_id;
	                add_user_notification($moderator_id, $notification);
	            }
	        }

	        $user_id = get_submitted_by_user_id($consult_id);
	        $notification = get_the_author_meta('display_name', $submitted_by) . " (Moderator) has replied to consult #" . $consult_id;
	        add_user_notification($user_id, $notification);
	    } else {
	        foreach ($moderator_ids as $moderator_id) {
	            $notification = get_the_author_meta('display_name', $submitted_by) . " (User) has replied to consult #" . $consult_id;
	            add_user_notification($moderator_id, $notification);
	        }
	    }
	}
	function mark_notification_as_read($notification_row, $notification_id) {
	    // Get the consult_notification post
	    $notification_post = get_post($notification_id);

	    if ($notification_post) {
	        // Get the notifications repeater field
	        $notifications_field = get_field('notifications', $notification_id);

	        if ($notifications_field) {
	            // Update the is_read field for the specified notification row
	            $notifications_field[$notification_row]['is_read'] = true;

	            // Save the updated notifications field
	            update_field('notifications', $notifications_field, $notification_id);
	        }
	    }
	}
	function mark_all_notifications_as_read($notification_id) {
	    // Get the consult_notification post
	    $notification_post = get_post($notification_id);

	    if ($notification_post) {
	        // Get the notifications repeater field
	        $notifications_field = get_field('notifications', $notification_id);

	        if ($notifications_field) {
	            // Loop through the notifications and mark them as read
	            foreach ($notifications_field as &$notification) {
	                $notification['is_read'] = true;
	            }

	            // Save the updated notifications field
	            update_field('notifications', $notifications_field, $notification_id);
	        }
	    }
	}

	function get_submitted_by_user_id($consult_id) {
	    $submitted_by = get_post_field('post_author', $consult_id);

	    if ($submitted_by) {
	        return $submitted_by;
	    }

	    return false;
	}
