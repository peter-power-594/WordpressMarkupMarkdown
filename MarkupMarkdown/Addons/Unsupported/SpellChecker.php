<?php

namespace MarkupMarkdown\Addons\Unsupported;

defined( 'ABSPATH' ) || exit;

class SpellChecker {


	private $prop = array(
		'slug' => 'hungspellchecker',
		'release' => 'experimental',
		'active' => 0
	);


	protected $dict_dir = '';

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


	/**
	 * @property Array $extra Additional words to exclude from the spell checking
	 * @see https://github.com/peter-power-594/codemirror-spell-checker
	 *
	 * @since 3.5.1
	 * @access protected
	 */
	protected $extra = array(
		'french' => array( 'file_name' => 'fr_FR' ),
		'english_american' => array( 'file_name' => 'en_US' ),
	);


	public function __construct() {
		$this->prop[ 'label' ] = __( 'Hung Spell Checker', 'markup-markdown' );
		$this->prop[ 'desc' ] = __( 'Multilingual spell checker for your posts! Enable live spellchecking with multiple languages while writing your articles.', 'markup-markdown' );
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
		else :
			add_action( 'wp_footer', array( $this, 'load_engine_assets' ), 12 );
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
			add_action( 'admin_footer', array( $this, 'load_engine_assets' ) );
		elseif ( 'settings_page_markup-markdown-admin' === $hook ) :
			add_action( 'mmd_before_options', array( $this, 'install_spell_checker' ) );
			add_action( 'mmd_tabmenu_options', array( $this, 'add_tabmenu' ) );
			add_action( 'mmd_tabcontent_options', array( $this, 'add_tabcontent' ) );
		endif;
	}


	/**
	 * Method to add the javascript inline settings for the spell checkers
	 * Hooked to the head previously, now hooked to the footer
	 *
	 * @access public
	 * @since 3.0
	 *
	 * @return Void
	 */
	public function load_engine_assets() {
		wp_add_inline_script( 'markup_markdown__wordpress_richedit', $this->add_inline_editor_conf() );
	}


