<?php

defined( 'ABSPATH' ) || exit;

require_once ABSPATH . 'wp-admin/admin.php';

/**
 * @global string $typenow The post type of the current screen.
 */
global $typenow;

if ( ! $typenow ) {
	wp_die( __( 'Invalid post type.' ) );
}

if ( ! in_array( $typenow, get_post_types( array( 'show_ui' => true ) ), true ) ) {
	wp_die( __( 'Sorry, you are not allowed to edit posts in this post type.' ) );
}

if ( 'attachment' === $typenow ) {
	if ( wp_redirect( admin_url( 'upload.php' ) ) ) {
		exit;
	}
}

/**
 * @global string       $post_type
 * @global WP_Post_Type $post_type_object
 */
global $post_type, $post_type_object;

$post_type        = $typenow;
$post_type_object = get_post_type_object( $post_type );

if ( ! $post_type_object ) {
	wp_die( __( 'Invalid post type.' ) );
}

if ( ! current_user_can( $post_type_object->cap->edit_posts ) ) {
	wp_die(
		'<h1>' . __( 'You need a higher level of permission.' ) . '</h1>' .
		'<p>' . __( 'Sorry, you are not allowed to edit posts in this post type.' ) . '</p>',
		403
	);
}

require_once ABSPATH . 'wp-admin/admin-header.php';

require_once ABSPATH . 'wp-admin/admin-footer.php';
