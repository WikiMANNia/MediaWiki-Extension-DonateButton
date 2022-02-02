# MediaWiki-Extension-DonateButton
Mediawiki extension for adding a Donation Button into the sidebar

## Configuration options

Enable the DonateButton. Default is false.

* $wgDonateButton = true;

Specify the link to a donation page.

* $wgDonateButtonURL = "yourdomain/yourdonationpage.php?lang=";

The link is automatically completed by the code of the language selected by the user or alternatively by the $wgLanguageCode variable.

## Localization

The extension is localized for the languages "de", "en", "es", "fr", "it", "nl", "pt", and "ru".

## Support

Currently, this extension supports the skins Cologne Blue, Modern, MonoBook and Vector.
Further skins may require additional adjustments, which would have to be made in "resources/css/myskin.css".

## Compatibility
This extension works from REL1_25 and has been tested up to MediaWiki version 1.36.3.

In REL1_37, the [SkinBuildSidebar](https://www.mediawiki.org/wiki/Manual:Hooks/SkinBuildSidebar) hook no longer allows images and HTML code to be placed in the sidebar.
A solution for this circumstance is not yet known.
