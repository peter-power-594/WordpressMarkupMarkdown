<?php

namespace MarkupMarkdown;

defined( 'ABSPATH' ) || exit;

class SpellCheckerAddon {


	private $prop = array(
		'slug' => 'hungspellchecker',
		'label' => 'Hung Spell Checker',
		'desc' => 'Multilingual spell checker for your posts! Enable live spellchecking with multiple languages while writing your articles.',
		'release' => 'experimental',
		'active' => 0
	);


	/**
	 * @property Array $dictionaries The languages list for spell checker
	 * @see https://github.com/titoBouzout/Dictionaries
	 *
	 * @since 1.9.1
	 * @access protected
	 */
	protected $dictionaries = array(
		'arabic' => array( 'code' => 'ar', 'label' => 'Arabic', 'file_name' => 'Arabic' ),
		'belarusian' => array( 'code' => 'be', 'label' => 'Беларуская -- Belarusian (Official)', 'file_name' => 'be-official' ),
		'bulgarian' => array( 'code' => 'bg', 'label' => 'български -- Bulgarian', 'file_name' => 'Bulgarian' ),
		'catalan' => array( 'code' => 'ca', 'label' => 'Català -- Catalan', 'file_name' => 'Catalan' ),
		'czech' => array( 'code' => 'cs', 'label' => 'Čeština -- Czech', 'file_name' => 'Czech' ),
		'danish' => array( 'code' => 'da', 'label' => 'Dansk -- Danish', 'file_name' => 'Danish' ),
		'german_at' => array( 'code' => 'de_AT', 'label' => 'Deutsch (AT) -- German (Austria)', 'file_name' => 'German_de_AT' ),
		'german_ch' => array( 'code' => 'de_CH', 'label' => 'Deutsch (CH) -- German (Switzerland)', 'file_name' => 'German_de_CH' ),
		'german_de' => array( 'code' => 'de', 'label' => 'Deutsch (DE) -- German', 'file_name' => 'German_de_DE' ),
		'greek' => array( 'code' => 'el', 'label' => 'Ελληνικά -- Greek', 'file_name' => 'Greek' ),
		'english_american' => array( 'code' => 'en_US', 'label' => 'English (American)', 'file_name' => 'English (American)' ),
		'english_australian' => array( 'code' => 'en_AU', 'label' => 'English (Australian)', 'file_name' => 'English (Australian)' ),
		'english_british' => array( 'code' => 'en_GB', 'label' => 'English (British)', 'file_name' => 'English (British)' ),
		'english_canadian' => array( 'code' => 'en_CA', 'label' => 'English (Canadian)', 'file_name' => 'English (Canadian)' ),
		'english_southafrica' => array( 'code' => 'en_ZA', 'label' => 'English (South African)', 'file_name' => 'English (South African)' ),
		'spanish' => array( 'code' => 'es', 'label' => 'Español -- Spanish', 'file_name' => 'Spanish' ),
		'estonian' => array( 'code' => 'et', 'label' => 'Eesti keel -- Estonian', 'file_name' => 'Estonian' ),
		'basque' => array( 'code' => 'eu', 'label' => 'Euskara -- Basque', 'file_name' => 'Basque' ),
		'french' => array( 'code' => 'fr', 'label' => 'Français -- French', 'file_name' => 'French' ),
		'galego' => array( 'code' => 'gl_ES', 'label' => 'Galego -- Galician', 'file_name' => 'Galego' ),
		'hebrew' => array( 'code' => 'he', 'label' => 'עברית - ישראל  -- Hebrew (Israel)', 'file_name' => 'Hebrew (Israel)' ),
		'croatian' => array( 'code' => 'hr', 'label' => 'Hrvatski -- Croatian', 'file_name' => 'Croatian' ),
		'hungarian' => array( 'code' => 'hu', 'label' => 'Magyar -- Hungarian', 'file_name' => 'Hungarian' ),
		'armenian_east' => array( 'code' => 'hye', 'label' => 'հայերեն (արևելյան) -- Armenian (Eastern)', 'file_name' => 'Armenian (Eastern)' ),
		'armenian_west' => array( 'code' => 'hyw', 'label' => 'հայերեն (արևմտյան) -- Armenian (Western)', 'file_name' => 'Armenian (Western)' ),
		'indonesian' => array( 'code' => 'id', 'label' => 'Bahasa Indonesia -- Indonesian', 'file_name' => 'Indonesian' ),
		'dutch' => array( 'code' => 'nl', 'label' => 'Nederlands -- Dutch', 'file_name' => 'Dutch' ),
		'icelandic' => array( 'code' => 'is', 'label' => 'Íslenska -- Icelandic', 'file_name' => 'Icelandic' ),
		'italian' => array( 'code' => 'it', 'label' => 'Italiano -- Italian', 'file_name' => 'Italian' ),
		'korean' => array( 'code' => 'ko', 'label' => '한국인 -- Korean', 'file_name' => 'Korean' ),
		'latvian' => array( 'code' => 'la', 'label' => 'Latvijas -- Latvian', 'file_name' => 'Latvian' ),
		'lithuanian' => array( 'code' => 'lt', 'label' => 'Lietuvių -- Lithuanian', 'file_name' => 'Lithuanian' ),
		'luxembourgish' => array( 'code' => 'lb', 'label' => 'Lëtzebuergesch -- Luxembourgish', 'file_name' => 'Luxembourgish' ),
		'mongolian' => array( 'code' => 'mn', 'label' => 'Монгол -- Mongolian', 'file_name' => 'Mongolian' ),
		'malaysian' => array( 'code' => 'ms', 'label' => 'Malays -- Malaysian', 'file_name' => 'Malays' ),
		'norwegian_nb' => array( 'code' => 'nb', 'label' => 'Norsk (Bokmål) -- Norwegian', 'file_name' => 'Norwegian (Bokmal)' ),
		'norwegian_nn' => array( 'code' => 'nn', 'label' => 'Norsk (Nynorsk) -- Norwegian', 'file_name' => 'Norwegian (Nynorsk)' ),
		'occitan_fr' => array( 'code' => 'oc', 'label' => 'Occitan (France) -- Occitan', 'file_name' => 'Occitan (France)' ),
		'persian' => array( 'code' => 'fa', 'label' => 'فارسی -- Persian', 'file_name' => 'Persian' ),
		'polish' => array( 'code' => 'pl', 'label' => 'Polski -- Polish', 'file_name' => 'Polish' ),
		'portuguese_br' => array( 'code' => 'pt_BR', 'label' => 'Português (Brasileiro) -- Portuguese (Brazilian)', 'file_name' => 'Portuguese (Brazilian)' ),
		'portuguese_pt1' => array( 'code' => 'pt_PT', 'label' => 'Português (Europeu - Antes do Acordo Ortográfico de 1990) -- Portuguese (European - Before the Ortographic Agreement of 1990)', 'file_name' => 'Portuguese (European - Before OA 1990)' ),
		'portuguese_pt2' => array( 'code' => 'pt_PT', 'label' => 'Português (Europeu) -- Portuguese (European)', 'file_name' => 'Portuguese (European)' ),
		'romanian_ro1' => array( 'code' => 'ro', 'label' => 'Română -- Romanian (Ante1993)', 'file_name' => 'Romanian (Ante1993)' ),
		'romanian_ro2' => array( 'code' => 'ro', 'label' => 'Română -- Romanian (Modern)', 'file_name' => 'Romanian (Modern)' ),
		'russian_ru1' => array( 'code' => 'ru', 'label' => 'Русский -- Russian (Bilingual English Unified)', 'file_name' => 'Russian-English Bilingual' ),
		'russian_ru2' => array( 'code' => 'ru', 'label' => 'Русский -- Russian (Modern)', 'file_name' => 'Russian' ),
		'russian_ru3' => array( 'code' => 'ru', 'label' => 'Русский -- Russian (Pre-reform of 1918)', 'file_name' => 'ru_petr1708' ),
		'slovak' => array( 'code' => 'sk_SK', 'label' => 'Slovenčina -- Slovak', 'file_name' => 'Slovak_sk_SK' ),
		'serbian_latn' => array( 'code' => 'sr_Latn', 'label' => 'Srpski (Latinica) -- Serbian (Latin)', 'file_name' => 'Serbian (Cyrillic)' ),
		'serbian_cyrl' => array( 'code' => 'sr_Cyrl', 'label' => 'Srpski (Ćirilica) -- Serbian (Cyrillic)', 'file_name' => 'Serbian (Latin)' ),
		'slovenian' => array( 'code' => 'sl', 'label' => 'Slovenščina -- Slovenian', 'file_name' => 'Slovenian' ),
		'swedish' => array( 'code' => 'sv', 'label' => 'Svenska -- Swedish', 'file_name' => 'Swedish' ),
		'turkish' => array( 'code' => 'tr', 'label' => 'Türkçe -- Turkish', 'file_name' => 'Turkish' ),
		'ukrainian' => array( 'code' => 'uk_UA', 'label' => 'Українська -- Ukrainian', 'file_name' => 'Ukrainian_uk_UA' ),
		'ukrainian' => array( 'code' => 'uk_UA', 'label' => 'Українська -- Ukrainian', 'file_name' => 'Ukrainian_uk_UA' ),
		'vietnamese' => array( 'code' => 'vi', 'label' => 'Tiếng Việt -- Vietnamese', 'file_name' => 'Vietnamese_vi_VN' ),
	);


