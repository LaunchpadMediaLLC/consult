<?php
	function generate_consults_table($user_dash = false, $show_archived = false) {

	  // Get the current user ID
	  $current_user_id = get_current_user_id();
	  $tableWrapper = '';
	  $tableItems = '';
	  $output = '';

	  if($show_archived){
	  	$status_not_closed = array(
		    'key' => 'status',
		    'value' => 'closed',
		    'compare' => '='
		);

	  }else{
	  	$status_not_closed = array(
		    'key' => 'status',
		    'value' => 'closed',
		    'compare' => '!='
		);
	  }

	  if (is_user_mod() && !$user_dash) :
	    // Query for consults with matching moderators_assigned
	    $args = array(
	      'post_type' => 'consults',
	      'fields' => 'ids',
	      'meta_query' => array(
	        'relation' => 'AND',
	        $status_not_closed,
	        array(
          'relation' => 'OR',
	          array(
	            'key' => 'moderators_assigned', // Assuming 'moderators_assigned' is the meta key for the user field
	            'value' => $current_user_id,
	            'compare' => '='
	          ),
	          array(
	            'key' => 'collaborators', // Assuming 'collaborators' is the meta key for the ACF field
	            'value' => serialize(strval($current_user_id)),
	            'compare' => 'LIKE' // Using 'LIKE' to search within serialized arrays
	          ),
	        )
	      ),
	      'posts_per_page' => 1,
	    );
	  else :
	    $current_user = wp_get_current_user();
	    $args = array(
	      'post_type' => 'consults',
	      'fields' => 'ids',
	      'posts_per_page' => 1,
	      'meta_query' => array(
	        'relation' => 'AND',
	        $status_not_closed,
	        array(
	          'key' => 'submitted_email',
	          'value' => $current_user->user_email,
	          'compare' => '='
	        ),
	        array(
	          'key' => 'moderators_assigned',
	          'compare' => 'EXISTS'
	        ),
	        array(
	          'key' => 'moderators_assigned',
	          'value' => '',
	          'compare' => '!='
	        )
	      ),
	    );
	  endif;

	  $consults_query = new WP_Query($args);

	  $tableWrapper .= '<div id="loading-icon" class="bg-background absolute flex left-0 right-0 text-center h-full z-10">
			<div class="mx-auto text-blue">'
			. get_icon('load') .
			'</div>
		</div>';
	  	$tableWrapper .= '<div x-data="{ isOpen: false, activeConsult: null, openSlideover(consultId) { this.activeConsult = consultId; this.isOpen = true; updatePanel(consultId); } }" x-on:click="if ($event.target.closest(\'.slideover-btn\')) { openSlideover($event.target.closest(\'.slideover-btn\').dataset.consultId) }">';
	    $tableWrapper .= '<table id="consult-table" class="min-w-full display" data-user-dash="' . $user_dash . '" data-show-archived="' . $show_archived . '">';
		    $tableWrapper .= '<thead class="bg-white">';
			    $tableWrapper .= '<tr>';
				    $tableWrapper .= '<th class="p-6 text-left text-xs font-medium text-title uppercase tracking-wider">Consult ID</th>';
				    $tableWrapper .= '<th class="p-6 text-left text-xs font-medium text-title uppercase tracking-wider">Title</th>';
				    $tableWrapper .= '<th class="p-6 text-left text-xs font-medium text-title uppercase tracking-wider">Date Created</th>';
				    (is_user_mod() && !$user_dash) ? $tableWrapper .= '<th class="p-6 text-left text-xs font-medium text-title uppercase tracking-wider">Submitted By</th>' :  $tableWrapper .= '<th class="p-6 text-left text-xs font-medium text-title uppercase tracking-wider">Moderator Assigned</th>';
				    $tableWrapper .= '<th class="p-6 text-left text-xs font-medium text-title uppercase tracking-wider dt-head-center">Status</th>';
				    $tableWrapper .= '<th class="p-6 text-left text-xs font-medium text-title uppercase tracking-wider">Topic</th>';
			    $tableWrapper .= '</tr>';
		    $tableWrapper .= '</thead>';
	    $tableWrapper .= '<tbody class="bg-white divide-y divide-gray-200">';
	    $tableWrapper .= '</tbody>';
	    $tableWrapper .= '</table>';
		$tableWrapper .= '</div>';

	  // Generate the HTML table
	  if ($consults_query->have_posts()) {
		$output .= $tableWrapper;
	  } else {
	  	  if(is_user_mod() && !$user_dash) :
		  	$output = '<div class="w-full block p-8 py-6 text-center border border-blue rounded bg-light-blue text-blue text-sm font-bold text-bold">You have no consults assigned.</div>';
		  	$output .= $tableWrapper;
		  elseif(!get_pending_consults()) :
		  	$output = '<a href="/submit-consult" class="w-full block p-8 py-6 text-center border border-blue rounded bg-light-blue text-blue text-sm font-bold text-bold cursor-pointer transition-all duration-200 ease-in-out hover:bg-blue focus:bg-blue hover:text-light-blue focus:text-light-blue">You have no active consults. Click here to start one.</a>';
		  	$output .= $tableWrapper;
		  else :
		  	$output .= $tableWrapper;
		  endif;
	  }

	  // Reset post data
	  wp_reset_postdata();

	  return $output;
	}
	function lazy_load_table_posts() {
		// Read the parameters sent by DataTables
		$draw = intval($_POST['draw']);
		$start = intval($_POST['start']);
		$length = intval($_POST['length']);
		// Add user_dash and show_archived parameters if needed
		$show_archived = $_POST['show_archived'] == "true"; // Make sure to convert the string to boolean
	    $user_dash = $_POST['user_dash'] == "true";

		// Get the current user ID
		$current_user_id = get_current_user_id();

		if ($show_archived) {
			$status_not_closed = array(
			  'key' => 'status',
			  'value' => 'closed',
			  'compare' => '='
			);
		} else {
			$status_not_closed = array(
			  'key' => 'status',
			  'value' => 'closed',
			  'compare' => '!='
			);
		}

		if (is_user_mod() && !$user_dash) {
			$args = array(
			  'post_type' => 'consults',
			  'meta_query' => array(
			    'relation' => 'AND',
			    $status_not_closed,
			    array(
			        'relation' => 'OR',
			        array(
			          'key' => 'moderators_assigned',
			          'value' => $current_user_id,
			          'compare' => '='
			        ),
			        array(
			          'key' => 'collaborators',
			          'value' => serialize(strval($current_user_id)), // Since it's an array in serialized form
			          'compare' => 'LIKE'
			        )
			      )
			  ),
			  'offset' => $start,
			  'posts_per_page' => $length,
			);
		} else {
			$current_user = wp_get_current_user();
			$args = array(
			  'post_type' => 'consults',
			  'offset' => $start,
			  'posts_per_page' => $length,
			  'meta_query' => array(
			    'relation' => 'AND',
			    $status_not_closed,
			    array(
			      'key' => 'submitted_email',
			      'value' => $current_user->user_email,
			      'compare' => '='
			    ),
			    array(
			      'key' => 'moderators_assigned',
			      'compare' => 'EXISTS'
			    ),
			    array(
			      'key' => 'moderators_assigned',
			      'value' => '',
			      'compare' => '!='
			    )
			  ),
			);
		}

		$consults_query = new WP_Query($args);


	  $data = array();
	  if ($consults_query->have_posts()) {
	  	$slideover = '';
	    while ($consults_query->have_posts()) {
	    	
			$consults_query->the_post();
	      
			// Extract the required data
			$post_date = get_the_date('Y-m-d'); // Get the date in WordPress format
			$formatted_date = date('M d, Y', strtotime($post_date)); // Format the date
			$timestamp = strtotime($post_date);
			$submitted_by = get_field('submitted_by');
			$moderators_assigned = get_mods(get_field('moderators_assigned'));
			$status = get_field('status');
			$statuses = array('new'=>'New', 'in-progress'=>'In Progress', 'closed'=>'Closed');
			$consult_id = get_the_ID();
			$consult_title = get_the_title();
			$topic = get_consult_topic_by_id(get_field('topic'));
			$subtopic = get_consult_topic_by_id(get_field('subtopic'));

			$file_upload = get_field('file_upload');

			$color = get_status_color($status['label']);

			$is_collab = is_current_user_collaborator($consult_id);

			if(is_user_mod()  && !$user_dash && $is_collab):
				$consult_title .= ' <span class="text-green">(Collaborative)</span>';
			endif;

			if ($topic) {
  				list($topic_name, $topic_id) = $topic;
  			}else {$topic_name = 'None';$topic_id = 0;}
  			if ($subtopic) {
  				list($subtopic_name, $subtopic_id) = $subtopic;
  			}else {$subtopic_name = 'None';}
			
	      $slideover .= '
				<!-- The slide-over element -->
				<div class="fixed inset-0 overflow-hidden z-10 slideover-panel" x-show="isOpen && activeConsult === \'' . $consult_id . '\'" x-cloak>
				  <div class="absolute inset-0 overflow-hidden">
				    <!-- Background overlay -->
				    <div class="absolute inset-0 bg-gray-500 bg-opacity-75 transition-opacity" x-on:click="isOpen = false; updateAllConsults(\'' . $consult_id . '\', \'' . $user_dash . '\', archived);"></div>

				    <!-- Slide-over panel -->
				    <div class="fixed inset-y-0 right-0 max-w-full flex">
				      <div class="w-screen max-w-screen-lg">
				        <div class="h-full divide-y divide-gray-200 flex flex-col bg-background shadow">
				          <div class="flex-1 h-0 overflow-y-auto">
				            <div>
				              <!-- Panel header -->
				              <div class="flex items-center justify-between p-8 border-b border-solid bg-white">
				                <h2 class="text-lg font-medium text-title">' . $consult_title . '</h2>
				                <button type="button" x-on:click="isOpen = false; updateAllConsults(\'' . $consult_id . '\', \'' . $user_dash . '\', archived);" class="transition-all duration-200 ease-in-out transform hover:rotate-180 focus:rotate-180">
				                  <svg class="h-6 w-6 text-gray-500" x-description="Heroicon name: x" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
				                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
				                  </svg>
				                </button>
				              </div>

				              <!-- Panel content -->
				              <div class="p-8 space-y-8">';
												$slideover .= '<div class="consult-info text-sm flex flex-wrap items-center text-text">';
													$slideover .= '<div class="p-4 py-2 border rounded border-solid mr-4 mb-4"><span class="font-bold text-xs block">Consult ID</span> ' . $consult_id . '</div>';
													if(is_user_mod() && !$user_dash && !$is_collab) :
													$slideover .= '<div x-data="{ status: \'' . $status['label'] . '\', open: false }" class="relative">
																				  <button x-on:click="open = !open" type="button" :class="\'text-left p-4 py-2 border rounded border-solid border-\' + statusColor(status) + \' mr-4 mb-4 bg-\' + statusColor(status) + \' text-white cursor-pointer transition-all duration-200 ease-in-out transform focus:scale-105 hover:scale-105\'">
																				    <span class="font-bold text-xs block">Status
																				      <svg x-show="!open" class="w-4 h-4 inline-block -mt-1" viewBox="0 0 20 20" fill="currentColor">
																				        <path fill-rule="evenodd" d="M10 12.585l4.95-4.95a.75.75 0 111.06 1.06l-5.657 5.657a.75.75 0 01-1.06 0L4.99 8.695a.75.75 0 011.06-1.06L10 12.585z" clip-rule="evenodd"></path>
																				      </svg>
																				      <svg x-show="open" class="w-4 h-4 inline-block -mt-1" viewBox="0 0 20 20" fill="currentColor">
																				        <path fill-rule="evenodd" d="M10 7.414l-4.95 4.95a.75.75 0 01-1.06-1.06l5.657-5.657a.75.75 0 011.06 0l5.657 5.657a.75.75 0 11-1.06 1.06L10 7.414z" clip-rule="evenodd"></path>
																				      </svg>
																				    </span>
																				    <span x-text="status">' . $status['label'] . '</span>
																				  </button>
																				  <div x-show="open" x-on:click.away="open = false" class="absolute left-0 w-48 z-10 bg-white rounded-md shadow-xl divide-y divide-gray-100">';
																				    foreach ($statuses as $statusValue => $statusLabel) { 
																				      $slideover .= '<button x-on:click="status = \'' . $statusLabel . '\'; open = false; updateStatus(\'' . $statusValue . '\', \'' . $consult_id . '\', \'' . $current_user_id . '\')" :class="\'block px-4 py-2 w-full text-left text-sm text-gray-700 hover:bg-gray-100 hover:text-gray-900 rounded \' + statusColor(\'' . $statusLabel . '\')">
																				        ' . $statusLabel . '
																				      </button>';
																				    }
																				  $slideover .= '</div>
																				</div>';
													else :
														$slideover .= '<div class="p-4 py-2 border rounded border-solid mr-4 mb-4 text-white bg-'.$color.' border-'.$color.'"><span class="font-bold text-xs block">Status</span> ' . $status['label'] . '</div>';
													endif;
													if(is_user_mod() && !$user_dash) :
													$slideover .= '<div class="p-4 py-2 border rounded border-solid mr-4 mb-4"><span class="font-bold text-xs block">Submitted By</span> ' . get_field('submitted_by') . '</div>';
													
													$slideover .= '<div class="p-4 py-2 border rounded border-solid mr-4 mb-4"><span class="font-bold text-xs block">Submitted Email</span> ' . get_field('submitted_email') . '</div>';
													endif;
													$slideover .= '<div class="p-4 py-2 border rounded border-solid mr-4 mb-4"><span class="font-bold text-xs block">Moderators Assigned</span> ' . $moderators_assigned . '</div>';
													if($is_collab):
														$slideover .= '<div class="p-4 py-2 border border-green text-green rounded border-solid mr-4 mb-4"><span class="font-bold text-xs block">Collaborative Consult</span></div>';
													endif;
													if(is_user_mod()  && !$user_dash && !$is_collab) :
													$slideover .= '<div x-data="{ topic: \'' . $topic_name . '\', subtopic: \'' . $subtopic_name . '\', open: false }" class="flex flex-wrap items-center">
														<div x-data="{open: false }" class="relative">
														  <button x-on:click="open = !open" type="button" class="p-4 py-2 border rounded border-solid mr-4 mb-4 cursor-pointer bg-white transition-all duration-200 ease-in-out transform focus:scale-105 hover:scale-105">
														  	<span class="font-bold text-xs block text-left">Topic
														      <svg x-show="!open" class="w-4 h-4 inline-block -mt-1" viewBox="0 0 20 20" fill="currentColor">
														        <path fill-rule="evenodd" d="M10 12.585l4.95-4.95a.75.75 0 111.06 1.06l-5.657 5.657a.75.75 0 01-1.06 0L4.99 8.695a.75.75 0 011.06-1.06L10 12.585z" clip-rule="evenodd"></path>
														      </svg>
														      <svg x-show="open" class="w-4 h-4 inline-block -mt-1" viewBox="0 0 20 20" fill="currentColor">
														        <path fill-rule="evenodd" d="M10 7.414l-4.95 4.95a.75.75 0 01-1.06-1.06l5.657-5.657a.75.75 0 011.06 0l5.657 5.657a.75.75 0 11-1.06 1.06L10 7.414z" clip-rule="evenodd"></path>
														      </svg>
														      <span x-text="topic" class="font-normal block text-left text-sm">' . $topic_name . '</span>
														    </span>
														  </button>
														  <div x-show="open" x-on:click.away="open = false;" class="absolute left-0 z-10 bg-white rounded-md shadow-xl max-h-[180px] w-[32rem] overflow-y-auto divide-y divide-gray-100" id="consult-topic-' . $consult_id . '">
														    ' . get_topic_list($consult_id) . '
														  </div>
														</div>
														<div x-show="topic !== \'None\'" x-data="{open: false }" class="relative hidden">
														  <button x-on:click="open = !open" type="button" class="p-4 py-2 border rounded border-solid mr-4 mb-4 cursor-pointer bg-white transition-all duration-200 ease-in-out transform focus:scale-105 hover:scale-105" id="consult-subtopic-button-' . $consult_id . '">
														  	<span class="font-bold text-xs block text-left">Subtopic
														      <svg x-show="!open" class="w-4 h-4 inline-block -mt-1" viewBox="0 0 20 20" fill="currentColor">
														        <path fill-rule="evenodd" d="M10 12.585l4.95-4.95a.75.75 0 111.06 1.06l-5.657 5.657a.75.75 0 01-1.06 0L4.99 8.695a.75.75 0 011.06-1.06L10 12.585z" clip-rule="evenodd"></path>
														      </svg>
														      <svg x-show="open" class="w-4 h-4 inline-block -mt-1" viewBox="0 0 20 20" fill="currentColor">
														        <path fill-rule="evenodd" d="M10 7.414l-4.95 4.95a.75.75 0 01-1.06-1.06l5.657-5.657a.75.75 0 011.06 0l5.657 5.657a.75.75 0 11-1.06 1.06L10 7.414z" clip-rule="evenodd"></path>
														      </svg>
														      <span x-text="subtopic" class="font-normal block text-left text-sm">' . $subtopic_name . '</span>
														    </span>
														  </button>
														  <div x-show="open" x-on:click.away="open = false;" class="absolute left-0 z-10 bg-white rounded-md shadow-xl max-h-[180px] w-[32rem] overflow-y-auto divide-y divide-gray-100" id="consult-subtopic-' . $consult_id . '">
														    ' . get_topic_list($consult_id, get_field('topic')) . '
														  </div>
														</div>
													</div>';
													else : 
														if ($topic) {
										  				list($topic_name, $topic_id) = $topic;
										  				$slideover .= '<div class="p-4 py-2 border rounded border-solid mr-4 mb-4"><span class="font-bold text-xs block">Topic</span>' . $topic_name . '</div>';
										  			}
										  			if ($subtopic) {
										  				list($subtopic_name, $subtopic_id) = $subtopic;
										  				$slideover .= '<div class="p-4 py-2 border rounded border-solid mr-4 mb-4"><span class="font-bold text-xs block">Subtopic</span>' . $subtopic_name . '</div>';
										  			}
													endif;
													if(get_field('contact_phone_number')):
														$slideover .= '<div class="p-4 py-2 border rounded border-solid mr-4 mb-4"><span class="font-bold text-xs block">Contact Phone Number</span> ' . get_field('contact_phone_number') . '</div>';
													endif;
													if(get_field('organization')):
													$slideover .= '<div class="p-4 py-2 border rounded border-solid mr-4 mb-4"><span class="font-bold text-xs block">Organization</span> ' . get_field('organization') . '</div>';
													endif;
													if(get_field('organization_zip_code')):
													$slideover .= '<div class="p-4 py-2 border rounded border-solid mr-4 mb-4"><span class="font-bold text-xs block">Organization Zip Code</span> ' . get_field('organization_zip_code') . '</div>';
													endif;
													if(get_field('country')):
													$slideover .= '<div class="p-4 py-2 border rounded border-solid mr-4 mb-4"><span class="font-bold text-xs block">Country</span> ' . get_field('country') . '</div>';
													endif;
													if(get_field('referral')):
													$slideover .= '<div class="p-4 py-2 border rounded border-solid mr-4 mb-4"><span class="font-bold text-xs block">Referral</span> ' . get_field('referral') . '</div>';
													endif;
													$slideover .= '<div class="w-full p-8 py-4 border border-b-0 rounded rounded-b-none border-solid bg-white"><span class="font-bold text-xs block">Subject</span>' . get_field('subject') . '</div>';
													$slideover .= '<div class="w-full p-8 py-4 border rounded rounded-t-none border-solid space-y-4 bg-white"><span class="font-bold text-xs block">Description</span><div class="space-y-4 consult-message">' . get_field('description') . '</div></div>';
													if($file_upload) :
														$slideover .= '<div class="w-full p-8 py-4 mt-4 border rounded border-solid space-y-4 bg-white"><span class="font-bold text-xs block">Attachments</span><div class="space-y-2">' . get_attachments($file_upload) . '</div></div>';
													endif;
												$slideover .= '</div>';
												$slideover .= '<div class="consult-tools text-text flex items-center justify-between">';
													$slideover .= '<div class="flex items-center space-x-2">';
														$slideover .= '<div class="relative" x-data="{open: false}" id="consult-history-'.$consult_id.'">';
															$slideover .= '<button x-on:click="open = !open" type="button" class="p-4 py-2 border rounded border-solid cursor-pointer bg-white transition-all duration-200 ease-in-out transform focus:scale-105 hover:scale-105 hover:text-blue focus:text-blue">';
															$slideover .= get_icon('history');
															$slideover .= '</button>';
															$slideover .= '<div x-show="open" x-on:click.away="open = false" class="absolute left-0 z-10 bg-white rounded-md shadow-xl divide-y divide-gray-100 mt-4 max-h-[180px] w-[32rem] overflow-y-auto">'.get_history($consult_id).'
														  	</div>';
														$slideover .= '</div>';
														if(is_user_mod()):
															$slideover .= render_mod_tools($consult_id);
														endif;
													$slideover .= '</div>';
												$slideover .= '</div>';
												$slideover .= '<div class="border border-solid rounded bg-background divide-y" id="consult-replies-'.$consult_id.'">';
													$slideover .= get_replies($consult_id);
												$slideover .= '</div>';
												$slideover .= '<div>';
													$slideover .= render_consult_message_form($consult_id, $status['label'], $user_dash, $show_archived);
												$slideover .= '</div>';
											$slideover .= '</div>
				            </div>
				          </div>
				        </div>
				      </div>
				    </div>
				  </div>
				</div>';

	    	$data[] = array(
			  $consult_id,
			  $consult_title,
			  '<div class="text-sm text-text" data-order="' . $timestamp . '">' . $formatted_date . '</div>',
			  (is_user_mod() && !$user_dash) ? $submitted_by : $moderators_assigned,
			  '<div class="text-xs uppercase tracking-wider rounded p-2 text-center font-bold text-white bg-' . $color . '" data-status="' . $statuses[$status['value']] . '">' . $status['label'] . '</div>',
			  $topic_name,
			  'slideover_html' => $slideover,
			);
	    }
	  }

	  // Return the response in the format that DataTables expects
	  $response = array(
	    "draw" => $draw,
	    "recordsTotal" => (int) $consults_query->found_posts,
	    "recordsFiltered" => (int) $consults_query->found_posts, // Update if you add filtering
	    "data" => $data
	  );

	  echo json_encode($response);
	  wp_die(); // This is required to terminate immediately and return a proper response
	}

	add_action('wp_ajax_lazy_load_table_posts', 'lazy_load_table_posts'); // If called from admin side
	add_action('wp_ajax_nopriv_lazy_load_table_posts', 'lazy_load_table_posts'); // If called from elsewhere
	function get_toolbar($user_dash = false) {
	    ob_start();

	    // Get the current user ID
	    $current_user_id = get_current_user_id();

	    // Get the unread notifications count
	    $unread_count = get_unread_notifications_count($current_user_id);

	    ?>
	    <div x-data="{ open: false }" class="py-4 px-8 bg-white border-b">
	        <div class="container mx-auto">
	            <div class="flex items-center justify-between text-text relative">
	            	<div class="flex space-x-3 items-center">
	            		<div class="relative" x-data="{open: false}" id="notification-button">
		                    <button x-on:click="open = !open" type="button" class="relative p-4 py-2 border rounded border-solid cursor-pointer bg-white transition-all duration-200 ease-in-out transform focus:scale-105 hover:scale-105 hover:text-blue focus:text-blue">
		                        <?php echo get_icon('bell'); ?>
		                        <?php if ($unread_count > 0): ?>
		                            <span class="absolute top-[-8px] right-[-8px] text-white text-xs font-bold bg-pink w-6 h-6 flex justify-center items-center rounded-full"><?php echo $unread_count; ?></span>
		                        <?php endif; ?>
		                    </button>
		                    <div x-show="open" x-on:click.away="open = false" class="absolute left-0 z-10 bg-white rounded-md rounded-t-none border shadow-xl mt-4 max-h-[180px] w-[36rem] overflow-y-auto" x-cloak>
		                        <?php echo get_user_notifications($current_user_id); ?>
		                    </div>
		                </div>
		                <div>
		                	<button @click="archived = !archived; toggleArchivedConsults('<?php echo $user_dash; ?>', archived )" :class="{'text-pink border-pink': archived}" type="button" class="relative p-4 py-2 border rounded border-solid cursor-pointer bg-white transition-all duration-200 ease-in-out transform hover:scale-105 hover:text-pink">
		                        <?php echo get_icon('archive'); ?>
		                	</button>
		                </div>
		                <div class="text-sm font-bold" 
						     x-bind:class="archived ? 'text-pink' : 'text-blue'" 
						     x-text="archived ? 'Archived Consults' : 'Active Consults'">
						</div>
	            	</div>
	                <div class="relative inline-flex space-x-2 items-center" id="logout-button">
	                	<div class="text-sm text-text"><?php echo is_user_mod() ? 'Support Moderator: ' : 'Support User: '; ?> <strong><?php echo get_the_author_meta('display_name', $current_user_id); ?></strong></div>
	                	<a href="<?php echo wp_logout_url(home_url()); ?>" class="relative p-4 py-2 border rounded border-solid cursor-pointer bg-white transition-all duration-200 ease-in-out transform focus:scale-105 hover:scale-105 hover:text-blue focus:text-blue">
	                        <?php echo get_icon('logout'); ?>
	                    </a>
	                </div>
	            </div>
	        </div>
	    </div>
	    <?php

	    return ob_get_clean();
	}
	function get_main_bar() {
	    ob_start();
	    $is_mod = is_user_mod();
	    $current_user_id = get_current_user_id();
		$user = get_userdata($current_user_id);
	    $dashboard_link = $is_mod ? '/moderator-dashboard' : '/my-consultations';
	    $dashboard_text_color = (is_page('moderator-dashboard') || (is_page('my-consultations') && !$is_mod)) ? 'text-blue font-bold' : '';
	    $submit_consult_text_color = is_page('submit-consult') ? 'text-blue font-bold' : '';

	    ?>
	    <div class="flex flex-col h-full border-r" x-data="{ open: false, profileOpen: false }">
	        <!-- Logo and Mobile Menu Button -->
	        <div class="flex w-full justify-between items-center p-4 border-b">
	            <a href="/" class="block">
	                <img src="/app/uploads/2023/08/smi-logo.svg" alt="Logo" class="w-32 lg:w-full" />
	            </a>
	            <button x-data="{ open: false }" @click="open = !open" class="relative p-4 py-2 border rounded border-solid cursor-pointer bg-white text-text transition-all duration-200 ease-in-out transform focus:scale-105 hover:scale-105 hover:text-blue focus:text-blue md:hidden">
	                <?php echo get_icon('menu'); ?>
	            </button>
	        </div>

	        <!-- Main Nav -->
	        <nav class="text-sm flex-grow overflow-y-auto divide-y text-text">
	            <a href="<?php echo $dashboard_link; ?>"
	               class="flex items-center space-x-2 p-2 transition-colors duration-150 ease-in-out hover:bg-gray-200 <?php echo $dashboard_text_color; ?>">
	                <?php echo get_icon('home'); ?><span>Dashboard</span>
	            </a>
	            <?php if (!$is_mod) : ?>
		            <a href="/submit-consult"
		               class="flex items-center space-x-2 p-2 transition-colors duration-150 ease-in-out hover:bg-gray-200 <?php echo $submit_consult_text_color; ?>">
		                <?php echo get_icon('edit-2'); ?><span>Submit Consultation</span>
		            </a>
	            <?php endif; ?>
	            <a href="<?php echo is_user_mod() ? 'https://smiadviser.org/knowledge-base' : 'https://smiadviser.org/knowledge-base-fp'; ?>" target="_blank" class="flex items-center space-x-2 p-2 transition-colors duration-150 ease-in-out hover:bg-gray-200">
	                <?php echo get_icon('book'); ?><span>Knowledge Base</span>
	            </a>
	            <!-- Add more nav items here -->
	        </nav>

	        <!-- Settings and Legal Nav -->
	        <nav class="text-sm md:text-xs mt-auto divide-y text-text border-t">
	            <button x-on:click="profileOpen = true" class="w-full flex items-center space-x-2 p-2 transition-colors duration-150 ease-in-out hover:bg-gray-200">
				    <?php echo get_icon('settings'); ?><span>Profile</span>
				</button>
	            <a href="https://smiadviser.org/contact" target="_blank" class="flex items-center space-x-2 p-2 transition-colors duration-150 ease-in-out hover:bg-gray-200">
	                <?php echo get_icon('help'); ?><span>Contact Us</span>
	            </a>
	            <button x-on:click="open = true" class="w-full flex items-center space-x-2 p-2 transition-colors duration-150 ease-in-out hover:bg-gray-200">
	                <?php echo get_icon('info'); ?><span>Grant & Mission Statements</span>
	            </button>
	            <a href="https://www.psychiatry.org/terms" target="_blank" class="flex items-center space-x-2 p-2 transition-colors duration-150 ease-in-out hover:bg-gray-200">
	                <?php echo get_icon('info'); ?><span>Terms & Privacy</span>
	            </a>
	            <a href="<?php echo wp_logout_url(home_url()); ?>" class="flex items-center space-x-2 p-2 transition-colors duration-150 ease-in-out hover:bg-gray-200">
	                <?php echo get_icon('logout'); ?><span>Logout</span>
	            </a>
	        </nav>

	        <!-- Modal -->
	        <div x-show="open" class="fixed inset-0 flex items-center justify-center z-50" x-cloak>
	            <div class="fixed inset-0 bg-black opacity-75"></div>
	            <div class="relative bg-white p-8 max-w-5xl mx-auto">
	                <h2 class="text-lg font-bold mb-2">Grant Statement</h2>
	                <p class="mb-4">Funding for SMI Adviser was made possible by Grant No. SM080818 from SAMHSA of the U.S. Department of Health and Human Services (HHS). The contents are those of the author(s) and do not necessarily represent the official views of, nor an endorsement by, SAMHSA/HHS or the U.S. Government.</p>
	                <hr>
	                <h2 class="text-lg font-bold mb-2 mt-4">Mission Statement</h2>
	                <p>Our mission is to advance the use of a person-centered approach to care that ensures people who have SMI find the treatment and support they need. For clinicians, we offer access to education, data, and consultations so you can make evidence-based treatment decisions. For patients, families, friends, people who have questions, or people who care for someone with SMI, we offer access to resources and answers from a national network of experts.</p>
	                <button type="button" class="absolute top-0 right-0 p-4 text-gray-500 transform transition-all duration-200 ease-in-out hover:rotate-90" @click="open = false">
	                    <?php echo get_icon('x'); ?>
	                </button>
	            </div>
	        </div>
	        <!-- Profile Modal -->
			<div x-show="profileOpen" class="fixed inset-0 flex items-center justify-center z-50" x-cloak>
			    <div class="fixed inset-0 bg-black opacity-75"></div>
			    <div class="relative bg-white p-8 max-w-5xl mx-auto">
			        <h2 class="text-lg font-bold mb-2">Edit Profile</h2>
			        <form id="user-profile-form" method="post" class="space-y-2">
			            <!-- First Name -->
			            <input type="text" name="first_name" value="<?php echo esc_attr($user->first_name); ?>" placeholder="First Name" class="w-full border rounded border-gray-100 px-4 py-2 focus:outline-none focus:border-blue transition-all duration-200 ease-in-out" required>
			            <!-- Last Name -->
			            <input type="text" name="last_name" value="<?php echo esc_attr($user->last_name); ?>" placeholder="Last Name" class="w-full border rounded border-gray-100 px-4 py-2 focus:outline-none focus:border-blue transition-all duration-200 ease-in-out" required>
			            <!-- Phone Number -->
			            <input type="tel" name="phone_number" value="<?php echo esc_attr(get_the_author_meta('phone_number', $user->ID)); ?>" placeholder="Phone Number" class="w-full border rounded border-gray-100 px-4 py-2 focus:outline-none focus:border-blue transition-all duration-200 ease-in-out">
			            <!-- Organization -->
			            <input type="text" name="organization" value="<?php echo esc_attr(get_the_author_meta('organization', $user->ID)); ?>" placeholder="Organization" class="w-full border rounded border-gray-100 px-4 py-2 focus:outline-none focus:border-blue transition-all duration-200 ease-in-out">
			            <!-- Organization Zip Code -->
			            <input type="text" name="organization_zip_code" value="<?php echo esc_attr(get_the_author_meta('organization_zip_code', $user->ID)); ?>" placeholder="Organization Zip Code" class="w-full border rounded border-gray-100 px-4 py-2 focus:outline-none focus:border-blue transition-all duration-200 ease-in-out">
			            <input type="hidden" name="user_id" value="<?php echo esc_attr($user->ID); ?>">
			            <!-- Update Button -->
			            <button type="submit" class="border rounded uppercase tracking-wide font-bold text-xs px-6 py-3 cursor-pointer mt-4 text-blue bg-white hover:border-gray-400 focus:border-gray-400 transition-all duration-200 ease-in-out transform hover:scale-105">Update Profile</button>
			        </form>
			        <button type="button" class="absolute top-0 right-0 p-4 text-gray-500 transform transition-all duration-200 ease-in-out hover:rotate-90" @click="profileOpen = false">
			            <?php echo get_icon('x'); ?>
			        </button>
			    </div>
			</div>

	    </div>
	    <?php
	    return ob_get_clean();
	}
	function get_mods($moderators_assigned){
	    $user_list = '';
	    if(!empty($moderators_assigned)){
	        $user = get_userdata($moderators_assigned);
	        if($user !== false) {
	            $user_list = $user->display_name;
	        } else {
	            $user_list = 'Not Assigned';
	        }
	    } else {
	        $user_list = 'Not Assigned';
	    }
	    return $user_list;
	}

	function render_consult_message_form($consult_id, $status, $user_dash = false, $show_archived = false) {

		$output = '';

	    $current_user = wp_get_current_user();
	    $is_collab = is_current_user_collaborator($consult_id);
	    $moderators_assigned = get_field('moderators_assigned', $consult_id);

		if (is_not_closed($status)) { 
			if(is_user_mod()):
				$output .= '<div class="mb-2 space-x-2 flex">
					<div class="relative" x-data="{open: false}">
						<button x-on:click="open = !open" type="button" class="p-4 py-2 text-xs uppercase font-bold text-text border rounded border-solid cursor-pointer bg-white transition-all duration-200 ease-in-out transform focus:scale-105 hover:scale-105 hover:text-yellow focus:text-yellow">Canned Responses</button>
						<div x-show="open" x-on:click.away="open = false" class="absolute divide-y divide-gray-200 left-0 z-10 bg-white rounded-md shadow-xl mt-2 max-h-[360px] w-[48rem] overflow-y-auto" id="consult-canned-responses-'.$consult_id.'"> 
							'. get_canned_responses($current_user, $consult_id) . '
					  	</div>
					</div>
					<div class="relative" x-data="{open: false}">
						<button x-on:click="open = !open" type="button" class="p-4 py-2 text-xs uppercase font-bold text-text border rounded border-solid cursor-pointer bg-white transition-all duration-200 ease-in-out transform focus:scale-105 hover:scale-105 hover:text-yellow focus:text-yellow">Signature</button>
						<div x-show="open" x-on:click.away="open = false" class="p-4 absolute left-0 z-10 bg-white rounded-md shadow-xl mt-2 max-h-[180px] w-[32rem] overflow-y-auto"> 
							'. get_user_signature($current_user, $consult_id) . '
					  	</div>
					</div>
				</div>';
			endif;
			$output .= '<div id="consult-message-form-'.$consult_id.'">
	        <form>
	            <input type="hidden" name="consult_id" value="'.$consult_id.'">
	            <input type="hidden" name="submitted_by" value="' . $current_user->ID . '">
	            <input type="hidden" name="moderators_assigned" value="' . $moderators_assigned . '">
	            <div>
	                <textarea name="message'.$consult_id.'" id="message'.$consult_id.'"></textarea>
	            </div>
	            <div class="inline-flex items-center space-x-2">
		            <input type="submit" value="Submit" class="border rounded uppercase tracking-wide font-bold text-xs px-6 py-3 cursor-pointer mt-4 text-blue bg-white hover:border-gray-400 focus:border-gray-400 transition-all duration-200 ease-in-out transform focus:scale-105 hover:scale-105">';
		            if(!$is_collab):
		            $output .= '<div class="border border-transparent rounded uppercase tracking-wide font-bold text-xs px-6 py-3 cursor-pointer mt-4 text-pink hover:border-pink focus:border-pink transition-all duration-200 ease-in-out transform focus:scale-105 hover:scale-105" x-on:click="status = \'Closed\'; updateStatus(\'closed\', \''. $consult_id .'\', \'' . $current_user->ID . '\'); open = false; archived = archived; updateAllConsults(\'' . $consult_id . '\', \'' . $user_dash . '\', \'\');">Close Consult</div>';
		        	endif;
		        $output .= '</div>
	        </form>
	    </div>';
		} else {
		  $output .= '<div class="w-full p-8 py-6 text-center border border-pink rounded bg-light-pink text-pink text-sm font-bold text-bold cursor-pointer transition-all duration-200 ease-in-out hover:bg-pink focus:bg-pink hover:text-light-pink focus:text-light-pink" x-on:click="status = \'In Progress\'; updateStatus(\'in-progress\', \''. $consult_id .'\', \'' . $current_user->ID . '\'); open = false; archived = !archived; updateAllConsults(\'' . $consult_id . '\', \'' . $user_dash . '\', \'\');">This consult is marked closed. Click here if you\'d like to reopen it.</div>';
		}

		return $output;
	}
	function render_mod_tools($consult_id) {
		$moderators_assigned = get_mods(get_field('moderators_assigned'), $consult_id);
		$current_user_id = get_current_user_id();
		$is_collab = is_current_user_collaborator($consult_id);

		$output = '';
		if(!$is_collab):
			$output .= '<div class="relative" x-data="{open: false}" id="consult-share-'.$consult_id.'">';
				$output .= '<button x-on:click="open = !open" type="button" class="p-4 py-2 border rounded border-solid cursor-pointer bg-white transition-all duration-200 ease-in-out transform focus:scale-105 hover:scale-105 hover:text-blue focus:text-blue">';
				$output .= get_icon('share');
				$output .= '</button>';
				$output .= '<div x-show="open" x-on:click.away="open = false" class="p-4 absolute left-0 z-10 bg-white rounded-md shadow-xl mt-4 max-h-[300px] w-[32rem] overflow-y-auto">';
					$output .= '<div class="relative">';
					    $output .= '<div id="update-mod-note-'.$consult_id.'">'
						    .get_mod_message($consult_id, $current_user_id, 'Collaborate', true).
						'</div>';
					$output .= '</div>';
			  	$output .= '</div>';
			$output .= '</div>';
		endif;
		$output .= '<div class="relative" x-data="{open: false}" id="consult-note-'.$consult_id.'">';
			$output .= '<button x-on:click="open = !open" type="button" class="p-4 py-2 border rounded border-solid cursor-pointer bg-white transition-all duration-200 ease-in-out transform focus:scale-105 hover:scale-105 hover:text-blue focus:text-blue">';
			$output .= get_icon('note');
			$output .= '</button>';
			$output .= '<div x-show="open" x-on:click.away="open = false; $refs.form.reset()" class="p-4 absolute left-0 z-10 bg-white rounded-md shadow-xl mt-4 max-h-[200px] w-[32rem] overflow-y-auto">'
				.get_mod_message($consult_id, $current_user_id, 'Add Note', false).
		  	'</div>';
		$output .= '</div>';

		return $output;
	}
	function get_pending_consults() {
	    global $current_user;
	    $output = '';
	    $args = array(
		    'post_type' => 'consults',
		    'meta_query' => array(
		        'relation' => 'AND',
		        array(
		            'key' => 'submitted_email',
		            'value' => $current_user->user_email,
		            'compare' => '=',
		        ),
		        array(
		            'relation' => 'OR',
		            array(
		                'key' => 'moderators_assigned',
		                'compare' => 'NOT EXISTS',
		            ),
		            array(
		                'key' => 'moderators_assigned',
		                'value' => '',
		                'compare' => '=',
		            ),
		        ),
		    ),
		);

	    $consults = new WP_Query( $args );
	    if ( $consults->have_posts() ) {
		    $output .= '<div>';
	        	$output .= '<div class="rounded-t border border-solid bg-white">';
		    		$output .= '<h2 class="text-gray-400 font-black text-2xl px-8 py-6">Pending</h2>';
		    	$output .= '</div>';
		    	$output .= '<div class="bg-background p-8 rounded-b border border-t-0 border-solid space-y-4 consult-dock" id="consults-pending">';
		    	while ( $consults->have_posts() ) {
					$consults->the_post();
					$consult_id = get_the_ID();
					$post_date = get_the_date('Y-m-d', $consult_id);
        			$formatted_date = date('M d, Y', strtotime($post_date)); // Format the date
					$output .= '<div id="consult-'.$consult_id.'">
					  	<div class="text-gray-400 rounded bg-white px-8 py-6 block border border-solid">
					  		<span class="text-gray-400 block font-bold text-lg pb-4 border-b border-solid">' . get_the_title($consult_id) . '</span>
					  		<div class="mt-4 text-sm flex flex-wrap items-center">
					  			<div class="p-4 py-2 border rounded border-solid mr-4 mb-4"><span class="font-bold text-xs block">Consult ID</span>' . $consult_id . '</div>
					  			<div class="p-4 py-2 border rounded border-solid mr-4 mb-4"><span class="font-bold text-xs block">Submitted On</span>' . $formatted_date . '</div>
					  		</div>
					  	</div>
					</div>';
				}
		        $output .= '</div>';
		    $output .= '</div>';
		}

		return $output;
	}
	function create_consult_message() {

	    $purpose = $_POST['purpose'];

	    switch ($purpose) {
	    	case 'create_message':
	    		$moderator_ids = explode(',', $_POST['moderators_assigned']);

			    $post_data = array(
			        'post_type' => 'consult_messages',
			        'post_status' => 'publish',
			        'post_author' => $_POST['submitted_by'],
			        'post_title' => 'Consult Message',
			        'meta_input' => array(
			            'consult_id' => $_POST['consult_id'],
			            'submitted_by' => $_POST['submitted_by'],
			            'message' => $_POST['message'],
			            'moderators_assigned' => $moderator_ids
			        )
			    );

			    $post_id = wp_insert_post( $post_data );

			    handle_message_notifications($_POST['submitted_by'], $moderator_ids, $_POST['consult_id']);

			    $result['type'] = 'create_message';
			    $result = json_encode($result);

    			echo $result;
	    		break;

	    	case 'update_status':
	    		$consult_id = $_POST['consult_id'];
	    		$status = $_POST['status'];
	    		$submitted_by = $_POST['submitted_by'];
	    		update_history( $consult_id, 'status',  $status);
	    		update_field('status', $status, $consult_id);

	    		// Retrieve additional information if needed
				$user_id = get_submitted_by_user_id($consult_id);
				$user_name = '';
				$moderator_assigned = get_field('moderators_assigned', $consult_id);

				// Check if the user is a moderator
				if (is_user_mod($submitted_by)) {
			        if ($moderator_assigned != $submitted_by) {
			            $user_name = get_the_author_meta('display_name', $submitted_by);
			            $status_readable = ucwords(str_replace('-', ' ', $status));
			            $notification = "$user_name (Moderator) has updated consult #$consult_id to $status_readable";
			            add_user_notification($moderator_assigned, $notification);
			        }
				    // Moderator updated the status, notify the user
				    $user_name = get_the_author_meta('display_name', $submitted_by);
				    $status_readable = ucwords(str_replace('-', ' ', $status));
				    $notification = "$user_name (Moderator) has updated consult #$consult_id to $status_readable";
				    add_user_notification($user_id, $notification);
				} else {
				    // User updated the status, notify the assigned moderators
				    $user_name = get_the_author_meta('display_name', $submitted_by);
				    $status_readable = ucwords(str_replace('-', ' ', $status));
			        $notification = "$user_name (User) has updated consult #$consult_id to $status_readable";
			        add_user_notification($moderator_assigned, $notification);
				}

		    	$result['type'] = 'update_status';
		    	$result = json_encode($result);
	    		echo $result;

	    		break;
	    	case 'update_moderator':
			    $consult_id = $_POST['consult_id'];
			    $moderator_id = $_POST['moderator'];
			    $submitted_by_id = $_POST['submitted_by'];
			    
			    // Convert the moderator ID to a user object
			    $moderator = get_user_by('ID', $moderator_id);
			    
			    // Convert the submitted by ID to a user object
			    $submitted_by = get_user_by('ID', $submitted_by_id);
			    
			    update_field('moderators_assigned', $moderator_id, $consult_id);
			    update_history($consult_id, 'moderator', '', $moderator_id);

			    $user_id = get_submitted_by_user_id($consult_id);
			    $old_mod = get_the_author_meta('display_name', $submitted_by_id);
			    $new_mod = $moderator->display_name;

			    $notification = "Consult #$consult_id has been assigned to $new_mod";
			    add_user_notification($user_id, $notification);

			    $notification = "Consult #$consult_id has been assigned to you by $old_mod";
			    add_user_notification($moderator_id, $notification);

			    $result['type'] = 'update_moderator';
			    $result = json_encode($result);
			    echo $result;

		    break;
	    	case 'update_topic':
	    		$consult_id = $_POST['consult_id'];
	    		$topic = $_POST['topic_id'];
	    		$child = $_POST['child'];
	    		update_field($child, $topic, $consult_id);

		    	$result['type'] = 'update_topic';
		    	$result['topic'] = $topic;
		    	if($child == 'topic'){
		    		update_field('subtopic', 0, $consult_id);
		    	}
		    	$result = json_encode($result);
	    		echo $result;

	    		break;
	    	case 'refreshConsults':
	    		$consult_id = $_POST['consult_id'];
	    		$archived = $_POST['show_archived'] == "true"; // Make sure to convert the string to boolean
	    		$user_dash = $_POST['user_dash'] == "true";

	    		$result['archived'] = $archived;
	    		$result['user_dash'] = $user_dash;
	    		$result['type'] = 'refreshConsults';
	    		$result = json_encode($result);
	    		echo $result;

	    		break;
	    	case 'save_message':

		    	// Get the form data from the AJAX request
			    $title = $_POST['title'];
			    $message = $_POST['message'];
			    $submitted_by = $_POST['submitted_by'];

			    // Create a new custom post
			    $post_id = wp_insert_post(array(
			        'post_title' => $title,
			        'post_type' => 'consult_response',
			        'post_status' => 'publish',
			        'post_author' => $submitted_by
			    ));

			    // Check if the post was created successfully
			    if ($post_id) {
			        // Update ACF fields for the new custom post
			        update_field('response', $message, $post_id);
			        update_field('author', $submitted_by, $post_id);
			    } else {
			        // Handle the error if the post was not created successfully
			        // You can return an error response or handle it in any other way you prefer
			    }

			    $result['type'] = 'save_message';
	    		$result = json_encode($result);
	    		echo $result;

	    	break;
	    	case 'edit_message':

		    	if(isset($_POST['message_id']) && isset($_POST['message'])) {
			        $post_id = intval($_POST['message_id']);
			        $message_content = $_POST['message'];
			        update_field('message', $message_content, $post_id);
			    }

			    $result['type'] = 'edit_message';
	    		$result = json_encode($result);
	    		echo $result;

	    	break;
	    	case 'delete_message':
	    		$message_id = $_POST['message_id'];
	    		wp_delete_post($message_id);

	    		$result['type'] = 'delete_message';
	    		$result = json_encode($result);
	    		echo $result;
	    		break;
	    	case 'save_edited_canned_response':
			    if (isset($_POST['response_id']) && isset($_POST['response'])) {
			        $post_id = intval($_POST['response_id']);
			        $response_content = $_POST['response'];
			        update_field('response', $response_content, $post_id);
			    }

			    $result['type'] = 'save_edited_canned_response';
			    $result = json_encode($result);
			    echo $result;

				break;
	    	case 'delete_canned_response':
	    		$response_id = $_POST['response_id'];
	    		wp_delete_post($response_id);

	    		$result['type'] = 'delete_canned_response';
	    		$result = json_encode($result);
	    		echo $result;
	    		break;
	    	case 'create_note':
	    		$consult_id = $_POST['consult_id'];
	    		$moderator = $_POST['moderator'];
	    		$note = $_POST['note'];
	    		update_history( $consult_id, 'note', '', $moderator, $note);

	    		$result['type'] = 'create_note';
	    		$result = json_encode($result);
	    		echo $result;
	    		break;
	    	case 'create_collab_note':
	    		$consult_id = $_POST['consult_id'];
	    		$moderator = $_POST['moderator'];
	    		$note = $_POST['note'];
	    		
	    		update_history( $consult_id, 'collab_note', '', $moderator, $note);

	    		$result['type'] = 'create_note';
	    		$result = json_encode($result);
	    		echo $result;
	    		break;
	    	case 'collaborate':
			    $consult_id = $_POST['consult_id'];
			    $collaborators = isset($_POST['collaborators']) ? $_POST['collaborators'] : []; // Check if 'collaborators' is set, if not initialize as an empty array


			    if (empty($collaborators)) {
			        // If $collaborators is empty, update the field to be an empty array
			        update_field('collaborators', [], $consult_id);
			    } else {
			       
			        foreach ($collaborators as $collaborator) {
			        	$is_collab = is_current_user_collaborator($consult_id, $collaborator);
			        	if(!$is_collab):
			        		$notification = "You have been invited to collaborate on Consult #$consult_id.";
			    			add_user_notification($collaborator, $notification);
			        	endif;
			        }

			        // Update the ACF field with the new collaborators
			        update_field('collaborators', $collaborators, $consult_id);
			    }

			    $result['type'] = 'collaborate';
			    $result = json_encode($result);
			    echo $result;
			    break;
	    	case 'delete_note':
	    		$row_id = $_POST['row_id'];
	    		$history_id = $_POST['history_id'];

	    		delete_history_log( $row_id, $history_id);

	    		$result['type'] = 'delete_note';
	    		$result = json_encode($result);
	    		echo $result;
	    		break;
	    	case 'notification_read':
	    		$notification_id = $_POST['notification_id'];
	    		$notification_row = $_POST['notification_row'];

	    		mark_notification_as_read( $notification_row, $notification_id);

	    		$result['type'] = 'notification_read';
	    		$result = json_encode($result);
	    		echo $result;
	    		break;	

	    	case 'all_notification_read':
	    		$notification_id = $_POST['notification_id'];

	    		mark_all_notifications_as_read($notification_id);

	    		$result['type'] = 'all_notification_read';
	    		$result = json_encode($result);
	    		echo $result;
	    		break;	
	    	default:
	    		// code...
	    		break;
	    }

	    wp_die();
	}
	add_action( 'wp_ajax_create_consult_message', 'create_consult_message' );
	add_action( 'wp_ajax_nopriv_create_consult_message', 'create_consult_message' );

	function get_updated_content() {
	  // Get the 'action' and 'consult_id' from the request
	  $action = isset($_REQUEST['action']) ? sanitize_text_field($_REQUEST['action']) : '';
	  $consult_id = isset($_REQUEST['consult_id']) ? sanitize_text_field($_REQUEST['consult_id']) : '';
	  $purpose = isset($_REQUEST['purpose']) ? sanitize_text_field($_REQUEST['purpose']) : '';
	  $extra_id = isset($_REQUEST['extra_id']) ? sanitize_text_field($_REQUEST['extra_id']) : '';

	  $current_user = wp_get_current_user();

	  switch ($purpose) {
	    case 'get_replies':
	      // Retrieve and return the updated content for the consult replies
	      // Modify this part according to your specific logic and data structure
	      $replies = get_replies($consult_id);
	      echo $replies;
	      break;
	    // Additional cases for other actions can be added here
	    case 'get_history':
	      $replies = '<button x-on:click="open = !open" type="button" class="p-4 py-2 border rounded border-solid cursor-pointer bg-white transition-all duration-200 ease-in-out transform focus:scale-105 hover:scale-105 hover:text-blue focus:text-blue">' . get_icon('history') . '</button><div x-show="open" x-on:click.away="open = false" class="absolute left-0 z-10 bg-white rounded-md shadow-xl divide-y divide-gray-100 mt-4 max-h-[180px] w-[32rem] overflow-y-auto">'.get_history($consult_id).'</div>';
	      echo $replies;
	      break;
	    case 'update_topic':
	      $replies = get_topic_list($consult_id, $extra_id);
	      echo $replies;
	      break;
	    case 'get_canned':
	      $replies = get_canned_responses($current_user, $consult_id);
	      echo $replies;
	      break;
	    default:
	      echo 'Invalid action';
	      break;
	  }

	  // Terminate the script to prevent additional output
	  wp_die();
	}

	// Hook the function to WordPress AJAX
	add_action('wp_ajax_get_updated_content', 'get_updated_content');
	add_action('wp_ajax_nopriv_get_updated_content', 'get_updated_content');


	function get_replies($consult_id){
		$output = '';

		$status = get_field('status', $consult_id);
		if (!is_array($status)) {
    		$status = array('label' => ucwords($status), 'value' => $status);
		}
		$args = array(
		    'post_type' => 'consult_messages',
		    'meta_key' => 'consult_id',
		    'orderby' => 'date',
		    'order' => 'ASC',
		    'posts_per_page' => -1,
		    'meta_query' => array(
		        array(
		            'key' => 'consult_id',
		            'value' => $consult_id,
		            'compare' => '='
		        )
		    )
		);

		$messages_query = new WP_Query($args);

		if ($messages_query->have_posts()) {
		    while ($messages_query->have_posts()) {
		        $messages_query->the_post();
		        $message = get_field('message');
		        $submitted_by = get_field('submitted_by');
		        $user = get_user_by( 'ID', $submitted_by );
		        $current_user = get_current_user_id();
		        $date_published = get_the_date('M jS, Y g:ia');
		        $message_id = get_the_ID();
		        $moderators_assigned = get_field('moderators_assigned');
		        $moderators_list = implode(',', $moderators_assigned);


		        if($message):
				    $output .= '<div class="p-8 even:bg-white space-y-4">';
					    $output .= '<div class="text-text space-y-2 border-b pb-4" id="consult-'.$consult_id.'-message-'.get_the_id().'">';
					    	$output .= $message;
					    $output .= '</div>';
					    $output .= '<div class="flex items-center justify-between text-xs text-text">';
						    $output .= '<div id="consult-'.$consult_id.'-message-'.get_the_id().'-credit">';
							if ($user) {
							    $signature = get_moderator_signature($submitted_by);
							    if (empty($signature)) {
							        $output .= '<span>' . $user->display_name . ' | ';
							        $output .= '<span class="font-normal text-gray-400">' . $date_published . '</span>';

							    } else {
							        $output .= $signature;
							        $output .= '<span class="font-normal mt-2 block text-gray-400">' . $date_published . '</span>';
							    }
							}
							
							$output .= '</div>';
						    if (is_user_allowed_to_view_message_tools($submitted_by)) {
							    $output .= '<div class="message-tools text-xs space-x-2" x-data="{currentPopup: window.currentPopup || null}">';
							    	if(is_user_mod()):
								        $output .= '<div class="relative inline-block">';
								            $output .= '<button @click="currentPopup = currentPopup === \'saveMessage\' ? null : \'saveMessage\'; closeEditScreens();" class="hover:text-yellow focus:text-yellow inline-block transition-all duration-200 ease-in-out">'.get_icon('clipboard').'</button>';
								            $output .= '<div x-show="currentPopup === \'saveMessage\'" class="absolute right-0 text-text top-10 z-50 bg-white rounded-lg p-4 shadow-md w-[32rem]" x-on:click.away="currentPopup = null; $refs.form.reset();">';
								                $output .= '<form class="space-y-2" id="consult-'.$consult_id.'-message-'.$message_id.'-save" method="post" x-ref="form" x-on:submit="currentPopup = null;">
								                    <input type="text" name="title" id="title" placeholder="Title" class="w-full border rounded border-gray-100 px-4 py-2 focus:outline-none focus:border-blue transition-all duration-200 ease-in-out">
								                    <div class="border rounded border-gray-100 p-4 overflow-y-auto max-h-32">'.$message.'</div>
								                    <input type="submit" class="border rounded uppercase tracking-wide font-bold text-xs px-6 py-3 cursor-pointer mt-4 text-blue bg-white hover:border-gray-400 focus:border-gray-400 transition-all duration-200 ease-in-out transform focus:scale-105 hover:scale-105" value="Save">
								                    <input type="hidden" name="consult_id" value="'.$consult_id.'">
								                    <input type="hidden" name="submitted_by" value="' . $submitted_by . '">
								                </form>';
								            $output .= '</div>';
								        $output .= '</div>';
								    endif;
							        $output .= '<div class="relative inline-block">';
							            $output .= '<button x-on:click="editMessage(' . $message_id . ', ' . $consult_id . ', ' . $submitted_by . ', ' . $moderators_list .');" class="hover:text-green focus:text-green inline-block transition-all duration-200 ease-in-out">'.get_icon('edit').'</button>';
							        $output .= '</div>';
							        $output .= '<div class="relative inline-block">';
							            $output .= '<button @click="currentPopup = currentPopup === \'deleteMessage\' ? null : \'deleteMessage\'; closeEditScreens();" class="hover:text-pink focus:text-pink inline-block transition-all duration-200 ease-in-out">'.get_icon('trash').'</button>';
							            $output .= '<div x-show="currentPopup === \'deleteMessage\'" class="absolute right-0 top-10 z-50 bg-white rounded-lg p-4 shadow-md" x-on:click.away="currentPopup = null;">';
										    $output .= '<div class="text-center mb-4">Delete message?</div>';
										    $output .= '<div class="flex justify-center space-x-4">';
										        $output .= '<button class="px-4 text-pink font-bold transition-all duration-200 ease-in-out hover:underline" x-on:click="showDeleteMessage = false; deleteMessage(' . $message_id . ', ' . $consult_id . ');">Yes</button>';
										        $output .= '<button class="px-4 text-text font-bold transition-all duration-200 ease-in-out hover:underline" x-on:click="currentPopup = null">No</button>';
										    $output .= '</div>';
										$output .= '</div>';
							        $output .= '</div>';
							    $output .= '</div>';
							}
					    $output .= '</div>';
				    $output .= '</div>';
						endif;
		    }
		} else {
			if(is_not_closed($status['value']) ):
		    	$output .= '<div class="p-8 text-center text-gray-400 text-sm font-bold">';
		    	$output .= 'Add a message below to get started!';
		    	$output .= '</div>';
		  endif;
		}

		wp_reset_postdata();

		return $output;

	}
	function get_history($consult_id) {
	    $output = '';
	    $current_user = wp_get_current_user();
	    $history_query = new WP_Query(
	        array(
	            'post_type' => 'consult_history',
	            'meta_query' => array(
	                array(
	                    'key' => 'consult_id',
	                    'value' => $consult_id,
	                    'compare' => '=',
	                ),
	            ),
	        )
	    );

	    if ($history_query->have_posts()) {
	    	$mod_only_notes_exist = false;
	    	$history_exists = false; // New variable to track if any history exists
	        $output .= '<ul class="text-sm divide-y">';
	        while ($history_query->have_posts()) {
	            $history_query->the_post();
	            $history_log = get_field('history_log');
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
	                    if ($mod_only && is_user_mod()) {
	                    	$mod_only_notes_exist = true;
	                    	$history_exists = true; // Update history_exists to true
	                    
                    		$output .= '<li class="px-4 py-2 bg-light-blue"><strong>' . $user['display_name'] . '</strong> ' . $action_taken . ' - <span class="text-xs">' . $date_time_formatted . ' (Moderator Only)</span>';
                    		$output .= '<div class="flex items-center justify-between w-full">';
                    		if($note){ $output .= '<div class="p-4 italic border-l-4 mt-2">'.$note.'</div>'; }
                    		if($user['ID'] == $current_user->ID){
                    			$output .= '<button class="opacity-40 hover:opacity-100 focus:opacity-100 hover:text-pink focus:text-pink transition-all duration-200 ease-in-out" x-on:click="deleteNote(' . $row_id . ', ' . get_the_ID() . ', '.$consult_id.');">'.get_icon('trash').'</button>';
                    		}
                    		$output .= '</div>';
                    		$output .= '</li>';

	                    } elseif(!$mod_only) {
	                    	$history_exists = true; // Update history_exists to true
	                    	if(isset($user['display_name'])):
	                    		$output .= '<li class="px-4 py-2 even:bg-gray-100"><strong>' . $user['display_name'] . '</strong> ' . $action_taken . ' - <span class="text-xs">' . $date_time_formatted . '</span></li>';
	                    	endif;
	                    }
	                    
	                }
	            }
	        }
	        $output .= '</ul>';
	        if (!$history_exists) { // Check if $history_exists is false instead of $mod_only_notes_exist
					$output .= '<div class="px-4 py-2">No history found.</div>';
				}
	    } else {
	        $output .= '<div class="px-4 py-2">No history found.</div>';
	    }

	    wp_reset_postdata();

	    return $output;
	}
	function update_history($consult_id, $action, $status=NULL, $moderator=NULL, $note=NULL) {
	    switch ($action) {
	        case 'create':
	            $new_history_post_id = create_history_post($consult_id);
	            $submitted_user_id = get_submitted_by_user_id($consult_id);
	            $date_created = get_post_time('U', false, $consult_id, true);

	            // set first history log item
	            $history_log = array(create_history_log($submitted_user_id, 'created a consult', $date_created));

	            update_field('consult_id', $consult_id, $new_history_post_id);
	            update_field('history_log', $history_log, $new_history_post_id);
	            break;
	        case 'status':
	            // find corresponding consult_history and add history log entry
	            $consult_history = get_history_posts($consult_id);
	            if ($consult_history) {
	            	$current_status = get_field('status', $consult_id);
	            	if ($status != $current_status['value']) {
		                $history_log = create_history_log(get_current_user_id(), 'changed the status to ' . ucwords(str_replace('-', ' ', $status)), current_time('timestamp'));
		                $history_logs = get_field('history_log', $consult_history[0]->ID);
		                if (!$history_logs) {
		                    $history_logs = array();
		                }
		                array_unshift($history_logs, $history_log);
		                update_field('history_log', $history_logs, $consult_history[0]->ID);
		            }
	            } else {
	                $new_history_post_id = create_history_post($consult_id);

	                $current_status = get_field('status', $consult_id);
	            	if ($status != $current_status['value']) {
		                $history_log = array(create_history_log(get_current_user_id(), 'changed the status to ' . ucwords(str_replace('-', ' ', $status)), current_time('timestamp'))
		                );

		                update_field('consult_id', $consult_id, $new_history_post_id);
		                update_field('history_log', $history_log, $new_history_post_id);
		            }
	            }
	            break;
	        case 'moderator':
			    // find corresponding consult_history and add history log entry
			    $consult_history = get_history_posts($consult_id);

			    if ($consult_history) {
			        $history_logs = get_field('history_log', $consult_history[0]->ID);
			        $user_assigned_to = get_userdata($moderator)->display_name;
			        if (!(get_current_user_id() == $moderator)) {
			            $history_log = create_history_log(get_current_user_id(), 'assigned this consult to ' . $user_assigned_to, current_time('timestamp'));
			            if (!$history_logs) {
				            $history_logs = array();
				        }
				        $last_history_log = reset($history_logs);
						$user_assigned_to = get_userdata($moderator)->display_name;
						$new_history_log = create_history_log(get_current_user_id(), 'assigned this consult to ' . $user_assigned_to, current_time('timestamp'));
						if (!$last_history_log || $last_history_log['action_taken'] != $new_history_log['action_taken']) {
						    array_unshift($history_logs, $new_history_log);
						    update_field('history_log', $history_logs, $consult_history[0]->ID);
						}
			        }
			    } else {
			        $new_history_post_id = create_history_post($consult_id);

			        $user_assigned_to = get_userdata($moderator)->display_name;
			        if (!(get_current_user_id() == $moderator)) {
			            $history_log = create_history_log(get_current_user_id(), 'assigned this consult to ' . $user_assigned_to, current_time('timestamp'));

			            update_field('consult_id', $consult_id, $new_history_post_id);
			        	update_field('history_log', array($history_log), $new_history_post_id);
			        }  
			    }
	            break;
	        case 'note':
			    // find corresponding consult_history and add history log entry
	            $consult_history = get_history_posts($consult_id);
	            if ($consult_history) {
	                $history_log = create_history_log(get_current_user_id(), 'left a note', current_time('timestamp'), TRUE, $note);
	                $history_logs = get_field('history_log', $consult_history[0]->ID);
	                if (!$history_logs) {
	                    $history_logs = array();
	                }
	                array_unshift($history_logs, $history_log);
	                update_field('history_log', $history_logs, $consult_history[0]->ID);
	            } else {
	                $new_history_post_id = create_history_post($consult_id);
	                $history_log = array(create_history_log(get_current_user_id(), 'left a note', current_time('timestamp'), TRUE, $note));

	                update_field('consult_id', $consult_id, $new_history_post_id);
	                update_field('history_log', $history_log, $new_history_post_id);
	            }
	            break;
	        case 'collab_note':
			    // find corresponding consult_history and add history log entry
	            $consult_history = get_history_posts($consult_id);
	            if ($consult_history) {
	                $history_log = create_history_log(get_current_user_id(), 'shared this consult', current_time('timestamp'), TRUE, $note);
	                $history_logs = get_field('history_log', $consult_history[0]->ID);
	                if (!$history_logs) {
	                    $history_logs = array();
	                }
	                array_unshift($history_logs, $history_log);
	                update_field('history_log', $history_logs, $consult_history[0]->ID);
	            } else {
	                $new_history_post_id = create_history_post($consult_id);
	                $history_log = array(create_history_log(get_current_user_id(), 'shared this consult', current_time('timestamp'), TRUE, $note));

	                update_field('consult_id', $consult_id, $new_history_post_id);
	                update_field('history_log', $history_log, $new_history_post_id);
	            }
	            break;

	        case 'admin_moderator_change':
			    // find corresponding consult_history and add history log entry
			    $consult_history = get_history_posts($consult_id);

			    if ($consult_history) {
			        $history_logs = get_field('history_log', $consult_history[0]->ID);
			        $user_assigned_to = get_userdata($moderator)->display_name;
			        if (!$history_logs) {
			            $history_logs = array();
			        }
			        $last_history_log = reset($history_logs);

			        // set first history log item
			        $history_log = array(create_history_log(get_current_user_id(), '(Admin) has assigned this consult to ' . $user_assigned_to . ' (Moderator)', current_time('timestamp')));
			        $new_history_log = create_history_log(get_current_user_id(), '(Admin) has assigned this consult to ' . $user_assigned_to . ' (Moderator)', current_time('timestamp'));
					if (!$last_history_log || $last_history_log['action_taken'] != $new_history_log['action_taken']) {
					    array_unshift($history_logs, $new_history_log);
					    update_field('history_log', $history_logs, $consult_history[0]->ID);
					}

			    } else {
			        $new_history_post_id = create_history_post($consult_id);

			        $user_assigned_to = get_userdata($moderator)->display_name;
			        // set first history log item
			        $history_log = array(create_history_log(get_current_user_id(), '(Admin) has assigned this consult to ' . $user_assigned_to . ' (Moderator)', current_time('timestamp')));
			        update_field('consult_id', $consult_id, $new_history_post_id);
			        update_field('history_log', $history_log, $new_history_post_id);
			    }
			break;

	        default:
	            // handle default case
	            break;
	    }
	}
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
	function render_mod_login() {
	    $redirect_url = '/wp/wp-login.php?redirect_to=https%3A%2F%2Fconsult.smiadviser.org%2Fmoderator-dashboard';

	    // Redirect user to the specified URL
	    wp_redirect($redirect_url);
	    exit; // Important to call exit after redirection
	}
	function render_user_login(){
		echo '<section class="flex items-center justify-center h-screen">';
	    echo '<div class="w-full max-w-xs">';
	    echo '<div class="bg-white rounded p-8 space-y-4 text-center">
			<h1 class="text-center text-gray-700 font-bold">Consult Login</h1>
			<div class="space-x-4 md:space-x-0 md:space-y-4">
				<a href="#" class="inline-block bg-blue px-8 py-3 rounded-full text-white font-bold uppercase tracking-wider text-sm">Create Account</a>
				<a href="#" class="inline-block bg-green px-8 py-3 rounded-full text-white font-bold uppercase tracking-wider text-sm">Returning User</a>
			</div>
		</div>';
	    echo '</div>';
	    echo '</section>';
	}
	function render_non_access(){
		echo '<section class="flex items-center justify-center h-screen">';
	    echo '<div class="w-full max-w-xs">';
	    echo '<div class="bg-white rounded p-8 space-y-4 text-center">
			<h1 class="text-center text-gray-700 font-bold">You Don\'t Have Access To This</h1>
		</div>';
	    echo '</div>';
	    echo '</section>';
	}
	function hide_admin_bar_for_support_users() {
	    if (current_user_can('support_user') || current_user_can('support_agent')) {
	        add_filter('show_admin_bar', '__return_false');
	    }
	}
	add_action('init', 'hide_admin_bar_for_support_users');

	function get_topic_list($consult_id, $child=false) {

		$output = '';
		$args = array(
		    'taxonomy' => 'consult_topic',
		    'hide_empty' => false,
		    'parent' => $child
		);

	    $topics = get_terms($args);

	    if(!$child){
	    	$output .= "<button x-on:click=\"topic = 'None'; topic_id='0'; subtopic = 'None'; open = false; updateTopic('0', '{$consult_id}', 'topic'); updateTopic('0', '{$consult_id}', 'subtopic');\" :class=\"'block px-4 py-2 w-full text-left text-sm text-gray-700 hover:bg-gray-100 hover:text-gray-900 rounded'\">None</button>";

			foreach ($topics as $topic) {
		    	$output .= "<button x-on:click=\"topic = '{$topic->name}'; topic_id='{$topic->term_id}'; subtopic = 'None'; open = false; updateTopic('{$topic->term_id}', '{$consult_id}', 'topic');\" :class=\"'block px-4 py-2 w-full text-left text-sm text-gray-700 hover:bg-gray-100 hover:text-gray-900 rounded'\">{$topic->name}</button>";
		    }
		} else{
			$output .= "<button x-on:click=\"subtopic = 'None'; topic_id='0'; open = false; updateTopic('0', '{$consult_id}', 'subtopic');\" :class=\"'block px-4 py-2 w-full text-left text-sm text-gray-700 hover:bg-gray-100 hover:text-gray-900 rounded'\">None</button>";
			foreach ($topics as $topic) {
		    	$output .= "<button x-on:click=\"subtopic = '{$topic->name}'; subtopic_id='{$topic->term_id}'; open = false; updateTopic('{$topic->term_id}', '{$consult_id}', 'subtopic');\" :class=\"'block px-4 py-2 w-full text-left text-sm text-gray-700 hover:bg-gray-100 hover:text-gray-900 rounded'\">{$topic->name}</button>";
		    }
		}

		return $output;
	}
	function get_support_agents($consult_id, $current_user_id) {
	    $output = '';
	    $collaborators = get_field('collaborators', $consult_id); // Get the collaborators field

	    $agents = get_users(array(
	        'role' => 'support_agent'
	    ));

	    foreach ($agents as $agent) {
	        if ($current_user_id !== $agent->ID) { // Skip the current user
	            $isChecked = '';
	            if (is_array($collaborators) && in_array($agent->ID, $collaborators)) {
	                $isChecked = 'checked';
	            }
	            $output .= '<div class="flex items-center w-full space-x-2">';
	            $output .= '<input type="checkbox" name="collaborators[]" value="' . $agent->ID . '" id="moderator-' . $agent->ID . '" ' . $isChecked . '>';
	            $output .= '<label for="moderator-' . $agent->ID . '" class="text-sm text-text cursor-pointer py-2 rounded bg-white hover:text-blue focus:bg-blue">' . $agent->display_name . '</label>';
	            $output .= '</div>';
	        }
	    }

	    return $output;
	}
	function get_mod_message($consult_id, $moderator, $button_label, $collaborate) {
	    ob_start();
	    ?>
	    <form method="post" x-ref="form" x-on:submit="open = false;">
	    	<?php if ($collaborate) {
	    		echo '<div class="text-xl font-bold text-text mb-2">Collaborate</div>';
	            echo '<div class="divide-y divide-gray-200">' . get_support_agents($consult_id, $moderator) . '</div>';
	        } ?>
	        <textarea name="note" placeholder="Add A Note" class="w-full border rounded border-gray-100 p-4 mt-4 focus:outline-none focus:border-blue transition-all duration-200 ease-in-out"></textarea>
	        <input type="submit" class="border rounded uppercase tracking-wide font-bold text-xs px-6 py-3 cursor-pointer mt-4 text-blue bg-white hover:border-gray-400 focus:border-gray-400 transition-all duration-200 ease-in-out transform focus:scale-105 hover:scale-105" value="<?php echo esc_attr($button_label); ?>">
	        <?php if ($collaborate) {
	            echo '<div class="inline-flex items-center mt-4 text-xs text-text">'.get_icon('info').'<span class="ml-2">Deselect all moderators and click the Collaborate button to remove them from the consult.</span></div>';
	        } ?>
	        <input type="hidden" name="consult_id" value="<?php echo esc_attr($consult_id); ?>">
	        <input type="hidden" name="moderator" value="<?php echo esc_attr($moderator); ?>">
	        
	    </form>
	    <?php
	    return ob_get_clean();
	}
	function get_canned_responses($current_user, $consult_id) {

		$output = '';
	    // Check if a valid WP_User object is provided
	    if (!($current_user instanceof WP_User)) {
	        return new WP_Error('invalid_user', 'The provided $current_user is not a valid WP_User object.');
	    }

	    // Prepare the WP_Query arguments
	    $args = array(
	        'post_type' => 'consult_response',
	        'post_status' => 'publish',
	        'posts_per_page' => -1,
	        'meta_query' => array(
	            array(
	                'key' => 'author',
	                'value' => $current_user->ID,
	                'compare' => '='
	            )
	        )
	    );

	    // Perform the WP_Query
	    $query = new WP_Query($args);

	    // Check if any posts are found
	    if (!$query->have_posts()) {
	        return '<div class="p-4 text-text">No Responses Saved</div>';
	    }

	    // Prepare an array to hold the results
	    $canned_responses = array();

	    // Loop through the posts and add them to the results array
	    while ($query->have_posts()) {
	        $query->the_post();
	        
	        $response_id = get_the_ID();

	        $output .= '<div class="even:bg-gray-100 p-4 text-text space-y-4 divide-y">';
	        	$output .= '<div class="space-y-2">';
			        $output .= '<div class="font-bold text-lg">' . get_the_title() . '</div>';
			        $output .= '<div class="text-sm space-y-4" id="consult-'.$consult_id.'-canned-response-'.$response_id.'">' . get_field('response') . '</div>';
		        $output .= '</div>';
		        $output .= '<div class="text-text pt-4">';
		        	$output .= '<button class="px-2 hover:text-green focus:text-green transition-all duration-200 ease-in-out" x-on:click="addToEditor(\''.$consult_id.'\', \''.$response_id.'\'); open = false;">'.get_icon('plus').'</button>';
		        	$output .= '<button class="px-2 hover:text-blue focus:text-blue transition-all duration-200 ease-in-out" x-on:click="editCannedResponse(\''.$consult_id.'\', \''.$response_id.'\');">'.get_icon('edit').'</button>';
			        $output .= '<button class="px-2 hover:text-pink focus:text-pink transition-all duration-200 ease-in-out" x-on:click="deleteCannedResponse(\''.$consult_id.'\', \''.$response_id.'\');">'.get_icon('trash').'</button>';
		        $output .= '</div>';
	        $output .= '</div>';
	    }

	    // Reset the post data
	    wp_reset_postdata();

	    // Return the array of canned responses
	    return $output;
	}
	function get_status_color($status){
		switch ($status) {
	      case 'New':
	      	$color = 'green';
	          break;
	      case 'In Progress':
	      	$color = 'blue';
	          break;
	      case 'Old':
	      	$color = 'yellow';
	          break;
	      case 'Closed':
	      	$color = 'pink';
	          break;
	      default:
	      	$color = 'green';
	          break;
	    }
	    return $color;
	}
	function get_user_signature($current_user, $consult_id) {
	    $output = '';

	    // Check if a valid WP_User object is provided
	    if (!($current_user instanceof WP_User)) {
	        return new WP_Error('invalid_user', 'The provided $current_user is not a valid WP_User object.');
	    }

	    // Get the user's signature from the user meta
	    $signature = get_the_author_meta('signature', $current_user->ID);

	    // Render the signature editor with buttons
	    $output .= '<div class="text-text">';
	    $output .= '<textarea class="block text-sm mb-2 p-2 border w-full" id="signature-' . $consult_id . '" rows="4" cols="50">' . esc_textarea($signature) . '</textarea>';
	    $output .= '<div class="flex items-center">';
		    //To Delete $output .= '<button type="button" class="px-2 hover:text-green focus:text-green transition-all duration-200 ease-in-out" x-on:click="addSignatureToEditor(\'' . $consult_id . '\', \'' . $current_user->ID . '\'); open = false;">'.get_icon('plus').'</button>';
		    $output .= '<button id="save-signature-' . $consult_id . '" type="button" class="px-2 hover:text-blue focus:text-blue transition-all duration-200 ease-in-out" x-on:click="saveSignature(\'' . $consult_id . '\', \'' . $current_user->ID . '\');">'.get_icon('save').'</button>';
		$output .= '</div>';
	    $output .= '</div>';

	    return $output;
	}
	// Add the textarea field to the user profile page
	function add_user_signature_field($user) {
	    ?>
	    <h2><?php esc_html_e('Signature', 'text-domain'); ?></h2>
	    <table class="form-table">
	        <tr>
	            <th><label for="signature"><?php esc_html_e('Signature', 'text-domain'); ?></label></th>
	            <td>
	                <textarea name="signature" id="signature" rows="4" cols="50"><?php echo esc_textarea(get_user_meta($user->ID, 'signature', true)); ?></textarea>
	            </td>
	        </tr>
	    </table>
	    <?php
	}
	add_action('show_user_profile', 'add_user_signature_field');
	add_action('edit_user_profile', 'add_user_signature_field');
	function save_user_signature() {
	  if (isset($_POST['consult_id']) && isset($_POST['user_id']) && isset($_POST['signature'])) {
	    $consult_id = intval($_POST['consult_id']);
	    $user_id = intval($_POST['user_id']);
	    $signature = wp_kses_post($_POST['signature']);

	    // Update the user's signature in the user meta
	    update_user_meta($user_id, 'signature', $signature);

	    $result['type'] = 'save_user_signature';
	    $result = json_encode($result);
	    echo $result;
	  }

	  wp_die();
	}
	add_action('wp_ajax_save_user_signature', 'save_user_signature');
	add_action('wp_ajax_nopriv_save_user_signature', 'save_user_signature');

	function get_moderator_signature($user) {
	    // Retrieve the moderator's signature from the backend
	    $signature = get_user_meta($user, 'signature', true);

	    return wpautop($signature);
	}

	function update_user_profile() {
	  if (isset($_POST['user_id'])) {
	    $user_id = intval($_POST['user_id']);
	    $first_name = $_POST['first_name'];
	    $last_name = $_POST['last_name'];
	    $phone_number = (isset($_POST['phone_number']) ? $_POST['phone_number'] : '');
	    $organization = (isset($_POST['organization']) ? $_POST['organization'] : '');
	    $organization_zip_code = (isset($_POST['organization_zip_code']) ? $_POST['organization_zip_code'] : '');

	    if($first_name): update_user_meta($user_id, 'first_name', $first_name); endif;
	    if($last_name): update_user_meta($user_id, 'last_name', $last_name); endif;
	    update_user_meta($user_id, 'phone_number', $phone_number);
	    update_user_meta($user_id, 'organization', $organization);
	    update_user_meta($user_id, 'organization_zip_code', $organization_zip_code);

	    $result['type'] = 'update_user_profile';
	    $result = json_encode($result);
	    echo $result;
	  }

	  wp_die();
	}
	add_action('wp_ajax_update_user_profile', 'update_user_profile');
	add_action('wp_ajax_nopriv_update_user_profile', 'update_user_profile');

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
	function toggle_archived_consults() {
	    if (!isset($_POST['archived'])) {
	        echo json_encode(array('type' => 'error', 'message' => 'Archived state not provided.'));
	        wp_die();
	        return;
	    }

	    $archived = $_POST['archived'] == "true"; // Make sure to convert the string to boolean
	    $user_dash = $_POST['user_dash'] == "true";

	    // Start output buffering
	    ob_start();

	    // Respond with a result and the new table HTML
	    $result['archived'] = $archived;
	    $result['user_dash'] = $user_dash;
	    $result['type'] = 'toggle_archived_consults';
	    echo json_encode($result);

	    wp_die();
	}
	add_action('wp_ajax_toggle_archived_consults', 'toggle_archived_consults');
	add_action('wp_ajax_nopriv_toggle_archived_consults', 'toggle_archived_consults');



