<?php defined( 'ABSPATH' ) || exit; ?>

<div id="tab-layout">
	<h2>Layout</h2>
	<p>Here are a few settings you can change to modify the behavior of your blog posts.</p>
	<table class="form-table" role="presentation">
		<tbody>
<?php
	$my_cnf = array(
		'lightbox' => defined( 'MMD_USE_LIGHTBOX' ) ? MMD_USE_LIGHTBOX : 1,
		'masonry' => defined( 'MMD_USE_MASONRY' ) ? MMD_USE_MASONRY : 1,
		'imagesloaded' => defined( 'MMD_USE_IMAGESLOADED' ) ? MMD_USE_IMAGESLOADED : 1,
		'goodvibes' => defined( 'MMD_USE_BLOCKSTYLES' ) ? MMD_USE_BLOCKSTYLES : 0
	);
?>
			<tr class="site-use-lightbox">
				<th scope="row">
					Use Gutenberg blocks styles
				</th>
				<td>
					<label for="mmd_goodvibes">
						<input type="checkbox" name="mmd_goodvibes" id="mmd_goodvibes" value="1" <?php echo (int)$my_cnf[ 'goodvibes' ] > 0 ? 'checked="checked"' : ''; ?> />
						Keep a minimum of assets active if your theme was designed to be used with Gutenberg. Can avoid broken layout in case styles are missing.
					</label>
				</td>
			</tr>
			<tr class="site-use-lightbox">
				<th scope="row">
					Use Lightbox
				</th>
				<td>
					<label for="mmd_lightbox">
						<input type="checkbox" name="mmd_lightbox" id="mmd_lightbox" value="1" <?php echo (int)$my_cnf[ 'lightbox' ] > 0 ? 'checked="checked"' : ''; ?> />
						An image inside a <em>post</em> or <em>page</em> that was linked to its original size will open in a modal (overlay on the same page) instead of a new window / tab.
					</label>
				</td>
			</tr>
			<tr class="site-use-masonry">
				<th scope="row">
					Use Masonry
				</th>
				<td>
					<label for="mmd_masonry">
						<input type="checkbox" name="mmd_masonry" id="mmd_masonry" value="1" <?php echo (int)$my_cnf[ 'masonry' ] > 0 ? 'checked="checked"' : '';; ?>>
						Transform a bullet list of images as a 2 waterfall column layout when the <em>photo gallery</em> post format is selected.
					</label>
				</td>
				<tr class="site-use-imagesloaded">
					<th scope="row">
						Use Imagesloaded
					</th>
					<td>
						<label for="mmd_imagesloaded">
							<input type="checkbox" name="mmd_imagesloaded" id="mmd_imagesloaded" value="1" <?php echo (int)$my_cnf[ 'imagesloaded' ] > 0 ? 'checked="checked"' : '';; ?> />
							Trigger the update of the layout after all images are loaded. Can solve specific issues in case the layout is broken with the gallery.
						</label>
					</td>
				</tr>
			</tr>
			<tr class="site-default-toolbar">
				<th scope="row">
					Custom toolbar
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
							<h4 class="ui-widget-header">Current Toolbar</h4>
							<p>
								Here is a preview of your toolbar, you can sort the buttons.<br />
								Buttons related to languages are represented as a single grey globe button, and will only be displayed if the related spell checkers are enabled.
							</p>
							<ul id="my_buttons" class="connected">
<?php

	$toolbar_fields = [];
	foreach( $my_toolbar->active_buttons as $button ) :
		$toolbar_fields[] = $button[ 'slug' ];
?>
								<li data-slug="<?php echo $button[ 'slug' ]; ?>" class="ui-widget-content button_<?php echo $button[ 'slug' ];  ?>">
									<span class="ui-widget-item"<?php if ( ! empty( $button[ 'tooltip' ] ) ) : ?> title="<?php echo $button[ 'tooltip' ]; ?>"<?php endif; ?>>
										<h5 class="ui-widget-header"><?php echo $button[ 'label' ]; ?></h5>
									<?php
										if ( $button[ 'slug' ] === "pipe" ) :
											echo "|";
										else :
											echo "<button class=\"" . str_replace( '_', '-', $button[ 'slug' ] ) . "\">" . $button[ 'icon' ] . "</button>";
										endif;
									?>
									</span>
								<?php if ( 'spell_check' !== $button[ 'slug' ] ) : ?>
									<a href="#button_<?php echo $button[ 'slug' ]; ?>" class="ui-trash-link" title="Delete button"><i class="fa fa-times" aria-hidden="true"></i></a>
								<?php endif; ?>
								</li>
<?php
	endforeach;
?>
							</ul>
						</div>
						<div id="default_buttons" class="editor-toolbar">
							<h4 class="ui-widget-header">Available Buttons</h4>
							<p>You can drag the following buttons and drop them to the toolbar above.</p>
							<ul id="toolbar_buttons" class="connected ui-helper-clearfix">
<?php
	foreach( $my_toolbar->unused_buttons as $button ) :
?>
								<li data-slug="<?php echo $button[ 'slug' ]; ?>" class="ui-widget-content button_<?php echo $button[ 'slug' ]; ?>">
									<span class="ui-widget-item" title="<?php echo $button[ 'tooltip' ]; ?>">
										<h5 class="ui-widget-header"><?php echo $button[ 'label' ]; ?></h5>
									<?php
										if ( $button[ 'slug' ] === "pipe" ) :
											echo "|";
										else :
											echo "<button class=\"" . str_replace( '_', '-', $button[ 'slug' ] ) . "\">" . $button[ 'icon' ] . "</button>";
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
