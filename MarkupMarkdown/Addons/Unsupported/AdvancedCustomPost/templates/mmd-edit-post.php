<?php

defined( 'ABSPATH' ) || exit;
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

if ( $post_ID ) : # post_id = filename
	require_once mmd()->plugin_dir . '/MarkupMarkdown/Addons/Unsupported/AdvancedCustomPost/Parser.php';
	$post = new \MarkupMarkdown\Addons\Unsupported\AdvancedCustomPost\Parser( $post_ID, $my_cnf );
	$my_nonce = filter_input( INPUT_POST, '_wpnonce', FILTER_SANITIZE_SPECIAL_CHARS );
	if ( isset( $my_nonce ) && wp_verify_nonce( $my_nonce, 'update-post_' . $post_ID ) ) :
		$post->update();
	endif;
endif;

global $title;
$title = '';
if ( $post ) {
	$post_type        = $post->post_type;
	$post_type_object = get_post_type_object( $post_type );
	$title = __( 'Edit' ) . ' ' . $post->post_title;
}

# Quick fix
remove_post_type_support( $post_type, 'comments' );
remove_post_type_support( $post_type, 'revisions' );
remove_post_type_support( $post_type, 'page-attributes' );
remove_post_type_support( $post_type, 'post-formats' );
remove_post_type_support( $post_type, 'author' );

$form_action = 'editpost';


if ( isset( $_POST['post_type'] ) && $post && $post_type !== $_POST['post_type'] ) {
	wp_die( __( 'A post type mismatch has been detected.' ), __( 'Sorry, you are not allowed to edit this item.' ), 400 );
}

wp_enqueue_script( 'post' );

$_wp_editor_expand   = false;
$_content_editor_dfw = false;

if ( post_type_supports( $post_type, 'editor' ) && ! wp_is_mobile() ) {
	if ( apply_filters( 'wp_editor_expand', true, $post_type ) ) {
		wp_enqueue_script( 'editor-expand' );
		$_content_editor_dfw = true;
		$_wp_editor_expand   = ( 'on' === get_user_setting( 'editor_expand', 'on' ) );
	}
}

if ( wp_is_mobile() ) {
	wp_enqueue_script( 'jquery-touch-punch' );
}

require_once ABSPATH . 'wp-admin/admin-header.php';

?>

