<?php
/**
 * Hooks for DonateButton extension
 *
 * @file
 * @ingroup Extensions
 */

use MediaWiki\Hook\BeforePageDisplayHook;
use MediaWiki\Skins\Hook\SkinAfterPortletHook;
use MediaWiki\Hook\SkinBuildSidebarHook;
use MediaWiki\MediaWikiServices;

/**
 * @phpcs:disable MediaWiki.NamingConventions.LowerCamelFunctionsName.FunctionName
 */
class DonateButtonHooks implements
	BeforePageDisplayHook,
	SkinAfterPortletHook,
	SkinBuildSidebarHook
{

	/**
	 * https://www.mediawiki.org/wiki/Manual:Hooks/BeforePageDisplay
	 *
	 * @param OutputPage $out
	 * @param Skin $skin
	 * @return void This hook must not abort, it must return no value
	 */
	public function onBeforePageDisplay( $out, $skin ) : void {

		if ( !self::isActive() )  return;

		$skinname = $skin->getSkinName();
		switch ( $skinname ) {
			case 'cologneblue' :
			case 'modern' :
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
	 * This hook is called when generating portlets.
	 * It allows injecting custom HTML after the portlet.
	 *
	 * @param Skin $skin
	 * @param string $portletName
	 * @param string &$html
	 * @return bool|void True or no return value to continue or false to abort
	 */
	public function onSkinAfterPortlet( $skin, $portletName, &$html ) {

		if ( !self::isActive() )  return;

		global $wmDonateButton, $wmDonateButtonEnabledPaypal;

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
		$config = MediaWikiServices::getInstance()->getConfigFactory()->makeConfig( 'main' );
		$url_file = $config->get( 'ExtensionAssetsPath' ) . '/DonateButton/resources/images/' . $lang_code . '/Donate_Button.gif';

		// 4. get URL of donation page
		$url_site = $wmDonateButtonEnabledPaypal ? self::getPaypalUrl( $lang_code ) : self::getYourUrl( $lang_code );

		// 5. get HTML-Snippet
		$img_element['donatebutton'] = self::getHtmlSnippet( $skin, $title_text, $url_site, $url_file );

		switch ( $skin->getSkinName() ) {
			case 'cologneblue' :
			case 'modern' :
			case 'monobook' :
			case 'timeless' :
			case 'vector' :
			case 'vector-2022' :
				if ( array_key_exists( $portletName, $img_element ) ) {
					$element = $img_element[$portletName];
					if ( !empty( $element ) ) {
						$html = $element;
						return true;
					}
				}
			break;
		}
	}

	/**
	 * https://www.mediawiki.org/wiki/Manual:Hooks/SkinBuildSidebar
	 *
	 * @param Skin $skin
	 * @param array &$bar Sidebar contents. Modify $bar to add or modify sidebar portlets.
	 * @return bool|void True or no return value to continue or false to abort
	 */
	public function onSkinBuildSidebar( $skin, &$bar ) {

		if ( !self::isActive() )  return;

		global $wmDonateButton, $wmDonateButtonEnabledPaypal;

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

		// 3. get URL of donation page
		$url_site = $wmDonateButtonEnabledPaypal ? self::getPaypalUrl( $lang_code ) : self::getYourUrl( $lang_code );

		// 4. get TEXT-Snippet
		$txt_item = [
			'text'   => $skin->msg( 'sitesupport' )->text(),
			'href'   => $url_site,
			'id'     => 'n-donatebutton',
			'active' => true
		];
		$empty_item = [
			'text'   => '',
			'id'     => 'n-donatebutton',
			'active' => true
		];

		$sidebar_element = [];

		switch ( $skin->getSkinName() ) {
			case 'cologneblue' :
			case 'modern' :
			case 'monobook' :
			case 'vector' :
			case 'vector-2022' :
			break;
			case 'timeless' :
				// Dirty hack for skin Timeless
				$sidebar_element = [ $empty_item ];
			break;
			default :
				$sidebar_element = [ $txt_item ];
			break;
		}

		$bar['donatebutton'] = $sidebar_element;

	}

	/*
	 * https://www.mediawiki.org/wiki/Skin:Minerva_Neue/Hooks/MobileMenu
	 */
	public function onMobileMenu( $name, \MediaWiki\Minerva\Menu\Group &$group ) {

		if ( !self::isActive() )  return;

		global $wmDonateButton, $wmDonateButtonEnabledPaypal;

		// 1. get link text
		$link_txt = $template->msg( 'donatebutton-donation' )->text();

		// 2. get lang_code
		$lang_code = $template->getLanguage()->getCode();
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

		// 3. get URL of donation page
		$link_url = $wmDonateButtonEnabledPaypal ? self::getPaypalUrl( $lang_code ) : self::getYourUrl( $lang_code );

		if ( $name === 'discovery' ) {
				$group->insert( 'donation' )
				->addComponent(
						'donatebutton-donation',
						$link_url
				);
		}
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
		global $wmDonateButtonURL;
		$url = $wmDonateButtonURL;

		// If the passed URL ends with a '=', append the language abbreviation to make the donation page language sensitive.
		// i.e. your URL is "https://yourdomain.org/donationpage.php?lang="
		// Wenn die übergebene URL mit einem '=' endet, das Sprachenkürzel anhängen, um die Spendenseite sprachsensitiv zu behandeln.
		if ( substr( $wmDonateButtonURL, ( strlen( $wmDonateButtonURL ) - 1 ), 1 ) === '=' ) {
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
		global $wmDonateButton;

		return ( isset( $wmDonateButton ) && ( ( $wmDonateButton === true ) || ( $wmDonateButton === 'true' ) ) );
	}

	/**
	 * Returns true if button image file is available
	 */
	private static function isAvailable( $lang ) {
		global $wmDonateButtonLangArray;

		$langs = explode( ', ', $wmDonateButtonLangArray );
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
		return in_array( $skinname, [ 'cologneblue', 'minerva', 'modern', 'monobook', 'timeless', 'vector', 'vector-2022' ] );
	}
}
