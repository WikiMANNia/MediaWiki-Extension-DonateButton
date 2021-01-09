<?php

use MediaWiki\MediaWikiServices;

class DonateButtonHooks extends Hooks {

	/**
	 * Hook: BeforePageDisplay
	 * @param OutputPage $out
	 * @param Skin $skin
	 * https://www.mediawiki.org/wiki/Manual:Hooks/BeforePageDisplay
	 */
	public static function onBeforePageDisplay( OutputPage &$out, Skin &$skin ) {

		if ( !self::isActive() )  return;

		$skinname = $skin->getSkinName();
		$out->addModuleStyles( 'ext.donatebutton.common' );
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

		global $wgDonateButton, $wgDonateButtonFilename, $wgDonateButtonURL;
		global $wgLanguageCode, $wgVersion;

		if ( !empty( $wgDonateButton ) &&
			!empty( $wgDonateButtonFilename ) &&
			!empty( $wgDonateButtonURL ) &&
			( ( $wgDonateButton === 'true' ) || ( $wgDonateButton === true ) )
			) {

			$langCode = ( strlen( $skin->msg( 'lang' )->text() ) === 2 ) ? $skin->msg( 'lang' )->text() : $wgLanguageCode;

			// If the passed URL ends with a '=', append the language abbreviation to make the donation page language sensitive.
			// Wenn die übergebene URL mit einem '=' endet, das Sprachenkürzel anhängen, um die Spendenseite sprachsensitiv zu behandeln.
			if ( substr( $wgDonateButtonURL, ( strlen( $wgDonateButtonURL ) - 1 ), 1 ) === '=' ) {
				$wgDonateButtonURL .= $langCode;
			}

			// Sucht den Dateipfad zum Botton-Bild im hiesigen Wiki
			$fileTitle = Title::makeTitleSafe( NS_FILE, $wgDonateButtonFilename );
			$fileObject = version_compare( $wgVersion, '1.35', '<' ) ? wfFindFile( $fileTitle ) : MediaWikiServices::getInstance()->getRepoGroup()->findFile( $fileTitle );
			$fileURL = ( $fileObject !== false )
					? $fileObject->getFullUrl()
					: self::paypalURL( $wgLanguageCode );

			// Ändert den Dateipfad zum Botton-Bild gemäß der Sprachauswahl
			if ( strcmp( $langCode, $wgLanguageCode ) !== 0 ) {
				$fileURL = self::paypalURL( $langCode );
			}

			$html = '<a href="//' . $wgDonateButtonURL .
					'"><img alt="Donate-Button" title="' .
					$skin->msg( 'donatebutton-msg' )->text() .
					'"src="' . $fileURL .
					'" /></a>';

			switch ( $skin->getSkinName() ) {
				case 'cologneblue' :
					$html = Html::rawElement( 'div', [ 'class' => 'body' ], $html );
				break;
				case 'modern' :
				break;
				case 'monobook' :
				break;
				case 'vector' :
				break;
			}

			$bar['donatebutton'] = $html;
		}
	}

	private static function paypalURL( $lang ) {
		if ( strcmp( $lang, 'en' ) === 0 ) {
			return 'https://www.paypalobjects.com/en_GB/i/btn/btn_donate_LG.gif';
		}
		return 'https://www.paypalobjects.com/' . $lang . '_' . strtoupper( $lang ) . '/i/btn/btn_donate_LG.gif';
	}


	private static function isActive() {
		global $wgDonateButton;

		return ( isset( $wgDonateButton ) && ( $wgDonateButton === true ) );
	}

	private static function isSupported( $skinname ) {
		return in_array( $skinname, [ 'cologneblue', 'modern', 'monobook', 'vector' ] );
	}
}
