<?php defined( 'ABSPATH' ) || exit; ?>

<div id="tab-layout">
	<h2><?php esc_html_e( 'Layout', 'markup-markdown' ); ?></h2>
	<p><?php esc_html_e( 'Here are a few settings you can change to modify the behavior of your blog posts.', 'markup-markdown' ); ?></p>
	<table class="form-table" role="presentation">
		<tbody>
<?php
	$my_cnf = array(
		'lightbox' => defined( 'MMD_USE_LIGHTBOX' ) ? MMD_USE_LIGHTBOX : 1,
		'masonry' => defined( 'MMD_USE_MASONRY' ) ? MMD_USE_MASONRY : 1,
		'imagesloaded' => defined( 'MMD_USE_IMAGESLOADED' ) ? MMD_USE_IMAGESLOADED : 1,
		'goodvibes' => defined( 'MMD_USE_BLOCKSTYLES' ) ? MMD_USE_BLOCKSTYLES : 0,
		'headings' => defined( 'MMD_USE_HEADINGS' ) && count( MMD_USE_HEADINGS ) > 1 ? MMD_USE_HEADINGS : [ 1, 2, 3, 4, 5, 6 ],
		'keepspaces' => defined( 'MMD_KEEP_SPACES' ) ? MMD_KEEP_SPACES : 0
	);