<div class="wrap">
<h1><?php _e( 'Edit' ); if ( $post->post_type !== 'post' ) : _e( $post->post_type ); endif; ?></h1>
<hr class="wp-header-end">
<form name="post" action="<?php echo add_query_arg( array( 'post' => $post_ID ), admin_url( 'post.php' ) ); ?>" method="post" id="post">
<?php wp_nonce_field( 'update-post_' . $post_ID ); ?>
<input type="hidden" id="user-id" name="user_ID" value="<?php echo get_current_user_id(); ?>" />
<input type="hidden" id="hiddenaction" name="action" value="<?php echo esc_attr( $form_action ); ?>" />
<input type="hidden" id="originalaction" name="originalaction" value="<?php echo esc_attr( $form_action ); ?>" />
<input type="hidden" id="post_type" name="post_type" value="<?php echo esc_attr( $post_type ); ?>" />
<input type="hidden" id="original_post_status" name="original_post_status" value="<?php echo esc_attr( $post->post_status ); ?>" />
<input type="hidden" id="referredby" name="referredby" value="<?php echo isset( $referer ) ? esc_url( $referer ) : ''; ?>" />
<?php

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
					<input type="text" name="post_title" size="30" value="<?php echo esc_attr( $post->post_title ); ?>" id="title" spellcheck="true" autocomplete="off" />
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
			<?php wp_editor( $post->post_content, 'content' ); ?>
		</div><!-- /postdivrich -->

		<?php endif; ?>

		</div><!-- /post-body-content -->

		<div id="postbox-container-1" class="postbox-container">
			<div id="side-sortables" class="meta-box-sortables ui-sortable">
				<div id="submitdiv" class="postbox">
					<div class="postbox-header">
						<h2 class="hndle ui-sortable-handle"><?php _e( 'Publish' ); ?></h2>
						<div class="handle-actions hide-if-no-js"><button type="button" class="handle-order-higher" aria-disabled="false" aria-describedby="submitdiv-handle-order-higher-description"><span class="screen-reader-text">Monter</span><span class="order-higher-indicator" aria-hidden="true"></span></button><span class="hidden" id="submitdiv-handle-order-higher-description">Déplacer la boite Publier vers le haut</span><button type="button" class="handle-order-lower" aria-disabled="false" aria-describedby="submitdiv-handle-order-lower-description"><span class="screen-reader-text">Descendre</span><span class="order-lower-indicator" aria-hidden="true"></span></button><span class="hidden" id="submitdiv-handle-order-lower-description">Déplacer la boite Publier vers le bas</span><button type="button" class="handlediv" aria-expanded="true"><span class="screen-reader-text">Ouvrir/fermer la section Publier</span><span class="toggle-indicator" aria-hidden="true"></span></button></div>
					</div>
					<div class="inside">

						<div class="submitbox" id="submitpost">

						<?php if ( $post->post_status === 'draft' ) : ?>
							<div id="minor-publishing-actions">
								<div id="save-action">
									<input type="submit" name="save" id="save-post" value="<?php esc_html_e( 'Save draft' ); ?>" class="button">
									<span class="spinner"></span>
								</div>
								<div class="clear"></div>
							</div>
						<?php endif; ?>
							<div id="misc-publishing-actions">
								<div class="misc-pub-section misc-pub-post-status">
									<?php _e( 'Status'); ?>&nbsp;: <span id="post-status-display"><?php echo $post->post_status === 'publish' ? __( 'Published' ) : __( 'Draft' ); ?></span>

									<a href="#post_status" class="edit-post-status hide-if-no-js" role="button">
										<span aria-hidden="true"><?php _e( 'Edit' ); ?></span>
										<span class="screen-reader-text"><?php _e( 'Edit status' ); ?></span>
									</a>

									<div id="post-status-select" class="hide-if-js">
										<input type="hidden" name="hidden_post_status" id="hidden_post_status" value="<?php echo $post->post_status; ?>">
										<label for="post_status" class="screen-reader-text"><?php esc_html_e( 'Status' ); ?></label>
										<select name="post_status" id="post_status">
										<?php if ( $post->post_status === 'publish' ) : ?>
											<option value="publish" selected="selected"><?php esc_html_e( 'Published' ); ?></option>
										<?php endif; ?>
											<option value="draft"<?php if ( $post->post_status === 'draft' ) : ?> selected="selected"<?php endif; ?>><?php esc_html_e( 'Draft' ); ?></option>
										</select>
										<a href="#post_status" class="save-post-status hide-if-no-js button"><?php esc_html_e( 'OK' ); ?></a>
										<a href="#post_status" class="cancel-post-status hide-if-no-js button-cancel"><?php esc_html_e( 'Cancel' ); ?></a>
									</div>
								</div>

								<div class="misc-pub-section curtime misc-pub-curtime">
								<?php
									$my_date_format = _x( 'M j, Y', 'publish box date format' ); # get_option( 'date_format' );
									$my_time_format = _x( 'H:i', 'publish box time format' ); # get_option( 'time_format' );
									$my_post_date = isset( $post->post_date ) ? strtotime( $post->post_date ) : current_time( 'mysql' );
									$my_cal_date = sprintf(
										__( '%1$s at %2$s' ),
										date_i18n( $my_date_format, $my_post_date ),
										date_i18n( $my_time_format, $my_post_date )
									);
								?>
									<span id="timestamp"><?php printf( esc_html__( 'Published on: %s' ), '<b>' . $my_cal_date . '</b>' ); ?></span>
									<a href="#edit_timestamp" class="edit-timestamp hide-if-no-js" role="button">
										<span aria-hidden="true"><?php esc_html_e( 'Edit' ); ?></span>
										<span class="screen-reader-text"><?php esc_html_e( 'Edit post date' ); ?></span>
									</a>
									<fieldset id="timestampdiv" class="hide-if-js">
										<legend class="screen-reader-text"><?php esc_html_e( 'Datetime' ); ?></legend>
										<div class="timestamp-wrap">
											<label>
												<span class="screen-reader-text"><?php esc_html_e( 'Day' ); ?></span>
												<input type="text" id="jj" name="jj" value="<?php echo date_i18n( 'd', $my_post_date ); ?>" size="2" maxlength="2" autocomplete="off" class="form-required">
											</label>
											<label>
												<span class="screen-reader-text"><?php esc_html_e( 'Month' ); ?></span>
												<?php
													$my_post_month = date_i18n( 'm', $my_post_date );
													$selected = ' selected="selected"';
													$cal_months = array();
													global $wp_locale;
													for ( $m = 1; $m < 13; $m++ ) :
														$cal_months[ $m - 1 ] = $wp_locale->get_month_abbrev( $wp_locale->get_month( $m ) );
													endfor;
												?>
												<select class="form-required" id="mm" name="mm">
													<option value="01" data-text="<?php echo $cal_months[ 0 ]; ?>" <?php if ( $my_post_month === '01' ) : echo $selected; endif; ?>>01-<?php echo $cal_months[ 0 ]; ?></option>
													<option value="02" data-text="<?php echo $cal_months[ 1 ]; ?>" <?php if ( $my_post_month === '02' ) : echo $selected; endif; ?>>02-<?php echo $cal_months[ 1 ]; ?></option>
													<option value="03" data-text="<?php echo $cal_months[ 2 ]; ?>" <?php if ( $my_post_month === '03' ) : echo $selected; endif; ?>>03-<?php echo $cal_months[ 2 ]; ?></option>
													<option value="04" data-text="<?php echo $cal_months[ 3 ]; ?>" <?php if ( $my_post_month === '04' ) : echo $selected; endif; ?>>04-<?php echo $cal_months[ 3 ]; ?></option>
													<option value="05" data-text="<?php echo $cal_months[ 4 ]; ?>" <?php if ( $my_post_month === '05' ) : echo $selected; endif; ?>>05-<?php echo $cal_months[ 4 ]; ?></option>
													<option value="06" data-text="<?php echo $cal_months[ 5 ]; ?>" <?php if ( $my_post_month === '06' ) : echo $selected; endif; ?>>06-<?php echo $cal_months[ 5 ]; ?></option>
													<option value="07" data-text="<?php echo $cal_months[ 6 ]; ?>" <?php if ( $my_post_month === '07' ) : echo $selected; endif; ?>>07-<?php echo $cal_months[ 6 ]; ?></option>
													<option value="08" data-text="<?php echo $cal_months[ 7 ]; ?>" <?php if ( $my_post_month === '08' ) : echo $selected; endif; ?>>08-<?php echo $cal_months[ 7 ]; ?></option>
													<option value="09" data-text="<?php echo $cal_months[ 8 ]; ?>" <?php if ( $my_post_month === '09' ) : echo $selected; endif; ?>>09-<?php echo $cal_months[ 8 ]; ?></option>
													<option value="10" data-text="<?php echo $cal_months[ 9 ]; ?>" <?php if ( $my_post_month === '10' ) : echo $selected; endif; ?>>10-<?php echo $cal_months[ 9 ]; ?></option>
													<option value="11" data-text="<?php echo $cal_months[ 10 ]; ?>" <?php if ( $my_post_month === '11' ) : echo $selected; endif; ?>>11-<?php echo $cal_months[ 10 ]; ?></option>
													<option value="12" data-text="<?php echo $cal_months[ 11 ]; ?>" <?php if ( $my_post_month === '12' ) : echo $selected; endif; ?>>12-<?php echo $cal_months[ 11 ]; ?></option>
												</select>
											</label>
											<label>
												<span class="screen-reader-text"><?php esc_html_e( 'Year' ); ?></span>
												<input type="text" id="aa" name="aa" value="<?php echo date_i18n( 'Y', $my_post_date ); ?>" size="4" maxlength="4" autocomplete="off" class="form-required">
											</label>
											<?php esc_html_e( 'at' ); ?>
											<label>
												<span class="screen-reader-text"><?php esc_html_e( 'Hour' ); ?></span>
												<input type="text" id="hh" name="hh" value="<?php echo date_i18n( 'H', $my_post_date ); ?>" size="2" maxlength="2" autocomplete="off" class="form-required">
											</label>
											h
											<label>
												<span class="screen-reader-text"><?php esc_html_e( 'Minute' ); ?></span>
												<input type="text" id="mn" name="mn" value="<?php echo date_i18n( 'i', $my_post_date ); ?>" size="2" maxlength="2" autocomplete="off" class="form-required">
											</label>
										</div>
										<input type="hidden" id="ss" name="ss" value="<?php echo date_i18n( 's' ); ?>">
										<input type="hidden" id="hidden_mm" name="hidden_mm" value="<?php echo date_i18n( 'm', $my_post_date ); ?>">
										<input type="hidden" id="cur_mm" name="cur_mm" value="<?php echo current_time( 'm' ); ?>">
										<input type="hidden" id="hidden_jj" name="hidden_jj" value="<?php echo date_i18n( 'd', $my_post_date ); ?>">
										<input type="hidden" id="cur_jj" name="cur_jj" value="<?php echo current_time( 'd' ); ?>">
										<input type="hidden" id="hidden_aa" name="hidden_aa" value="<?php echo date_i18n( 'Y', $my_post_date ); ?>">
										<input type="hidden" id="cur_aa" name="cur_aa" value="<?php echo current_time( 'Y' ); ?>">
										<input type="hidden" id="hidden_hh" name="hidden_hh" value="<?php echo date_i18n( 'H', $my_post_date ); ?>">
										<input type="hidden" id="cur_hh" name="cur_hh" value="<?php echo current_time( 'H' ); ?>">
										<input type="hidden" id="hidden_mn" name="hidden_mn" value="<?php echo date_i18n( 'i', $my_post_date ); ?>">
										<input type="hidden" id="cur_mn" name="cur_mn" value="<?php echo current_time( 'i' ); ?>">
										<p>
											<a href="#edit_timestamp" class="save-timestamp hide-if-no-js button"><?php esc_html_e( 'OK' ); ?></a>
											<a href="#edit_timestamp" class="cancel-timestamp hide-if-no-js button-cancel"><?php esc_html_e( 'Cancel' ); ?></a>
										</p>
									</fieldset>
								</div>

								<div class="clear"></div>
							</div><!-- //#misc-publishing-actions -->


							<div id="major-publishing-actions">
								<div id="delete-action">
									<a class="submitdelete deletion" href="https://www-dev.pierre-henri-lavigne.info/wp-admin/post.php?post=126&amp;action=trash&amp;_wpnonce=79854e8e0e">Mettre à la corbeille</a>
								</div>
								<div id="publishing-action">
									<span class="spinner"></span>
							<?php if ( $post->post_status === 'draft' ) : ?>
									<input name="original_publish" type="hidden" id="original_publish" value="<?php esc_html_e( 'Publish' ); ?>">
									<input type="submit" name="publish" id="publish" class="button button-primary button-large" value="<?php esc_html_e( 'Publish' ); ?>">
							<?php elseif ( $post->post_status === 'publish' ) : ?>
									<input name="original_publish" type="hidden" id="original_publish" value="<?php esc_html_e( 'Update' ); ?>">
									<input type="submit" name="publish" id="publish" class="button button-primary button-large" value="<?php esc_html_e( 'Update' ); ?>">
							<?php endif; ?>
								</div>
								<div class="clear"></div>
							</div><!-- //#major-publishing-actions -->


						</div><!-- //#submitpost.submitpost -->

					</div><!-- //.inside -->
				</div><!-- //#submitdiv.postbox -->

				<div id="categorydiv" class="postbox">
					<div class="postbox-header">
						<h2 class="hndle "><?php _e( 'Categories' ); ?></h2>
						<div class="handle-actions hide-if-no-js">
							<button type="button" class="handle-order-higher" aria-disabled="false" aria-describedby="postimagediv-handle-order-higher-description"><span class="screen-reader-text"><?php echo __( 'Move up' ); ?></span><span class="order-higher-indicator" aria-hidden="true"></span></button>
							<span class="hidden" id="postimagediv-handle-order-higher-description"><?php sprintf( __( 'Move %s box up' ), __( 'Categories' ) ); ?></span>
							<button type="button" class="handle-order-lower" aria-disabled="false" aria-describedby="postimagediv-handle-order-lower-description"><span class="screen-reader-text"><?php echo __( 'Move down' ); ?></span><span class="order-lower-indicator" aria-hidden="true"></span></button>
							<span class="hidden" id="postimagediv-handle-order-lower-description"><?php sprintf( __( 'Move %s box down' ), __( 'Categories' ) ); ?></span>
							<button type="button" class="handlediv" aria-expanded="true"><span class="screen-reader-text"><?php sprintf( __( 'Toggle panel: %s' ), __( 'Categories' ) ); ?></span><span class="toggle-indicator" aria-hidden="true"></span></button>
						</div>
					</div>
					<div class="inside">
						<div id="category-all">
							<ul id="categorychecklist" data-wp-lists="list:category" class="categorychecklist form-no-clear">
							<?php
								$cat_idx = 1;
								foreach( $post->post_categories as $category ) :
							?>
								<li class="checked"><label class="selectit"><input value="<?php echo $category; ?>" type="checkbox" name="post_category[]" id="in-category-14" checked="checked"> <?php echo $category; ?></label></li>
							<?php
								endforeach;
							?>
							</ul>
						</div><!-- //#category-all -->
					</div><!-- //.inside -->
				</div><!-- //#categorydiv.postbox -->


				<div id="postimagediv" class="postbox">
					<div class="postbox-header">
						<h2 class="hndle ui-sortable-handle"><?php _e( 'Featured image' ); ?></h2>
						<div class="handle-actions hide-if-no-js">
							<button type="button" class="handle-order-higher" aria-disabled="false" aria-describedby="postimagediv-handle-order-higher-description"><span class="screen-reader-text"><?php echo __( 'Move up' ); ?></span><span class="order-higher-indicator" aria-hidden="true"></span></button>
							<span class="hidden" id="postimagediv-handle-order-higher-description"><?php sprintf( __( 'Move %s box up' ), __( 'Featured image' ) ); ?></span>
							<button type="button" class="handle-order-lower" aria-disabled="false" aria-describedby="postimagediv-handle-order-lower-description"><span class="screen-reader-text"><?php echo __( 'Move down' ); ?></span><span class="order-lower-indicator" aria-hidden="true"></span></button>
							<span class="hidden" id="postimagediv-handle-order-lower-description"><?php sprintf( __( 'Move %s box down' ), __( 'Featured image' ) ); ?></span>
							<button type="button" class="handlediv" aria-expanded="true"><span class="screen-reader-text"><?php sprintf( __( 'Toggle panel: %s' ), __( 'Featured image' ) ); ?></span><span class="toggle-indicator" aria-hidden="true"></span></button>
						</div>
					</div>
					<div class="inside">
						<p class="hide-if-no-js"><a href="https://www-dev.pierre-henri-lavigne.info/wp-admin/media-upload.php?post_id=126&amp;type=image&amp;TB_iframe=1" id="set-post-thumbnail" class="thickbox">Définir l’image mise en avant</a></p>
						<input type="hidden" id="_thumbnail_id" name="_thumbnail_id" value="-1">
					</div>
				</div><!-- //#postimagediv.postbox -->

			</div><!-- //#side-sortables -->

		</div><!-- //#postbox-container-1.postbox-container -->

	</div><!-- /post-body -->
</div><!-- /poststuff -->

</div><!-- /wrap -->

<?php if ( ! wp_is_mobile() && post_type_supports( $post_type, 'title' ) && '' === $post->post_title ) : ?>
<script type="text/javascript">
try{document.post.title.focus();}catch(e){}
</script>
<?php endif; ?>
<?php
require_once ABSPATH . 'wp-admin/admin-footer.php';
