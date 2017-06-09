=== Video Base ===
Contributors: markredballoon
Tags: videos, youtube, modal, responsive
Requires at least: 4.4.6
Tested up to: 7.5.2
Stable tag: 7.5.2
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

A plugin for responsively displaying video iframes. The videos can be displayed inline or using a modal or a gallery.
Designed to work with youtube videos.

== Description ==
A plugin for responsivly displaying videos. These can be within the content, as a modal, or multiple videos in a gallery.

# Shortcodes

`id=\'[video id]\' (default: blank)`
This is the id of the video page in the back end that this links to. Enter this attribute or the youtube_id attribute.

`youtube_id=\"[youtube video id]\" (default: blank)`
This is the id of the youtube video you want to link to. it is the 11 digit code at the end of a youtube video\'s url. Enter this attribute or the id attribute.

`title=\"show/hide\" (default: \"show\")`
Whether to show or hide the video\'s title. If there isn\'t an id set then no title will be brought in.

`title_style=\"[css class]\" (default: blank)`
Css class that gets added to the title. Can be used for styling the title. \"Overlayed\" is the only one built in, which makes the title absolutely positioned in the bottom left hand corner of the video.

`thumbnail=\"show/hide\" (default: \"show\")`
Shows or hides the custom thumbnail. If there isn\'t an id set, or the video post has no featured image, then the youtube default image will be brought in.

`type=\"embed/modal\" (default: \"embed\")`
Changes which type of video is brought in. Either embed or modal. Both of these can be used with or without a video page set up.

`iframe_url=\"non-youtube url\" (default:blank)`
If the video isn\'t a youtube video then a the url gets added into a responsive iframe. This isn\'t used if id or youtube_id is used.


_This plugin uses modal and gallery js taken from Bootstrap 3._

== Installation ==
1. Upload the `video-base` folder to the `/wp-content/plugins/` directory
1. Activate the plugin through the \'Plugins\' menu in WordPress
1. Add the `[video]` shortcode where you want the video to be output.

== Frequently Asked Questions ==
= Does this work with other video hosts? =

This plugin is primarily designed to work with youtube videos, but it can work with any iframe content.

== Screenshots ==
1. Example of the modal option
2. Example of the gallery option

== Changelog ==
= 0.1 =
Initial launch version.

== Upgrade Notice ==
 