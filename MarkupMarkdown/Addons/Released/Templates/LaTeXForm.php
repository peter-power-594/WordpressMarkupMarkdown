<?php defined( 'ABSPATH' ) || exit; ?>

<div id="tab-latex">
	<h2><?php esc_html_e( 'LaTeX', 'markup-markdown' ); ?></h2>
	<p><?php esc_html_e( 'Easily type and render math formulas inside your post.', 'markup-markdown' ); ?></p>
	<table class="form-table" role="presentation">
		<tbody>
<?php
	$my_cnf = array(
		'latex' => 'none',
		'latex_front' => 0
	);
	if ( defined( 'MMD_USE_LATEX' ) && is_array( MMD_USE_LATEX ) ) :
		if ( isset( MMD_USE_LATEX[ 1 ] ) ) :
			$my_cnf[ 'latex' ] = MMD_USE_LATEX[ 1 ];
		endif;
		if ( isset( MMD_USE_LATEX[ 2 ] ) ) :
			$my_cnf[ 'latex_front' ] = MMD_USE_LATEX[ 2 ];
		endif;
	endif;
?>
			<tr class="site-use-latex">
				<th scope="row">
					<?php esc_html_e( 'Rendering engine', 'markup-markdown' ); ?>
				</th>
				<td>
					<label for="mmd_uselatex0">
						<input type="radio" name="mmd_uselatex" id="mmd_uselatex0" value="none" <?php echo ! isset( $my_cnf[ 'latex' ] ) || $my_cnf[ 'latex' ] === 'none' ? 'checked="checked"' : ''; ?> />
						<?php esc_html_e( 'None', 'markup-markdown' ); ?>
					</label>&nbsp;&nbsp;
					<label for="mmd_uselatex1">
						<input type="radio" name="mmd_uselatex" id="mmd_uselatex1" value="katex" <?php echo isset( $my_cnf[ 'latex' ] ) && $my_cnf[ 'latex' ] === 'katex' ? 'checked="checked"' : ''; ?> />
						<?php esc_html_e( 'Katex rendering', 'markup-markdown' ); ?>
					</label>&nbsp;&nbsp;
					<label for="mmd_uselatex2">
						<input type="radio" name="mmd_uselatex" id="mmd_uselatex2" value="mathml" <?php echo isset( $my_cnf[ 'latex' ] ) && $my_cnf[ 'latex' ] === 'mathml' ? 'checked="checked"' : ''; ?> />
						<?php esc_html_e( 'Mathml rendering', 'markup-markdown' ); ?>
					</label>
				</td>
			</tr>
			<tr class="site-load-front">
				<th scope="row">
					<?php esc_html_e( 'Load assets', 'markup-markdown' ); ?>
				</th>
				<td>
					<label for="mmd_latex_front">
						<input type="checkbox" name="mmd_latex_front" id="mmd_latex_front" value="1" <?php echo isset( $my_cnf[ 'latex_front' ] ) && (int)$my_cnf[ 'latex_front' ] > 0 ? 'checked="checked"' : ''; ?> />
						<?php esc_html_e( 'Load the LaTeX engine related assets on the frontend as well. (Only added to the edit screen by default)', 'markup-markdown' ); ?>
					</label>
				</td>
			</tr>
		</tbody>
	</table>
</div><!-- #tab-latex  -->
