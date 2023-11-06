<?php get_header(); ?>
<main>
	<?php
		if ( is_user_logged_in() ) {
		    // User is logged in
		    $current_user = wp_get_current_user();
		    $user_roles = $current_user->roles;

		    // Check user role
		    if ( in_array( 'administrator', $user_roles ) || in_array( 'support_agent', $user_roles ) ) { ?>
		        <section>
					<div class="grid grid-cols-12">
						<div class="col-span-2 relative order-1 md:order-none z-10">
							<div class="sticky top-0 h-screen">
								<?php echo get_main_bar(); ?>
							</div>
						</div>
						<div class="bg-background col-span-10 min-h-screen" x-data="{ archived: false }">
							<?php echo get_toolbar(); ?>
							<div>
								<div class="relative py-8 px-4 md:px-8 space-y-8" id="consult-dock">
									<?php echo generate_consults_table(); ?>
								</div>
							</div>
						</div>
					</div>
				</section>
		    <?php } elseif ( in_array( 'support_user', $user_roles ) ) {
		        render_non_access();
		    } else {
		        render_non_access();
		    }
		} else {
		    render_mod_login();
		}
	?>
</main>
<?php get_footer(); ?>