?>
			<tr class="site-use-selective_headings">
				<th scope="row">
					<?php esc_html_e( 'Select headline weights', 'markup-markdown' ); ?>
				</th>
				<td>
					<?php esc_html_e( 'You can toggle specific heading levels for your authors to avoid conflicts with your theme setup. At least two weights are required.', 'markup-markdown' ); ?><br />
					<label for="mmd_headings1">
						<input type="checkbox" name="mmd_headings[]" id="mmd_headings1" value="1" <?php echo in_array( '1', $my_cnf[ 'headings' ] ) ? 'checked="checked"' : ''; ?> />
						<?php esc_html_e( 'Level 1 (H1)', 'markup-markdown' ); ?>
					</label> &nbsp;
					<label for="mmd_headings2">
						<input type="checkbox" name="mmd_headings[]" id="mmd_headings2" value="2" <?php echo in_array( '2', $my_cnf[ 'headings' ] ) ? 'checked="checked"' : ''; ?> />
						<?php esc_html_e( 'Level 2 (H2)', 'markup-markdown' ); ?>
					</label> &nbsp;
					<label for="mmd_headings3">
						<input type="checkbox" name="mmd_headings[]" id="mmd_headings3" value="3" <?php echo in_array( '3', $my_cnf[ 'headings' ] ) ? 'checked="checked"' : ''; ?> />
						<?php esc_html_e( 'Level 3 (H3)', 'markup-markdown' ); ?>
					</label> &nbsp;
					<label for="mmd_headings4">
						<input type="checkbox" name="mmd_headings[]" id="mmd_headings4" value="4" <?php echo in_array( '4', $my_cnf[ 'headings' ] ) ? 'checked="checked"' : ''; ?> />
						<?php esc_html_e( 'Level 4 (H4)', 'markup-markdown' ); ?>
					</label> &nbsp;
					<label for="mmd_headings5">
						<input type="checkbox" name="mmd_headings[]" id="mmd_headings5" value="5" <?php echo in_array( '5', $my_cnf[ 'headings' ] ) ? 'checked="checked"' : ''; ?> />
						<?php esc_html_e( 'Level 5 (H5)', 'markup-markdown' ); ?>
					</label> &nbsp;
					<label for="mmd_headings6">
						<input type="checkbox" name="mmd_headings[]" id="mmd_headings6" value="6" <?php echo in_array( '6', $my_cnf[ 'headings' ] ) ? 'checked="checked"' : ''; ?> />
						<?php esc_html_e( 'Level 6 (H6)', 'markup-markdown' ); ?>
					</label>
				</td>
			</tr>
			<tr class="site-keep-spaces">
				<th scope="row">
					<?php esc_html_e( 'Preserve spaces', 'markup-markdown' ); ?>
				</th>
				<td>
					<label for="mmd_keepspaces">
						<input type="checkbox" name="mmd_keepspaces" id="mmd_keepspaces" value="1" <?php echo (int)$my_cnf[ 'keepspaces' ] > 0 ? 'checked="checked"' : ''; ?> />
						<?php esc_html_e( 'By default space characters at the beginning of each line are removed (trim), check this option to keep them.', 'markup-markdown' ); ?>
					</label>
				</td>
			</tr>
			<tr class="site-use-blocks">
				<th scope="row">
					<?php esc_html_e( 'Use Gutenberg blocks styles', 'markup-markdown' ); ?>
				</th>
				<td>
					<label for="mmd_goodvibes">
						<input type="checkbox" name="mmd_goodvibes" id="mmd_goodvibes" value="1" <?php echo (int)$my_cnf[ 'goodvibes' ] > 0 ? 'checked="checked"' : ''; ?> />
						<?php esc_html_e( 'Keep a minimum of assets active if your theme was designed to be used with Gutenberg. Can avoid broken layout in case styles are missing.', 'markup-markdown' ); ?>
					</label>
				</td>
			</tr>
			<tr class="site-use-lightbox">
				<th scope="row">
					<?php esc_html_e( 'Use Lightbox', 'markup-markdown' ); ?>
				</th>
				<td>
					<label for="mmd_lightbox">
						<input type="checkbox" name="mmd_lightbox" id="mmd_lightbox" value="1" <?php echo (int)$my_cnf[ 'lightbox' ] > 0 ? 'checked="checked"' : ''; ?> />
						<?php printf( esc_html__( 'An image inside a %1$spost%2$s or %1$spage%2$s that was linked to its original size will open in a modal (overlay on the same page) instead of a new window / tab.', 'markup-markdown' ), '<em>', '</em>', '<em>', '</em>' ); ?>
					</label>
				</td>
			</tr>
			<tr class="site-use-masonry">
				<th scope="row">
					<?php esc_html_e( 'Use Masonry', 'markup-markdown' ); ?>
				</th>
				<td>
					<label for="mmd_masonry">
						<input type="checkbox" name="mmd_masonry" id="mmd_masonry" value="1" <?php echo (int)$my_cnf[ 'masonry' ] > 0 ? 'checked="checked"' : ''; ?>>
						<?php printf( esc_html__( 'Transform a bullet list of images as a 2 waterfall column layout when the %1$sphoto gallery%2$s post format is selected.', 'markup-markdown' ), '<em>', '</em>' ); ?>
					</label>
				</td>
			</tr>
			<tr class="site-use-imagesloaded">
				<th scope="row">
					<?php esc_html_e( 'Use Imagesloaded', 'markup-markdown' ); ?>
				</th>
				<td>
					<label for="mmd_imagesloaded">
						<input type="checkbox" name="mmd_imagesloaded" id="mmd_imagesloaded" value="1" <?php echo (int)$my_cnf[ 'imagesloaded' ] > 0 ? 'checked="checked"' : ''; ?> />
						<?php esc_html_e( 'Trigger the update of the layout after all images are loaded. Can solve specific issues in case the layout is broken with the gallery.', 'markup-markdown' ); ?>
					</label>
				</td>
			</tr>
			<tr class="site-default-toolbar">
				<th scope="row">
					<?php esc_html_e( 'Custom toolbar', 'markup-markdown' ); ?>
				</th>
				<td>
					&nbsp;
				</td>
			</tr>
			<tr class="site-default-toolbar">
				<td colspan="2">
<?php
	include mmd()->plugin_dir . "/MarkupMarkdown/Addons/Released/Media/ToolbarEasyMDE.php";
	$my_toolbar = new \MarkupMarkdown\Addons\Released\Media\ToolbarEasyMDE( $toolbar_conf );
