=== TxtBear ===
Contributors: eload24ag
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=10843013
Tags: pdf, embed, howto, viewer, documents, ebook
Requires at least: 2.8
Tested up to: 3.3
Stable tag: 1.1.2223.2113

The fastest way to share your documents.

== Description ==

= English =

Upload documents in PDF, DOC, DOCX, XLS and many other formats to your Wordpress site. Then make them super-accessible to your visitors with our embedded Lightbox Viewer.

= Deutsch =

Lade Dokumente als PDF, DOC, DOCX, XLS und vielen anderen Formaten in deine WordPress-Site hoch.  Dann können deine Besucher ganz einfach mittels unseres eingebetteten Lightbox-Betrachters auf sie zugreifen.

== Installation ==

= English =

1. Download the plugin and upload its folder to /wp-content/plugins/.
2. Activate the plugin from the admin interface.
3. Open a new or existing post or page, and click the ebook embed button in the media bar.

More help is available on the [TxtBear homepage](http://www.txtbear.com/).

= Deutsch =

1. Lade das Plugin herunter und dessen Ordner nach /wp-content/plugins/ hoch.
2. Aktiviere das Plugin in der Admin-Oberfläche.
3. Öffne einen neuen oder bestehenden Beitrag oder eine Seite, und klicke auf die Schaltfläche Dokument einbetten in der Medienleiste.

Weitere Hilfe ist auf der [Homepage von TxtBear](http://www.txtbear.com/) verfügbar.

== Frequently Asked Questions ==

= English =

= What options can I set for the document embed tag? =

* **viewer=12345**. _Required._  Viewer ID at txtbear.com (view.txtbear.com/12345).
* **mode=preview|link**.  Preview displays a thumbnail, link inserts a text link.
* **align=none|left|center|right**.  Align the thumbnail/link.
* **title="The Title"**.  Image alternate text or link text.  Don’t forget the quote signs.
* **eload24=123**.  Ebook ID at eload24.com (www.eload24.com/product/show/123).

= Deutsch =

= Welche Optionen kann ich beim Einbetten von Dokumenten angeben? =

* **txtbear=12345**.  _Erforderlich._  Anzeige-ID bei txtbear.com. (view.txtbear.com/12345).
* **mode=preview|link**.  Preview zeigt eine Vorschau, link fügt einen Textlink ein.
* **align=none|left|center|right**.  Die Vorschau/den Link ausrichten.
* **title="Der Titel"**.  Alternativtext der Vorschau oder Linktext. Die Anführungszeichen nicht vergessen.
* **eload24=123**.  Ebook-ID bei eload24.com. (www.eload24.com/product/show/123).

== Screenshots ==

1. Click the document embed button in your media bar. / Klicke auf die Schaltfläche Dokument einbetten in der Medienleiste.

== Changelog ==

= 1.1.2223.2113 =
* Updated: Version number.
* Fixed: Plugin should work regardless of its folder name.
* Updated: Indenting.
* Fixed: Country detection for eload24 button.
* Fixed: Live preview alignment.
* Fixed: Doc uploader Embed options form row length (text was cut).

= 1.1.11 =
* Fixed: "Browse" button not working sometimes.
  (Now embedding only certain scripts in iframe, causes problems with theme JS hooks otherwise.)
* Fixed: Thumbnail not showing sometimes.
  (Database thumbnail cache field length was too short for SEO image URLs.)

= 1.1.10 =
* Now depending on PHP curl, fixed oEmbed code.

= 1.1.9 =
* Tested support for WordPress 3.
* Minor appearance corrections.
* Added internal parameters.

= 1.1.8 =
* Minor changes.

= 1.1.7 =
* Fixed: Readme.txt UTF-8 encoding.

= 1.1.6 =
* Changed: Status API to realtime.
* Fixed: Display of TxtBear URLs that contain document title.

= 1.1.5 =
* Fixed: HTML validity. div in p changed to span in p

= 1.1.4 =
* Fixed: Problem with square brackets in post text, changed delimiter for title parameter to "..."

= 1.1.3 =
* Renamed: from Ebook Embed to TxtBear
* Added: Embedding your own documents
* Changed: Embed method

= 1.0.2 =
* Changed: Embed method
* Changed: Code clean-up

= 0.9.1 =
* First public release
