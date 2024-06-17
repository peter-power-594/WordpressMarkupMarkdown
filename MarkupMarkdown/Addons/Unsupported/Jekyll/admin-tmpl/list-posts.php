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
?>

<div class="wrap">
	<h1><?php _e( 'Posts' ); ?></h1>
	<table class="wp-list-table widefat fixed striped table-view-list posts">
		<?php /* <caption class="screen-reader-text">Table ordered by Date. Descending.</caption> */ ?>
		<thead>
			<tr>
				<th scope="col" id="title" class="manage-column column-title column-primary" abbr="Title"><span>Title</span></th>
				<th scope="col" id="categories" class="manage-column column-categories">Categories</th>
				<th scope="col" id="date" class="manage-column column-date" abbr="Date"><span>Date</span></a></th>
			</tr>
		</thead>

		<tbody id="the-list"><?php
	$my_posts = json_decode( file_get_contents( mmd()->cache_dir . '/jekyll_posts.json' ) );
	foreach( $my_posts->data as $my_post ) :
		$post_tmp = file_get_contents( $posts_dir . '/' . $my_post );
		if ( strpos( $post_tmp, '---' ) === false ) :
			continue;
		endif;
		$post_row_headers = explode( "\n", explode( '---', $post_tmp )[ 1 ] );
		unset( $post_tmp ); $post_headers = [];
		foreach( $post_row_headers as $row_data ) :
			if ( strpos( $row_data, ':' ) === false ) :
				continue;
			endif;
			preg_match( '#([a-z]+):#', $row_data, $row_key );
			$post_headers[ $row_key[ 1 ] ] = preg_replace( '#[a-z]+:[\s\t]*#', '', $row_data );
		endforeach;
		$my_date_format = get_option( 'date_format' );
?>
			<tr class="status-publish hentry">
				<td class="title column-title has-row-actions column-primary page-title" data-colname="Title">
					<a class="row-title" href="/wp-admin/post.php?post=<?php echo urlencode( htmlspecialchars( $my_post ) ); ?>&amp;action=edit" aria-label="“test” (Edit)"><?php echo isset( $post_headers[ 'title' ] ) ? $post_headers[ 'title' ] : __( 'Untitled' ); ?></a>
				</td>
				<td class="categories column-categories" data-colname="<?php _e( 'Categories' ); ?>"><?php if ( isset( $post_headers[ 'categories' ] ) ) : echo $post_headers[ 'categories' ]; else: ?><span aria-hidden="true">—</span><span class="screen-reader-text"><?php _e( 'No categories' ); ?></span><?php endif; ?></td>
				<td class="date column-date" data-colname="<?php _e( 'Dated' ); ?>"><?php _e( 'Published' ); if ( isset( $post_headers[ 'date' ] ) ) : echo ' ' . date_i18n( $my_date_format, strtotime( $post_headers[ 'date' ] ) ); endif; ?></td>
			</tr>
		<?php
	endforeach;
		?></tbody>

		<tfoot>
			<tr>
				<th scope="col" class="manage-column column-title column-primary" abbr="Title"><span>Title</span></th>
				<th scope="col" class="manage-column column-categories">Categories</th>
				<th scope="col" class="manage-column column-date sorted desc"><span>Date</span></th>
			</tr>
		</tfoot>
	</table>
</div>

<?php
require_once ABSPATH . 'wp-admin/admin-footer.php';
