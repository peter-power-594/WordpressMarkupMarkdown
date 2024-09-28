					<div id="tab-advancedcustompost">
						<h3><?php esc_html_e( 'Advanced Custom Post', 'markup-markdown' ); ?></h3>
						<p><?php echo $this->prop[ 'desc' ]; ?></p>
						<table class="form-table" role="presentation">
							<tbody>
							<tr class="acp-post-type">
<?php
	$wp_post_types = get_post_types( [ '_builtin' => false ], 'objects' );
	$my_post_types = apply_filters( 'mmd_acp_post_types', $wp_post_types );
	$post_label = ucfirst( esc_html( translate( 'posts', 'default' ) ) );
	$page_label = ucfirst( esc_html( translate( 'pages', 'default' ) ) );
?>
									<th scope="row">
										<?php echo $post_label; ?>
									</th>
									<td>
										<label for="mmd_acp_blog_post_type">
											<?php esc_html_e( 'Map your posts to Wordpress native posts\'screen or an existing custom post type:' ); ?><br />
											<select name="mmd_acp_blog_post_type" id="mmd_acp_blog_post_type" class="regular-text"><?php
	if ( ! isset( $my_cnf[ 'blog_post_type' ] ) || ( 'post' !== $my_cnf[ 'blog_post_type' ] && ! isset( $my_post_types[ $my_cnf[ 'blog_post_type' ] ] ) ) ) :
		$my_cnf[ 'blog_post_type' ] = 'post';
	endif;
	echo '<option value="post"' . ( $my_cnf[ 'blog_post_type' ] === 'post' ? ' selected="selected"' : '' ) . '>' . $post_label . '</option>';
	foreach( $my_post_types as $slug => $props ) :
		echo '<option value="' . $slug . '"' . ( $my_cnf[ 'blog_post_type' ] === $slug ? ' selected="selected"' : '' ) . '>' . $props->label . '</option>';
	endforeach;
											?></select>
										</label>
									</td>
								</tr>
								<tr class="acp-page-type">
									<th scope="row">
										<?php echo $page_label; ?>
									</th>
									<td>
										<label for="mmd_acp_blog_page_type">
											<?php esc_html_e( 'Map your pages to Wordpress native pages\'screen or an existing custom post type:' ); ?><br />
											<select name="mmd_acp_blog_page_type" id="mmd_acp_blog_page_type" class="regular-text"><?php
	if ( ! isset( $my_cnf[ 'blog_page_type' ] ) || ( 'page' !== $my_cnf[ 'blog_page_type' ] && ! isset( $my_post_types[ $my_cnf[ 'blog_page_type' ] ] ) ) ) :
		$my_cnf[ 'blog_page_type' ] = 'page';
	endif;
	echo '<option value="page"' . ( $my_cnf[ 'blog_page_type' ] === 'page' ? ' selected="selected"' : '' ) . '>' . $page_label . '</option>';
	foreach( $my_post_types as $slug => $props ) :
		if ( preg_match( '#^acf-#', $slug ) ) :
			continue;
		endif;
		echo '<option value="' . $slug . '"' . ( $my_cnf[ 'blog_page_type' ] === $slug ? ' selected="selected"' : '' ) . '>' . $props->label . '</option>';
	endforeach;
											?></select>
										</label>
									</td>
								</tr>
								<tr class="acp-use-git">
									<th scope="row">
										<?php esc_html_e( 'Git Options' ); ?>
									</th>
									<td>
<?php
	if ( ! isset( $my_cnf[ 'use_git' ] ) ) :
		$my_cnf[ 'use_git' ] = 0;
	endif;
	$git_folders = [];
	if ( is_dir( ABSPATH . '.git' ) ) :
		$git_folders[] = ABSPATH;
	endif;
	if ( is_dir( ABSPATH . '_posts' ) && is_dir( ABSPATH . '_posts/.git' ) ) :
		$git_folders[] = ABSPATH . '_posts/';
	endif;
	if ( count( $git_folders ) > 0 ) :
?>
										<em><?php esc_html_e( 'To use this feature you need a git client previously installed on your server and accessible from the command line to PHP.' ); ?></em><br />
										<br />
										<label for="mmd_acp_use_git">
											<input type="checkbox" name="mmd_acp_use_git" id="mmd_acp_use_git" value="1"<?php echo (int)$my_cnf[ 'use_git' ] > 0 ? ' checked="checked"' : ''; ?>/>
											<?php esc_html_e( 'Use Git' ); ?>
											(<?php esc_html_e( 'Commit to a remote repository when publishing or updating a post' ); ?>)
										</label><br />
										<br />
										<label for="mmd_acp_git_folder">
											<?php esc_html_e( 'Repository Location', 'markup-markdown' ); ?><br />
											<select name="mmd_acp_git_folder" id="mmd_acp_git_folder" class="regular-text">
												<option value="#"><?php esc_html_e( 'Select the appropriate folder'); ?></option><?php
	if ( ! isset( $my_cnf[ 'git_folder' ] ) ) :
		$my_cnf[ 'git_folder' ] = '';
	endif;
	foreach( $git_folders as $folder ) :
		if ( strpos( $folder, '\\' ) !== false ) :
			$folder = str_replace( '/', '\\', $folder );
		endif;
		echo '<option value="' . $folder . '"' . ( $my_cnf[ 'git_folder' ] === $folder ? ' selected="selected"' : '' ) . '>' . $folder . '</option>';
	endforeach;
											?></select>
										</label><br />
										<br />
										<label for="mmd_acp_git_username">
											<?php esc_html_e( 'Repository User Name', 'markup-markdown' ); ?><br />
											<input type="text" name="mmd_acp_git_username" id="mmd_acp_git_username" value="<?php echo isset( $my_cnf[ 'git_username' ] ) && ! empty( $my_cnf[ 'git_username' ] ) ? $my_cnf[ 'git_username' ] : ''; ?>" class="regular-text">
										</label><br />
										<br />
										<label for="mmd_acp_git_useremail">
											<?php esc_html_e( 'Repository User Email', 'markup-markdown' ); ?><br />
											<input type="text" name="mmd_acp_git_useremail" id="mmd_acp_git_useremail" value="<?php echo isset( $my_cnf[ 'git_useremail' ] ) && ! empty( $my_cnf[ 'git_useremail' ] ) ? $my_cnf[ 'git_useremail' ] : ''; ?>" class="regular-text">
										</label><br />
										<br />
										<label for="mmd_acp_git_repo_origin">
											<?php esc_html_e( 'Repository Origin', 'markup-markdown' ); ?><br />
											<input type="text" name="mmd_acp_git_repo_origin" id="mmd_acp_git_repo_origin" value="<?php echo isset( $my_cnf[ 'git_repo_origin' ] ) && ! empty( $my_cnf[ 'git_repo_origin' ] ) ? $my_cnf[ 'git_repo_origin' ] : ''; ?>" class="regular-text">
										</label>
										
<?php
	else:
		esc_html_e( 'Feature unavailable. No git instance found on your server or no folder containing a git setup detected.' );
	endif;
?>
									</td>
								</tr>
							</tbody>
						</table>
					</div>
