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
This extension works from REL1_25 and has been tested up to MediaWiki version 1.39.0-rc.1.

The [SkinBuildSidebar](https://www.mediawiki.org/wiki/Manual:Hooks/SkinBuildSidebar) hook of several skins no longer allows images and HTML code to be placed in the sidebar.

A solution for this circumstance is not yet known.
As a minimal solution, a simple text link to the donation page is now given.
This occurs in Skin Vector since REL1_35 and Skins Cologne Blue, Modern and MonoBook since REL1_37. Skin Timeless still works as usual.
