<?php

class DonateButtonHooks extends Hooks {

	private static $instance;

	private $button_active;
	private $paypal_active;
	private $lang_array;
	private $url_site;

	/**
	 * @param GlobalVarConfig $config
	 */
	public function __construct() {

		global $wgDonateButton, $wgDonateButtonEnabledPaypal;
		global $wgDonateButtonURL, $wgDonateButtonLangs;

		$this->button_active = ( isset( $wgDonateButton ) && ( $wgDonateButton === true ) );
		$this->paypal_active = ( isset( $wgDonateButtonEnabledPaypal ) && ( $wgDonateButtonEnabledPaypal === true ) );
		$this->url_site = $wgDonateButtonURL;

		$this->lang_array = [];
		if ( empty( $wgDonateButtonLangs ) ) {
			$this->lang_array[] = 'en';
		} else {
			$langs = explode( ', ', $wgDonateButtonLangs );
			foreach ( $langs as $_lang ) {
				$this->lang_array[] = $_lang;
			}
		}
	}

	private function __clone() { }

	/**
	 * @return self
	 */
	public static function getInstance() {
		if ( self::$instance === null ) {
			// Erstelle eine neue Instanz, falls noch keine vorhanden ist.
			self::$instance = new self();
		}

		// Liefere immer die selbe Instanz.
		return self::$instance;
	}

	/**
	 * https://www.mediawiki.org/wiki/Manual:Hooks/BeforePageDisplay
	 *
	 * @param OutputPage $out
	 * @param Skin $skin
	 */
	public static function onBeforePageDisplay( OutputPage &$out, Skin &$skin ) {

		if ( !self::isActive() )  return;

		$skinname = $skin->getSkinName();
		switch ( $skinname ) {
			case 'cologneblue' :
			case 'modern' :
			case 'monaco' :
			case 'monobook' :
			case 'timeless' :
				$out->addModuleStyles( 'ext.donatebutton.common' );
				$out->addModuleStyles( 'ext.donatebutton.' . $skinname );
			break;
			case 'vector' :
			case 'vector-2022' :
				$out->addModuleStyles( 'ext.donatebutton.common' );
				$out->addModuleStyles( 'ext.donatebutton.vector' );
			break;
			case 'minerva' :
			case 'fallback' :
			break;
			default :
				wfLogWarning( 'Skin ' . $skinname . ' not supported by DonateButton.' . "\n" );
			break;
		}
	}

	/**
	 * https://www.mediawiki.org/wiki/Manual:Hooks/SkinBuildSidebar
	 *
	 * @param Skin $skin
	 * @param array $bar
	 */
	public static function onSkinBuildSidebar(
		Skin $skin,
		array &$bar
	) {

		if ( !self::isActive() )  return;

		// 1. get tool tip message
		$title_text = $skin->msg( 'donatebutton-msg' )->text();

		// 2. get lang_code
		$lang_code = $skin->getLanguage()->getCode();
		if ( !self::isAvailable( $lang_code ) ) {
			switch ( $lang_code ) {
				case 'de-formal' :
				case 'de-at' :
				case 'de-ch' :
				case 'de-formal' :
					$lang_code = 'de';
				break;
				default :
					$lang_code = 'en';
				break;
			}
		}

		// 3. get URL of image
		$config = ConfigFactory::getDefaultInstance()->makeConfig( 'main' );
		$url_file = $config->get( 'ExtensionAssetsPath' ) . '/DonateButton/resources/images/' . $lang_code . '/Donate_Button.gif';

		// 4. get URL of donation page
		$url_site = self::getInstance()->paypal_active ? self::getPaypalUrl( $lang_code ) : self::getYourUrl( $lang_code );

		// 5. get HTML-Snippet
		$img_element = self::getHtmlSnippet( $skin, $title_text, $url_site, $url_file );

		$bar['donatebutton'] = $img_element;
	}

	/**
	 * Load donate box for Monaco skin.
	 *
	 * @return bool
	 */
	public static function onMonacoStaticboxEnd( $skin, &$html ) {

		if ( !self::isActive() )  return;

		$skin = RequestContext::getMain()->getSkin();

		// 1. get tool tip message
		$title_text = wfMessage( 'donatebutton-msg' )->text();
		$title_key = wfMessage( 'donatebutton' )->text();

		// 2. get lang_code
		$lang_code = $skin->getLanguage()->getCode();
		if ( !self::isAvailable( $lang_code ) ) {
			switch ( $lang_code ) {
				case 'de-formal' :
				case 'de-at' :
				case 'de-ch' :
				case 'de-formal' :
					$lang_code = 'de';
				break;
				default :
					$lang_code = 'en';
				break;
			}
		}

		// 3. get URL of image
		$config = ConfigFactory::getDefaultInstance()->makeConfig( 'main' );
		$url_file = $config->get( 'ExtensionAssetsPath' ) . '/DonateButton/resources/images/' . $lang_code . '/Donate_Button.gif';

		// 4. get URL of donation page
		$url_site = self::getInstance()->paypal_active ? self::getPaypalUrl( $lang_code ) : self::getYourUrl( $lang_code );

		// 5. get HTML-Snippet
		$img_element = self::getHtmlSnippet( $skin, $title_text, $url_site, $url_file );

		$html .= "<p style='margin-top:0.5em;'>$title_key</p>";
		$html .= "<div style='text-align:center;'>$img_element</div>";

		return true;
	}

	/**
	 * Returns Paypal's url sensitive to $lang
	 */
	private static function getPaypalUrl( $lang ) {

		if ( strcmp( $lang, 'en' ) === 0 ) {
			return 'https://www.paypalobjects.com/en_GB/i/btn/btn_donate_LG.gif';
		}
		return 'https://www.paypalobjects.com/' . $lang . '_' . strtoupper( $lang ) . '/i/btn/btn_donate_LG.gif';
	}

	/**
	 * Returns your url sensitive to $lang
	 */
	private static function getYourUrl( $lang ) {

		$url = self::getInstance()->url_site;

		// If the passed URL ends with a '=', append the language abbreviation to make the donation page language sensitive.
		// i.e. your URL is "https://yourdomain.org/donationpage.php?lang="
		// Wenn die übergebene URL mit einem '=' endet, das Sprachenkürzel anhängen, um die Spendenseite sprachsensitiv zu behandeln.
		if ( substr( $url, ( strlen( $url ) - 1 ), 1 ) === '=' ) {
			$url .= $lang;
		}
		return $url;
	}

	/**
	 * Returns HTML-Snippet
	 */
	private static function getHtmlSnippet( $skin, $title, $url_site, $url_image ) {
		$html_pattern = '<a href="%1$s"><img alt="%2$s" title="%3$s" src="%4$s" /></a>';
		$html_code = sprintf( $html_pattern,
						$url_site,
						'Donate-Button',
						$title,
						$url_image
					);

		switch ( $skin->getSkinName() ) {
			case 'cologneblue' :
				$html_code = Html::rawElement( 'div', [ 'class' => 'body' ], $html_code );
			break;
			default :
			break;
		}
		return $html_code;
	}

	/**
	 * Returns true if extension is set to active
	 */
	private static function isActive() {

		return self::getInstance()->button_active;
	}

	/**
	 * Returns true if button image file is available
	 */
	private static function isAvailable( $lang ) {

		return in_array( $lang, self::getInstance()->lang_array );
	}

	/**
	 * Returns true if skin is supported
	 */
	private static function isSupported( $skinname ) {
		return in_array( $skinname, [ 'cologneblue', 'minerva', 'modern', 'monaco', 'monobook', 'timeless', 'vector', 'vector-2022' ] );
	}
}
