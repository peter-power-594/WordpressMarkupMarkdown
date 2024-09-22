					<div id="tab-advancedcustompost">
						<h3><?php esc_html_e( 'Advanced Custom Post', 'markup-markdown' ); ?></h3>
						<p><?php echo $this->prop[ 'desc' ]; ?></p>
						<table class="form-table" role="presentation">
							<tbody>
<?php
	$wp_post_types = get_post_types( [ '_builtin' => false ], 'objects' );
	$my_post_types = apply_filters( 'mmd_acp_post_types', $wp_post_types );
	$post_label = ucfirst( esc_html( translate( 'posts', 'default' ) ) );
	$page_label = ucfirst( esc_html( translate( 'pages', 'default' ) ) );
?>
								<tr class="acp-post-type">
									<th scope="row">
										<?php echo $post_label; ?>
									</th>
									<td>
										<label for="mmd_acp_blog_post_type">
											<?php esc_html_e( 'Match your posts to Wordpress native posts or an existing custom post type:' ); ?>
										</label>
										<select name="mmd_acp_blog_post_type" id="mmd_acp_blog_post_type"><?php
	if ( ! isset( $my_cnf[ 'blog_post_type' ] ) || ( 'post' !== $my_cnf[ 'blog_post_type' ] && ! isset( $my_post_types[ $my_cnf[ 'blog_post_type' ] ] ) ) ) :
		$my_cnf[ 'blog_post_type' ] = 'post';
	endif;
	echo '<option value="post"' . ( $my_cnf[ 'blog_post_type' ] === 'post' ? ' selected="selected"' : '' ) . '>' . $post_label . '</option>';
	foreach( $my_post_types as $slug => $props ) :
		echo '<option value="' . $slug . '"' . ( $my_cnf[ 'blog_post_type' ] === $slug ? ' selected="selected"' : '' ) . '>' . $props->label . '</option>';
	endforeach;
										?></select>
									</td>
								</tr>
								<tr class="acp-page-type">
									<th scope="row">
										<?php echo $page_label; ?>
									</th>
									<td>
										<label for="mmd_acp_blog_page_type">
											<?php esc_html_e( 'Match your pages to Wordpress native pages or an existing custom post type:' ); ?>
										</label>
										<select name="mmd_acp_blog_page_type" id="mmd_acp_blog_page_type"><?php
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
									</td>
								</tr>
							</tbody>
						</table>
					</div>