?>
					<div class="ui-widget ui-helper-clearfix">
						<div id="my_toolbar" class="editor-toolbar ui-widget-content ui-state-default">
							<h4 class="ui-widget-header"><?php _e( 'Current Toolbar (Preview)', 'markup-markdown' ); ?></h4>
							<p>
								<?php _e( 'You can sort the buttons and remove some of them if need.', 'markup-markdown' ); ?>
							</p>
							<ul id="my_buttons" class="connected">
<?php

	$toolbar_fields = [];
	foreach( $my_toolbar->active_buttons as $button ) :
		$toolbar_fields[] = $button[ 'slug' ];
?>
								<li data-slug="<?php echo $button[ 'slug' ]; ?>" class="ui-widget-content button_<?php echo $button[ 'slug' ];  ?>">
									<span class="ui-widget-item"<?php if ( isset( $button[ 'tooltip' ] ) && ! empty( $button[ 'tooltip' ] ) ) : ?> title="<?php echo $button[ 'tooltip' ]; ?>"<?php endif; ?>>
										<h5 class="ui-widget-header"><?php if ( isset( $button[ 'label' ] ) ) : echo $button[ 'label' ]; endif; ?></h5>
									<?php
										if ( strpos( $button[ 'slug' ], "pipe" ) !== FALSE ) :
											echo "|";
										else :
											echo "<button class=\"" . str_replace( 'mmd-', 'mmd_', str_replace( '_', '-', $button[ 'slug' ] ) ) . "\">" . $button[ 'icon' ] . "</button>";
										endif;
									?>
									</span>
								<?php if ( strpos( $button[ 'slug' ], 'spell_check' ) === FALSE ) : ?>
									<a href="#button_<?php echo $button[ 'slug' ]; ?>" class="ui-trash-link" title="Delete button"><i class="fa fa-times" aria-hidden="true"></i></a>
								<?php endif; ?>
								</li>
<?php
	endforeach;
?>
							</ul>
							<p><small><?php _e( 'Buttons related to languages are represented as a single grey globe button, and will only be displayed if the related spell checkers are enabled.', 'markup-markdown' ); ?></small></p>
						</div>
						<div id="default_buttons" class="editor-toolbar">
							<h4 class="ui-widget-header"><?php _e( 'Available Buttons', 'markup-markdown' ); ?></h4>
							<p><?php _e( 'You can drag the following buttons and drop them to the toolbar above.', 'markup-markdown' ); ?></p>
							<ul id="toolbar_buttons" class="connected ui-helper-clearfix">
<?php
	foreach( $my_toolbar->unused_buttons as $button ) :
?>
								<li data-slug="<?php echo $button[ 'slug' ]; ?>" class="ui-widget-content button_<?php echo $button[ 'slug' ]; ?>">
									<span class="ui-widget-item"<?php if ( isset( $button[ 'tooltip' ] ) && ! empty( $button[ 'tooltip' ] ) ) : ?> title="<?php echo $button[ 'tooltip' ]; ?>"<?php endif; ?>>
										<h5 class="ui-widget-header"><?php if ( isset( $button[ 'label' ] ) ) : echo $button[ 'label' ]; endif; ?></h5>
									<?php
										if ( strpos( $button[ 'slug' ], "pipe" ) !== FALSE ) :
											echo "|";
										else :
											echo "<button class=\"" . str_replace( 'mmd-', 'mmd_', str_replace( '_', '-', $button[ 'slug' ] ) ) . "\">" . $button[ 'icon' ] . "</button>";
										endif;
									?>
									</span>
									<a href="#button_<?php echo $button[ 'slug' ]; ?>" class="ui-trash-link" title="Delete button"><i class="fa fa-times" aria-hidden="true"></i></a>
								</li>
<?php
	endforeach;
?>
							</ul>
						</div>
					</div>
					<input type="hidden" name="mmd_toolbar" id="mmd_toolbar" value="<?php echo implode( ",", $toolbar_fields ); ?>" />
				</td>
			</tr>

		</tbody>
	</table>
</div><!-- #tab-layout -->