	/**
	 * Method to automatically switch the default dictionary in used when
	 * editing a post in an alternative language - for example with Polylang
	 *
	 * @access private
	 * @since 1.9.3
	 *
	 * @param Array $my_dict the dictionaries locales list
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
		$my_dict = $this->check_dict_preferences( defined( 'MMD_SPELL_CHECK' ) ? MMD_SPELL_CHECK : [] );
		$js = ''; $n = 0;
		$dict_base_uri = str_replace( '/plugins/markup-markdown/', '/mmd-dict/', mmd()->plugin_uri );
		foreach ( $my_dict as $dict ) :
			if ( ! isset( $this->dictionaries[ $dict ] ) ) :
				continue;
			endif;
			$lang_code = $this->dictionaries[ $dict ][ 'code' ];
			$lang_label = $this->dictionaries[ $dict ][ 'label' ];
			$lang_filename = $this->dictionaries[ $dict ][ 'file_name' ];
			$this->check_for_older_names( $lang_filename );
			$n++;
			if ( $n > 1 ) :
				$js .= ",\n";
			else :
				$js .= "wp.pluginMarkupMarkdown.spellChecker = {\n";
			endif;
			$js .= "  " . $dict . ": {"
				. "\n    code: \"" . $lang_code . "\""
				. ",\n    label: \"" . $lang_label . "\""
				. ",\n    aff: \"" . $dict_base_uri . md5( $lang_filename ) . ".aff\""
				. ",\n    dic: \"" . $dict_base_uri . md5( $lang_filename ) . ".dic\"";
			if ( isset( $this->extra[ $dict ] ) && file_exists( $this->dict_dir . '/' . md5( $lang_filename ) . '_extra.dic' ) ) :
				$js .= ",\n    etr: \"" . $dict_base_uri . md5( $lang_filename ) . "_extra.dic\"";
			endif;
			$js .= "\n  }";
		endforeach;
		if ( ! empty( $js ) ) :
			$js .= "\n};";
			return $js;
		else :
			return '';
		endif;
	}

	/**
	 * Move spellchecker dictionnaries to new file names
	 * Prior to version 3.2 urlencode was used.
	 * In order to use blueprint or other virtual machine,
	 * I switched to md5 to remove any other character than 0-9 a-z
	 *
	 * @since 3.2.1
	 * @access public
	 *
	 * @return Boolean TRUE if the new dictionary were renamed or FALSE
	 */
	public function check_for_older_names( $dict_name = '' ) {
		if ( empty( $dict_name ) ) :
			return false;
		endif;
		$dict_dir = $this->dict_dir;
		$re = 0;
		if ( file_exists( $dict_dir . '/' . urlencode( $dict_name ) . '.aff' ) ) :
			$mv = rename( $dict_dir . '/' . urlencode( $dict_name ) . '.aff', $dict_dir . '/' . md5( $dict_name ) . '.aff' );
			if ( ! $mv ) :
				error_log( "\nWP Markup Markdown: Unable to rename the dictionary file called " . $dict_name . ".aff" );
			else :
				$re++;
			endif;
		endif;
		if ( file_exists( $dict_dir . '/' . urlencode( $dict_name ) . '.dic' ) ) :
			$mv = rename( $dict_dir . '/' . urlencode( $dict_name ) . '.dic', $dict_dir . '/' . md5( $dict_name ) . '.dic' );
			if ( ! $mv ) :
				error_log( "\nWP Markup Markdown: Unable to rename the dictionary file called " . $dict_name . ".dic" );
			else :
				$re++;
			endif;
		endif;
		if ( file_exists( $dict_dir . '/' . urlencode( $dict_name ) . '.txt' ) ) :
			$mv = rename( $dict_dir . '/' . urlencode( $dict_name ) . '.txt', $dict_dir . '/' . md5( $dict_name ) . '.txt' );
			if ( ! $mv ) :
				error_log( "\nWP Markup Markdown: Unable to rename the dictionary file called " . $dict_name . ".dic" );
			else :
				$re++;
			endif;
		endif;
		return $re > 0 ? true : false;
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
			return false;
		elseif ( ! wp_verify_nonce( $nonce, "spell_checker" ) ) :
			# Invalide nonce. Might be refresh or cache issue
			error_log( 'MMD: The spell checker _nonce is not valid' );
			return false;
		endif;
		$dict_id = filter_input( INPUT_GET, 'dict', FILTER_SANITIZE_SPECIAL_CHARS );
		if ( ! isset( $dict_id ) || ! $dict_id || empty( $dict_id ) ) :
			# The dictionary argument ID is missing, dont' know what to install
			return false;
		elseif ( ! isset( $this->dictionaries[ $dict_id ] ) ) :
			# Doesn't look like a known dictionary in our database
			return false;
		endif;
		$dict_name = $this->dictionaries[ $dict_id ][ 'file_name' ];
		$dict_dir = $this->dict_dir;
		$this->check_for_older_names( $dict_name );
		if ( ! file_exists( $dict_dir . '/' . md5( $dict_name ) . '.aff' ) || ! file_exists( $dict_dir . '/' . md5( $dict_name ) . '.dic' ) ) :
			# Dictionary already installed, don't do anything
			$this->install_dictionary( $dict_name, $dict_dir );
		endif;
		if ( isset( $this->extra[ $dic_id ] ) && ! file_exists( $dict_dir . '/' . md5( $dict_name ) . '_extra.dic' ) ) :
			$this->install_extra( $this->extra[ $dic_id ][ 'file_name' ], $dict_name, $dict_dir );
		endif;
		return true;
	}


	/**
	 * Grab and save locally the compressed dictionary and rules file used for spell checking
	 * 
	 * @access private
	 * @since 3.5.1
	 * 
	 * @param String $name The dictionary file name
	 * @param String $dir The dictionary directory
	 * @return Boolean TRUE in case of success of FALSE
	 */
	private function install_dictionary( $name = '', $dir = '' ) {
		if ( empty( $name ) || empty( $dir ) ) :
			return false;
		endif;
		if ( ! is_dir( $dir ) ) :
			mkdir( $dir );
			touch( $dir . '/index.php' );
			file_put_contents( $dir . '/index.php', "<?php\n// Silence is Gold\n?>", );
		endif;
		$packages = array( 'aff' => 'aff. data', 'dic' => 'dict. data', 'txt' => 'lic. data' );
		$base = 'https://raw.githubusercontent.com/titoBouzout/Dictionaries/master';
		foreach ( $packages as $ext => $desc ) :
			$resp = wp_remote_get( $base . '/' . $name . '.' . $ext  );
			if ( is_wp_error( $resp ) || ! is_array( $resp ) || ! isset( $resp[ 'body' ] ) ) :
				error_log( 'WP Markup Markdown: Error while trying to retrieve the ' . $desc . ' for the dictionary ' . $name );
				continue;
			endif;
			file_put_contents( $dir . '/' . md5( $name ) . '.' . $ext, $resp[ 'body' ] );
			unset( $resp ); # Be kind?
			sleep( 1 ); # With rental server?
		endforeach;
		return true;
	}


