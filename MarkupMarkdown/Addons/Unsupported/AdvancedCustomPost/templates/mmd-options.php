					<div id="tab-advancedcustompost">
						<h3><?php esc_html_e( 'Advanced Custom Post', 'markup-markdown' ); ?></h3>
						<p><?php echo $this->prop[ 'desc' ]; ?></p>
						<table class="form-table" role="presentation">
							<tbody>
<?php
	$my_post_types = get_post_types( [ '_builtin' => false ], 'objects' );
?>
								<tr class="acp-post-type">
									<th scope="row">
										<?php echo ucfirst( esc_html( translate( 'posts', 'default' ) ) ); ?>
									</th>
									<td>
										<label for="mmd_acp_blog_post_type">
											<?php esc_html_e( 'Match your posts to Wordpress native posts or an existing custom post type:' ); ?>
										</label>
										<select name="mmd_acp_blog_post_type" id="mmd_acp_blog_post_type"><?php
	foreach( $my_post_types as $slug => $props ) :
		if ( preg_match( '#^acf-#', $slug ) ) :
			continue;
		endif;
		echo '<option value="' . $slug . '">' . $props->label . '</option>';
	endforeach;
										?></select>
									</td>
								</tr>
								<tr class="acp-page-type">
									<th scope="row">
										<?php echo ucfirst( esc_html( translate( 'pages', 'default' ) ) ); ?>
									</th>
									<td>
										<label for="mmd_acp_blog_page_type">
											<?php esc_html_e( 'Match your pages to Wordpress native pages or an existing custom post type:' ); ?>
										</label>
										<select name="mmd_acp_blog_page_type" id="mmd_acp_blog_page_type"><?php
	foreach( $my_post_types as $slug => $props ) :
		if ( preg_match( '#^acf-#', $slug ) ) :
			continue;
		endif;
		echo '<option value="' . $slug . '">' . $props->label . '</option>';
	endforeach;
										?></select>
									</td>
								</tr>
							</tbody>
						</table>
					</div>
