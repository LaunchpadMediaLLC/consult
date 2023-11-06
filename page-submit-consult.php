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
						<div class="col-span-2 relative order-1 md:order-none z-10">
							<div class="sticky top-0 h-screen">
								<?php echo get_main_bar(); ?>
							</div>
						</div>
						<div class="bg-main col-span-10 min-h-screen">
							<div class="container mx-auto">
								<div class="py-8 px-4 md:px-8">
									<div class="p-8 bg-white rounded border border-solid">
										<?php echo do_shortcode( '[gravityform id="1" title="false" ajax="true"]', false ); ?>
									</div>
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