	/**
	 * Grab and save locally extra data for the spell checker
	 * Mostly to exclude words from the spellchecker when rules from aff can't be handled properly like some conjunctions.
	 * And lately give power to the users to add their own words like brand names or custom language like linuxish words ^^ 
	 * 
	 * @access private
	 * @since 3.5.1
	 * 
	 * @param String $name The dictionary file name
	 * @param String $dir The dictionary directory
	 * @return Boolean TRUE in case of success of FALSE
	 */
	private function install_extra( $name = '', $parent_name = '', $dir = '' ) {
		if ( empty( $name ) || empty( $parent_name ) || empty( $dir ) ) :
			return false;
		endif;
		$base = 'https://raw.githubusercontent.com/peter-power-594/codemirror-spell-checker/dev/src/dict';
		$resp = wp_remote_get( $base . '/' . $name . '.dic' );
		if ( is_wp_error( $resp ) || ! is_array( $resp ) || ! isset( $resp[ 'body' ] ) ) :
			error_log( 'WP Markup Markdown: Error while trying to retrieve the extra data for the dictionary ' . $name );
			return false;
		endif;
		file_put_contents( $dir . '/' . md5( $parent_name ) . '_extra.dic', $resp[ 'body' ] );
		unset( $resp );
		sleep( 1 );
		return true;
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
		echo "\t\t\t\t\t\t<li><a href=\"#tab-spellchecker\" class=\"mmd-ico ico-spellcheck\">" . __( 'Spell Checker', 'markup-markdown' ) . "</a></li>\n";
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
						<h3><?php esc_html_e( 'Spell Checker', 'markup-markdown' ); ?></h3>

						<p>
							<?php esc_html_e( 'Dictionaries available from your browser (Firefox, Chrome, Edge, ...) or your operating system (Linux, Macintosh, Windows, etc ...) can\'t be used or accessed as it is, you need to select and install specific dictionaries to be downloaded on your server and used with the markdown editor while you input your text.', 'markup-markdown' ); ?>
						</p>
						<p>
							<?php esc_html_e( 'Data are borrowed from 3rd parties software (Sublime, OpenOffice, Mozilla, etc ...), some languages are unavailable, and a few variants missing. Data are free to use (*GPL or similar licenses), try to contribute to the original project mostly done by volunteers if you want better spell checking.', 'markup-markdown' ); ?>
						</p>

						<h4>
							<?php esc_html_e( 'Performances', 'markup-markdown' ); ?>
						</h4>
						<p>
							<strong><?php esc_html_e( 'Monolingual: Usable, performances are correct.', 'markup-markdown' ); ?></strong><br />
							<strong><?php esc_html_e( 'Bilingual: Depends on your machine, can be unstable so try to use it with caution.', 'markup-markdown' ); ?></strong>
						</p>
						<p>
							<?php esc_html_e( 'When possible I would advise using dictionaries embedded in two languages like Russian-English.', 'markup-markdown' ); ?><br />
							<?php esc_html_e( 'In case you need to activate multiple languages, please read the following disclaimers carefully:', 'markup-markdown' ); ?>
						</p>

						<h4>
							1) <em><?php esc_html_e( 'File size matters', 'markup-markdown' ); ?></em>
						</h4>
						<p>
							<?php _e( 'I don\'t recommend to activate more than 2 languages. Please remember that the related files (a few megabytes) will be loaded in the memory of your browser so depending on the weight of the related files AND the specification of your computer, the editor might freeze for a few seconds, especially when accessing the edit screen. Please be gentle and patient... In the worst case well you won\'t be able to use it and will need to disable it. Can\'t do better on my side.', 'markup-markdown' ); ?>
						</p>