	private $dict_dir = '';


	public function __construct() {
		if ( ! defined( 'MMD_ADDONS' ) || ( defined( 'MMD_ADDONS' ) && in_array( $this->prop[ 'slug' ], MMD_ADDONS ) === FALSE ) ) :
			$this->prop[ 'active' ] = 0;
			return FALSE; # Addon has been desactivated
		endif;
		$this->prop[ 'active' ] = 1;
		mmd()->default_conf = array( 'MMD_SPELL_CHECK' => array() );
		$this->dict_dir = WP_CONTENT_DIR . "/mmd-dict/";
		if ( is_admin() ) :
			add_filter( 'mmd_verified_config', array( $this, 'update_config' ) );
			add_filter( 'mmd_var2const', array( $this, 'create_const' ) );
			add_action( 'admin_enqueue_scripts', array( $this, 'load_spellchecker_assets' ), 11 , 1 );
		endif;
	}


	public function __get( $name ) {
		if ( array_key_exists( $name, $this->prop ) ) {
			return $this->prop[ $name ];
		}
		return 'mmd_undefined';
	}


	/**
	 * Filter to parse spellchecker options inside the options screen when the form was submitted
	 *
	 * @since 2.0.0
	 * @access public
	 *
	 * @return Void
	 */
	public function update_config( $my_cnf ) {
		$my_cnf[ 'spell_check' ] = filter_input( INPUT_POST, 'mmd_spell_check', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY );
		$my_cnf[ 'def_lang' ] = filter_input( INPUT_POST, 'mmd_default_spell_checker', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
		return $my_cnf;
	}
	public function create_const( $my_cnf ) {
		$my_cnf[ 'spell_check' ] = isset( $my_cnf[ 'spell_check' ] ) && is_array( $my_cnf[ 'spell_check' ] ) ? $my_cnf[ 'spell_check' ] : [];
		if ( isset( $my_cnf[ 'def_lang' ] ) && ! empty( $my_cnf[ 'def_lang' ] ) ) :
			$pos = array_search( $my_cnf[ 'def_lang' ], $my_cnf[ 'spell_check' ] );
			if ( $pos !== 0 ) :
				$my_cnf[ 'spell_check' ] = array_merge( array_splice( $my_cnf[ 'spell_check' ], $pos, 1 ), $my_cnf[ 'spell_check' ] );
			endif;
		endif;
		$my_cnf[ 'MMD_SPELL_CHECK' ] = $my_cnf[ 'spell_check' ];
		unset( $my_cnf[ 'spell_check' ] );
		unset( $my_cnf[ 'def_lang' ] );
		return $my_cnf;
	}


	public function load_spellchecker_assets( $hook ) {
		if ( 'post.php' === $hook || 'post-new.php' === $hook ) :
			wp_add_inline_script( 'markup_markdown__wordpress_richedit', $this->add_inline_editor_conf() );
		elseif ( 'settings_page_markup-markdown-admin' === $hook ) :
			add_action( 'mmd_before_options', array( $this, 'install_spell_checker' ) );
			add_action( 'mmd_tabmenu_options', array( $this, 'add_tabmenu' ) );
			add_action( 'mmd_tabcontent_options', array( $this, 'add_tabcontent' ) );
		endif;
	}



	/**
	 * Method to automatically switch the default dictionary in used when
	 * editing a post in an alternative language - for example with Polylang
	 *
	 * @access private
	 * @since 1.9.3
	 *
	 * @param Array $dict the dictionaries locales list
	 * @returns Array $my_dict the list updated with different order if need be
	 */
	private function check_dict_preferences( $my_dict = [] ) {
		if ( ! function_exists( 'pll_get_post_language' ) ) : # Polylang Plugin
			return $my_dict;
		endif;
		# New post: retrieve the lang from the "new_lang" url argument
		$locale = filter_input( INPUT_GET, 'new_lang', FILTER_SANITIZE_SPECIAL_CHARS );
		if ( ! isset( $locale ) || ! $locale || empty( $locale ) ) :
			# Existing post: retrieve the lang from post_id through pll_get_post_language
			$post_id = filter_input( INPUT_GET, 'post', FILTER_VALIDATE_INT );
			if ( ! isset( $post_id ) || ! is_numeric( $post_id ) || $post_id < 1 ) :
				return $my_dict;
			else :
				$locale = pll_get_post_language( $post_id, 'locale' );
			endif;
		endif;
		if ( ! isset( $locale ) || ! $locale || empty( $locale ) ) :
			return $my_dict;
		endif;
		if ( strpos( $locale, "_" ) !== FALSE ) : # "en_US" => [ "en", "US" ]
			$curr_locales = explode( "_", $locale );
		else : # "en" => [ "en" ]
			$curr_locales = [ $locale ];
		endif;
		$my_locales = [];
		foreach( $my_dict as $dict_name ) :
			$my_locales[] = $this->dictionaries[ $dict_name ][ 'code' ];
		endforeach;
		foreach( $curr_locales as $my_locale ) :
			if ( in_array( $my_locale, $my_locales ) !== FALSE ) :
				$pos = array_search( $my_locale, $my_locales );
				$primary = array_splice( $my_dict, $pos, 1 );
				$my_dict = array_merge( $primary, $my_dict );
				break;
			endif;
		endforeach;
		return $my_dict;
	}


	/**
	 * Method to setup spellchecker and other editor options as inline code
	 *
	 * @access public
	 * @since 1.9.1
	 *
	 * @returns string inline easymde configuration tool
	 */
	public function add_inline_editor_conf() {
		$home_url = get_home_url();
		$js = "wp.pluginMarkupMarkdown = wp.pluginMarkupMarkdown || {};\n";
		$js .= "wp.pluginMarkupMarkdown.homeURL = \"" . $home_url . "\";\n";
		$my_dict = $this->check_dict_preferences( defined( 'MMD_SPELL_CHECK' ) ? MMD_SPELL_CHECK : [] );
		$js .= "wp.pluginMarkupMarkdown.spellChecker = {\n";
		$n = 0; $dict_base_uri = str_replace( '/plugins/markup-markdown/', '/mmd-dict/', mmd()->plugin_uri );
		foreach ( $my_dict as $dict ) :
			if ( ! isset( $this->dictionaries[ $dict ] ) ) :
				continue;
			endif;
			$lang_code = $this->dictionaries[ $dict ][ 'code' ];
			$lang_label = $this->dictionaries[ $dict ][ 'label' ];
			$lang_filename = urlencode( $this->dictionaries[ $dict ][ 'file_name' ] );
			$n++; if ( $n > 1 ) : $js .= ",\n"; endif;
			$js .= "  " . $dict . ": {\n"
				. "    code: \"" . $lang_code . "\",\n"
				. "    label: \"" . $lang_label . "\",\n"
				. "    aff: \"" . $dict_base_uri . urlencode( $lang_filename ) . ".aff\",\n"
				. "    dic: \"" . $dict_base_uri . urlencode( $lang_filename ) . ".dic\"\n"
				. "  }";
		endforeach;
		$js .= "\n};";
		return $js;
	}


	/**
	 * Install spellchecker dictionnaries
	 *
	 * @since 1.9.1
	 * @access private
	 *
	 * @return Boolean TRUE if the new dictionary was installed
	 * or FALSE if nothing was installed or the target dictionary already exists
	 */
	public function install_spell_checker() {
		$nonce = filter_input( INPUT_GET, '_mmd_sc_nonce', FILTER_SANITIZE_SPECIAL_CHARS );
		if ( ! $nonce || empty( $nonce ) ) :
			# Empty or unavailable nonce, nothing to do from here
			return FALSE;
		endif;
		if ( ! wp_verify_nonce( $nonce, "spell_checker" ) ) :
			# Invalide nonce. Might be refresh or cache issue
			error_log( 'MMD: The spell checker _nonce is not valid' );
			return FALSE;
		endif;
		$dict_id = filter_input( INPUT_GET, 'dict', FILTER_SANITIZE_SPECIAL_CHARS );
		if ( ! isset( $dict_id ) || ! $dict_id || empty( $dict_id ) ) :
			# The dictionary argument ID is missing, dont' know what to install
			return FALSE;
		endif;
		if ( ! isset( $this->dictionaries[ $dict_id ] ) ) :
			# Doesn't look like a known dictionary
			return FALSE;
		endif;
		$dict_name = $this->dictionaries[ $dict_id ][ 'file_name' ];
		$dict_dir = $this->dict_dir;
		if ( file_exists( $dict_dir . '/' . urlencode( $dict_name ) . '.aff' ) && file_exists( $dict_dir . '/' . urlencode( $dict_name ) . '.dic' ) ) :
			# Dictionary already installed, don't do anything
			return FALSE;
		endif;
		if ( ! is_dir( $dict_dir ) ) :
			mkdir( $dict_dir );
			touch( $dict_dir . '/index.php' );
			file_put_contents( $dict_dir . '/index.php', "<?php\n// Silence is Gold\n?>", );
		endif;
		$packages = [
			'aff' => 'aff. data',
			'dic' => 'dict. data',
			'txt' => 'lic. data'
		];
		$base = "https://raw.githubusercontent.com/titoBouzout/Dictionaries/master";
		foreach ( $packages as $dict_ext => $dict_desc ) :
			$response = wp_remote_get( $base . '/' . $dict_name . '.' . $dict_ext  );
			if ( is_wp_error( $response ) || ! is_array( $response ) || ! isset( $response[ 'body' ] ) ) :
				error_log( 'Markup Markdown : Error while trying to retrieve the ' . $dict_desc . ' about the dictionary ' . $dict_id );
				continue;
			endif;
			file_put_contents( $dict_dir . '/' . urlencode( $dict_name ) . '.' . $dict_ext, $response[ 'body' ] );
			unset( $response ); # Be kind?
			sleep( 1 ); # With rental server?
		endforeach;
		return TRUE;
	}


	/**
	 * Show the spellchecker tab item inside the options screen
	 *
	 * @since 2.0.0
	 * @access public
	 *
	 * @return Void
	 */
	public function add_tabmenu() {
		echo "\t\t\t\t\t\t<li><a href=\"#tab-spellchecker\">Spell Checker (Experimental)</a></li>\n";
	}


	/**
	 * Display spellchecker options inside the options screen
	 *
	 * @since 1.9.1
	 * @access public
	 *
	 * @return Void
	 */
	public function add_tabcontent() {
		$my_cnf[ 'spellcheck' ] = defined( 'MMD_SPELL_CHECK' ) ? MMD_SPELL_CHECK : [];
?>
					<div id="tab-spellchecker">
						<h3>Spell Checker</h3>
						<p>
							Dictionaries available from your browser (Firefox, Chrome, Edge, ...) or your operating system (Linux, Macintosh, Windows, etc...) can't be used or accessed as it, you need to select and install specific dictionaries so they can be downloaded on your server and used with the markdown editor while you input your text.
							<br />
							Data are borrowed from 3rd parties software (Sublime, OpenOffice, Mozilla, etc...), some languages are not available and some variants probably missing. Data are free to use (*GPL or similar licenses), try to contribute to the original project mostly done by volunteers if you want better spell checking.
						</p>
						<h4>Monolingual: Usable, performances are ok.</h4>
						<h4>Bilingual: Unstable, try with caution.</h4>
						<p>
							When possible I would advise using dictionaries embedded in two languages like Russian-English.<br />
							In case you need to activate multiple languages, please read the following disclaimers carefully:
						</p>
						<p>
							Disclaimer 1: <em>Size matters! I don't recommend to activate more than 2 languages</em><br />
							Remember the related files (a few megabytes) will be loaded in the memory of your browser so depending on the weight of the related files AND the specification/ status of your computer, the editor might freeze for a few seconds, especially when accessing the edit screen. Please be gentle and patient... In the worst case well you won't be able to use it and will need to disable it. Can't do better on my side.
						</p>
						<p>
							Disclaimer 2: <em>There is no automatic language detection for spell checking</em><br />
							If you activate more than one dictionary, you have to set one as the default. Then in the editor, new buttons for the alternative languages will be shown in the toolbar so you can select the text and flag it as a different language. Following the markdown specification, it will be displayed as pure custom HTML. The code in your content is gonna look like this: &lt;span lang="XXX"&gt;My text in another language&lt;/span&gt; where XXX is the code of the language as listed below. It might not be the easiest approach, regards accessibilities specifications you should already define the language in case you are using multiple languages on the same page!
						</p>
						<p>
							Disclaimer 3: <em>One specific dictionary per language</em><br />
							Sounds obvious, multilingual means multiple languages on the same medium. With the current interface you <em>could</em> try activating two variants of the same parent language, for exemple American English and British English, that won't work of course!!! (Or they will be really odd side effects)
						</p>
						<table class="form-table" role="presentation">
							<tbody>
<?php
	$dict_dir = $this->dict_dir;
	$dict_base_uri = str_replace( '/plugins/markup-markdown/', '/mmd-dict/', mmd()->plugin_uri );
	foreach( $this->dictionaries as $dict_id => $dictionary ) :
		$dictionary[ 'file_name' ] = urlencode( $dictionary[ 'file_name' ] );
		echo "\n\t\t\t\t\t\t<tr>";
		echo "\n\t\t\t\t\t\t\t<th scope=\"row\" class=\"lang-code\">" . $dictionary[ 'code' ] . "</th>";
		echo "\n\t\t\t\t\t\t\t<th scope=\"row\">" . $dictionary[ 'label' ];
		if ( file_exists( $dict_dir . '/' . $dictionary[ 'file_name' ] . '.txt' ) ) :
			echo " (<a href=\"" . $dict_base_uri . urlencode( $dictionary[ 'file_name' ] ) . ".txt\" target=\"_blank\">Info</a>)";
		endif;
		echo "</th>";
		if ( file_exists( $dict_dir . '/' . $dictionary[ 'file_name' ] . '.dic' ) && file_exists( $dict_dir . '/' . $dictionary[ 'file_name' ] . '.aff' ) ) :
			echo "\n\t\t\t\t\t\t\t<td>";
			$isActive = isset( $my_cnf[ 'spellcheck' ] ) && is_array( $my_cnf[ 'spellcheck' ] ) && in_array( $dict_id, $my_cnf[ 'spellcheck' ] ) ? 1 : 0;
			echo "\n\t\t\t\t\t\t\t\t<label for=\"mmd_spell_check_" . $dict_id . "\">"
				. "\n\t\t\t\t\t\t\t\t\t<input type=\"checkbox\" name=\"mmd_spell_check[]\" id=\"mmd_spell_check_" . $dict_id . "\" value=\"" . $dict_id . "\"" . (  $isActive ? " checked=\"checked\"" : " " ) . "/>"
				. "\n\t\t\t\t\t\t\t\t\t" . ( $isActive ? "Active" : "Installed, check to activate" );
			echo "\n\t\t\t\t\t\t\t\t</label>";
			echo "\n\t\t\t\t\t\t\t</td>";
			echo "\n\t\t\t\t\t\t\t<td>";
			if ( $isActive ) :
				echo "\n\t\t\t\t\t\t\t\t<label for=\"mmd_default_" . $dict_id . "\">";
				echo "\n\t\t\t\t\t\t\t\t\t<input type=\"radio\" name=\"mmd_default_spell_checker\" id=\"mmd_default_" . $dict_id . "\" value=\"" . $dict_id . "\"";
				if ( array_search( $dict_id, $my_cnf[ 'spellcheck' ] ) === 0 ) :
					echo " checked=\"checked\" /> Default";
				else :
					echo " /> Make default";
				endif;
				echo "\n\t\t\t\t\t\t\t\t</label>";
			endif;
			echo "\n\t\t\t\t\t\t\t</td>";
		else :
			echo "\n\t\t\t\t\t\t\t<td colspan=\"2\">";
			echo "\n\t\t\t\t\t\t\t\t<a href=\"" . wp_nonce_url( menu_page_url( "markup-markdown-admin", false ), "spell_checker", "_mmd_sc_nonce" ) . "&dict=" . $dict_id . "#tab-spellchecker\">Install</a>";
		endif;
		echo "\n\t\t\t\t\t\t\t</td>";
		echo "\n\t\t\t\t\t\t</tr>";
	endforeach;
?>
							</tbody>
						</table>
					</div><!-- .tab-spellchecker -->
<?php
	}


}
