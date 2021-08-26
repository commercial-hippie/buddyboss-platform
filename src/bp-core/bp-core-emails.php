<?php
/**
 * BuddyBoss Core Email Setup.
 *
 * @package BuddyBoss\Core
 * @since BuddyBoss 1.0.0
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;
/**
 * Setup the bp-core-email-tokens component.
 *
 * @since BuddyBoss 1.0.0
 */
function bp_setup_core_email_tokens() {

	if ( function_exists( 'bp_email_queue' ) ) {
		bp_email_queue();
	}

	new BP_Email_Tokens();
}
add_action( 'bp_init', 'bp_setup_core_email_tokens', 0 );

/**
 * Set content type for all generic email notifications.
 *
 * @since BuddyBoss 1.0.0
 */
function bp_email_set_content_type() {
	return 'text/html';
}

/**
 * Output template for email notifications.
 *
 * @since BuddyBoss 1.0.0
 */
function bp_email_core_wp_get_template( $content = '', $user = false ) {
	ob_start();

	// Remove 'bp_replace_the_content' filter to prevent infinite loops.
	remove_filter( 'the_content', 'bp_replace_the_content' );

	set_query_var( 'email_content', $content );
	set_query_var( 'email_user', $user );
	bp_get_template_part( 'assets/emails/wp/email-template' );

	// Remove 'bp_replace_the_content' filter to prevent infinite loops.
	add_filter( 'the_content', 'bp_replace_the_content' );

	// Get the output buffer contents.
	$output = ob_get_clean();

	return $output;
}

/**
 * Function to load the instance of the class BP_Email_Queue.
 *
 * @since BuddyBoss 1.7.7
 *
 * @return null|BP_Email_Queue|void
 */
function bp_email_queue() {
	if ( class_exists( 'BP_Email_Queue' ) ) {
		global $bp_email_queue;
		$bp_email_queue = BP_Email_Queue::instance();

		return $bp_email_queue;
	}
}
