<?php

class DonateButtonHooks extends Hooks {

	/**
	 * Hook: BeforePageDisplay
	 * @param OutputPage $out
	 * @param Skin $skin
	 * https://www.mediawiki.org/wiki/Manual:Hooks/BeforePageDisplay
	 */
	public static function onBeforePageDisplay( OutputPage &$out, Skin &$skin ) {

		global $wgVersion;

		if ( !self::isActive() )  return;
		if ( version_compare( $wgVersion, '1.37', '>=' ) )  return;

		$skinname = $skin->getSkinName();
		$out->addModuleStyles( 'ext.donatebutton.common' );
		$out->addModuleStyles( 'ext.donatebutton.mobile' );
		if ( self::isSupported( $skinname ) ) {
			$out->addModuleStyles( 'ext.donatebutton.' . $skinname );
		} else {
			wfLogWarning( 'Skin ' . $skinname . ' not supported by DonateButton.' . "\n" );
		}
	}

	/**
	 * Hook: SkinBuildSidebar
	 * @param Skin $skin
	 * @param array $bar
	 * https://www.mediawiki.org/wiki/Manual:Hooks/SkinBuildSidebar
	 */
	public static function onSkinBuildSidebar(
		Skin $skin,
		array &$bar
	) {

		if ( !self::isActive() )  return;

		global $wgDonateButton, $wgDonateButtonEnabledPaypal;
		global $wgVersion;

		// 1. get tool tip message
		$title_text = $skin->msg( 'donatebutton-msg' )->text();

		// 2. get lang_code
		$lang_code = $skin->getLanguage()->getCode();
		if ( !self::isAvailable( $lang_code ) ) {
#			$title_text = "Image for lang '$lang_code' not supported!";
			$lang_code = 'en';
		}

		// 3. get URL of image
		$config = ConfigFactory::getDefaultInstance()->makeConfig( 'main' );
		$url_file = $config->get( 'ExtensionAssetsPath' ) . '/DonateButton/resources/images/' . $lang_code . '/Donate_Button.gif';

		// 4. get URL of donation page
		$url_site = $wgDonateButtonEnabledPaypal ? self::getPaypalUrl( $lang_code ) : self::getYourUrl( $lang_code );

		// 5. get HTML-Snippet
		$sidebar_element = self::getHtmlSnippet( $skin, $title_text, $url_site, $url_file );

		if ( version_compare( $wgVersion, '1.37', '>=' ) ) {
			$mylink = [
				'text'   => $skin->msg( 'donatebutton-donation' )->text(),
				'href'   => $url_site,
				'id'     => 'n-donatebutton',
				'active' => ''
			];
			$sidebar_element = [ $mylink ];
		}

		$bar['donatebutton'] = $sidebar_element;
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
		global $wgDonateButtonURL;
		$url = $wgDonateButtonURL;

		// If the passed URL ends with a '=', append the language abbreviation to make the donation page language sensitive.
		// i.e. your URL is "https://yourdomain.org/donationpage.php?lang="
		// Wenn die übergebene URL mit einem '=' endet, das Sprachenkürzel anhängen, um die Spendenseite sprachsensitiv zu behandeln.
		if ( substr( $wgDonateButtonURL, ( strlen( $wgDonateButtonURL ) - 1 ), 1 ) === '=' ) {
			$url .= $lang;
		}
		return $url;
	}

	/**
	 * Returns HTML-Snippet
	 */
	private static function getHtmlSnippet( $skin, $title, $url_site, $url_image ) {
		$html_pattern = '<a href="//%1$s"><img alt="%2$s" title="%3$s" src="%4$s" /></a>';
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
			case 'minerva' :
			break;
			case 'modern' :
			break;
			case 'monobook' :
			break;
			case 'vector' :
			break;
		}
		return $html_code;
	}

	/**
	 * Returns true if extension is set to active
	 */
	private static function isActive() {
		global $wgDonateButton;

		return ( isset( $wgDonateButton ) && ( ( $wgDonateButton === true ) || ( $wgDonateButton === 'true' ) ) );
	}

	/**
	 * Returns true if button image file is available
	 */
	private static function isAvailable( $lang ) {
		global $wgDonateButtonLangArray;

		$langs = explode( ', ', $wgDonateButtonLangArray );
		$lang_array = [];
		foreach ( $langs as $_lang ) {
			$lang_array[] = $_lang;
		}

		return in_array( $lang, $lang_array );
	}

	/**
	 * Returns true if skin is supported
	 */
	private static function isSupported( $skinname ) {
		return in_array( $skinname, [ 'cologneblue', 'minerva', 'modern', 'monobook', 'vector' ] );
	}
}
