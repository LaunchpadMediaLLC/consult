<?php get_header(); ?>
<main>
	<?php 
		if ( is_user_logged_in() ) {
		    // User is logged in
		    $current_user = wp_get_current_user();
		    $user_roles = $current_user->roles;

		    // Check user role
		    if ( in_array( 'administrator', $user_roles ) || in_array( 'support_user', $user_roles ) || in_array( 'support_agent', $user_roles ) ) { ?>
		    	<section>
					<div class="grid grid-cols-12">
						<div class="col-span-12 md:col-span-2 relative z-10">
							<div class="sticky top-0 md:h-screen">
								<?php echo get_main_bar(); ?>
							</div>
						</div>
						<div class="relative bg-background col-span-12 md:col-span-10 min-h-screen" x-data="{ archived: false }">
							<?php echo get_toolbar(true); ?>
							<div>
								<div class="py-8 px-4 md:px-8 space-y-8" id="consult-dock">
									<?php 
										if (isset($_GET['consult']) && $_GET['consult'] === 'success') {
										    // Display HTML text
										    echo '<div class="w-full p-8 py-6 text-center border border-green rounded bg-light-green text-sm font-bold text-green">Your consult has been submitted and will be assigned shortly. Keep an eye on your email for updates.</div>';
										}
										echo generate_consults_table(true);
										
										echo get_pending_consults();
									?>
								</div>
							</div>
						</div>
					</div>
				</section>
		    <?php } else {
		        // User has other role(s)
		        // Do something else
		        render_non_access();
		    }
		} else {
		    // User is not logged in
		    // Do something else
		    render_user_login();
		}
	?>
</main>
<?php get_footer(); ?>