						<h4>
							2)  <em><?php esc_html_e( 'No automatic language detection', 'markup-markdown' ); ?></em>
						</h4>
						<p>
							<?php esc_html_e( 'If you activate more than one dictionary, you have to pick up one as the default. Then in the editor, new buttons for the alternative languages will be shown in the toolbar so you can select the text and flag it as a different language. The code in your content is gonna look like this: &lt;span lang="XXX"&gt;My text in another language&lt;/span&gt; where XXX is the code of the language as listed below. It might not be the easiest approach, regards accessibilities specifications you should already define the language in case you are using multiple languages on the same page!', 'markup-markdown' ); ?>
						</p>

						<h4>
							3) <em><?php esc_html_e( 'One specific dictionary per language', 'markup-markdown' ); ?></em>
						</h4>
						<p>
							<?php esc_html_e( 'Sounds obvious, multilingual means multiple languages on the same medium. With the current interface you could try activating two variants of the same parent language, for exemple American English and British English, that won\'t work of course!!! (Or they will be really odd side effects)', 'markup-markdown' ); ?>
						</p>

						<table class="form-table" role="presentation">
							<tbody>
<?php
	$dict_dir = $this->dict_dir;
	$dict_base_uri = str_replace( '/plugins/markup-markdown/', '/mmd-dict/', mmd()->plugin_uri );
	foreach( $this->dictionaries as $dict_id => $dictionary ) :
		$this->check_for_older_names( $dictionary[ 'file_name' ] );
		$curr_base_filename = $dict_dir . '/' . md5( $dictionary[ 'file_name' ] );
		echo "\n\t\t\t\t\t\t<tr>";
		echo "\n\t\t\t\t\t\t\t<th scope=\"row\" class=\"lang-code\">" . $dictionary[ 'code' ] . "</th>";
		echo "\n\t\t\t\t\t\t\t<th scope=\"row\">" . $dictionary[ 'label' ];
		if ( file_exists( $curr_base_filename . '.txt' ) ) :
			echo " (<a href=\"" . $dict_base_uri . md5( $dictionary[ 'file_name' ] ) . ".txt\" target=\"_blank\">Info</a>)";
		endif;
		echo "</th>";
		if ( file_exists( $curr_base_filename . '.dic' ) && file_exists( $curr_base_filename . '.aff' ) ) :
			echo "\n\t\t\t\t\t\t\t<td>";
			$isActive = isset( $my_cnf[ 'spellcheck' ] ) && is_array( $my_cnf[ 'spellcheck' ] ) && in_array( $dict_id, $my_cnf[ 'spellcheck' ] ) ? 1 : 0;
			echo "\n\t\t\t\t\t\t\t\t<label for=\"mmd_spell_check_" . $dict_id . "\">"
				. "\n\t\t\t\t\t\t\t\t\t<input type=\"checkbox\" name=\"mmd_spell_check[]\" id=\"mmd_spell_check_" . $dict_id . "\" value=\"" . $dict_id . "\"" . (  $isActive ? " checked=\"checked\"" : " " ) . "/>"
				. "\n\t\t\t\t\t\t\t\t\t" . ( $isActive ? __( 'Active', 'markup-markdown' ) : __( 'Active', 'Installed, check to activate', 'markup-markdown' ) );
			echo "\n\t\t\t\t\t\t\t\t</label>";
			echo "\n\t\t\t\t\t\t\t</td>";
			echo "\n\t\t\t\t\t\t\t<td>";
			if ( $isActive ) :
				echo "\n\t\t\t\t\t\t\t\t<label for=\"mmd_default_" . $dict_id . "\">";
				echo "\n\t\t\t\t\t\t\t\t\t<input type=\"radio\" name=\"mmd_default_spell_checker\" id=\"mmd_default_" . $dict_id . "\" value=\"" . $dict_id . "\"";
				if ( array_search( $dict_id, $my_cnf[ 'spellcheck' ] ) === 0 ) :
					echo " checked=\"checked\" /> " . __( 'Default', 'markup-markdown' );
				else :
					echo " /> " . __( 'Make default', 'markup-markdown' );
				endif;
				echo "\n\t\t\t\t\t\t\t\t</label>";
				if ( ! file_exists( $curr_base_filename . '_extra.dic' ) && isset( $this->extra[ $dict_id ] ) ) :
					# Trigger here the download othe extra dictionnary
					$this->install_extra( $this->extra[ $dict_id ][ 'file_name' ], $dictionary[ 'file_name' ], $dict_dir );
				endif;
			endif;
			echo "\n\t\t\t\t\t\t\t</td>";
		else :
			echo "\n\t\t\t\t\t\t\t<td colspan=\"2\">";
			echo "\n\t\t\t\t\t\t\t\t<a href=\"" . wp_nonce_url( menu_page_url( "markup-markdown-admin", false ), "spell_checker", "_mmd_sc_nonce" ) . "&dict=" . $dict_id . "#tab-spellchecker\">" . __( 'Install', 'markup-markdown' ) . "</a>";
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
