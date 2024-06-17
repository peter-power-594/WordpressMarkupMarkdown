<?php
/**
 * Edit post administration panel.
 *
 * Manage Post actions: post, edit, delete, etc.
 *
 * @package WordPress
 * @subpackage Administration
 */

/** WordPress Administration Bootstrap */
require_once ABSPATH . 'wp-admin/admin.php';

$parent_file  = 'edit.php';
$submenu_file = 'edit.php';

wp_reset_vars( array( 'action' ) );

if ( isset( $_GET['post'] ) && isset( $_POST['post_ID'] ) && (int) $_GET['post'] !== (int) $_POST['post_ID'] ) {
	wp_die( __( 'A post ID mismatch has been detected.' ), __( 'Sorry, you are not allowed to edit this item.' ), 400 );
} elseif ( isset( $_GET['post'] ) ) {
	$post_id = $_GET['post'];
} elseif ( isset( $_POST['post_ID'] ) ) {
	$post_id = $_POST['post_ID'];
} else {
	$post_id = 0;
}
$post_ID = $post_id;

/**
 * @global string  $post_type
 * @global object  $post_type_object
 * @global WP_Post $post             Global post object.
 */
global $post_type, $post_type_object, $post;

if ( $post_id ) {
	$post = new \MarkupMarkdown\Addons\Unsupported\MarkdownPost( $post_id );
}

if ( $post ) {
	$post_type        = $post->post_type;
	$post_type_object = get_post_type_object( $post_type );
}

if ( isset( $_POST['post_type'] ) && $post && $post_type !== $_POST['post_type'] ) {
	wp_die( __( 'A post type mismatch has been detected.' ), __( 'Sorry, you are not allowed to edit this item.' ), 400 );
}

if ( post_type_supports( $post_type, 'comments' ) ) {
	wp_enqueue_script( 'admin-comments' );
	enqueue_comment_hotkeys_js();
}

require_once ABSPATH . 'wp-admin/admin-header.php';

?>

<div class="wrap">
<h1><?php _e( 'Edit' ); if ( $post->post_type !== 'post' ) : _e( $post->post_type ); endif; ?></h1>
<hr class="wp-header-end">
<form name="post" action="post.php" method="post" id="post">
<?php wp_nonce_field( $nonce_action ); ?>
<input type="hidden" id="user-id" name="user_ID" value="<?php echo (int) $user_ID; ?>" />
<input type="hidden" id="hiddenaction" name="action" value="<?php echo esc_attr( $form_action ); ?>" />
<input type="hidden" id="originalaction" name="originalaction" value="<?php echo esc_attr( $form_action ); ?>" />
<input type="hidden" id="post_author" name="post_author" value="<?php echo esc_attr( $post->post_author ); ?>" />
<input type="hidden" id="post_type" name="post_type" value="<?php echo esc_attr( $post_type ); ?>" />
<input type="hidden" id="original_post_status" name="original_post_status" value="<?php echo esc_attr( $post->post_status ); ?>" />
<input type="hidden" id="referredby" name="referredby" value="<?php echo $referer ? esc_url( $referer ) : ''; ?>" />
<?php

echo $form_extra;

wp_nonce_field( 'meta-box-order', 'meta-box-order-nonce', false );
wp_nonce_field( 'closedpostboxes', 'closedpostboxesnonce', false );
?>
<div id="poststuff">
	<div id="post-body" class="metabox-holder columns-<?php echo ( 1 === get_current_screen()->get_columns() ) ? '1' : '2'; ?>">
		<div id="post-body-content">


		<?php if ( post_type_supports( $post_type, 'title' ) ) : ?>
			<div id="titlediv">
				<div id="titlewrap">
<?php
/**
 * Filters the title field placeholder text.
 *
 * @since 3.1.0
 *
 * @param string  $text Placeholder text. Default 'Add title'.
 * @param WP_Post $post Post object.
 */
$title_placeholder = apply_filters( 'enter_title_here', __( 'Add title' ), $post );
?>
					<label class="screen-reader-text" id="title-prompt-text" for="title"><?php echo $title_placeholder; ?></label>
					<input type="text" name="post_title" size="30" value="<?php echo esc_attr( $post->title ); ?>" id="title" spellcheck="true" autocomplete="off" />
				</div><!-- /titlewrap -->
			</div><!-- /titlediv -->
		<?php endif; ?>

		<?php if( post_type_supports( $post_type, 'editor' ) ) :
			$_wp_editor_expand_class = '';
			if ( $_wp_editor_expand ) {
				$_wp_editor_expand_class = ' wp-editor-expand';
			}
		?>

		<div id="postdivrich" class="postarea<?php echo $_wp_editor_expand_class; ?>">
			<?php wp_editor( $post->content, 'content' ); ?>
		</div><!-- /postdivrich -->

		<?php endif; ?>

		</div><!-- /post-body-content -->
	</div><!-- /post-body -->
</div><!-- /poststuff -->

</div><!-- /wrap -->

<?php
require_once ABSPATH . 'wp-admin/admin-footer.php';
