=== RB Video ===
Contributors: markredballoon
Tags: videos, youtube, modal, responsive
Requires at least: 4.4.2
Tested up to: 4.8
Stable tag: 4.8
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

A plugin for responsively displaying video iframes. The videos can be displayed inline or using a modal or a gallery.
Designed to work with youtube videos.

== Description ==
A plugin for managing and displaying videos on your website. Creates a "Videos" Custom post type allowing you to integrate youtube videos with Wordpress to output custom titles, descriptions and image thumbnails to display videos on your website. Alternatively you can also output any youtube video directly via shortcodes, optimised for responsive devices with a variety of output options to best suit your page.

Includes a number of embed methods:
* Embed: These can be within the content, 
* Modal: as a modal,
* Gallery:  multiple videos in a gallery

## Shortcodes

### One of these options must be set:

`id='[video id]' (default: blank)`
This is the id of the video page in Wordpress that this links to. If you set the type to gallery then you can add multiple comma separated ids.

`youtube_id="[youtube-video-id]" (default: blank)`
This is the id of the youtube video you want to link to. This is the 11 digit code at the end of a youtube video's url. You cannot have multiple youtube ids. 

`iframe_url="[non-youtube-url]" (default:blank)`
If the video isn't a youtube video then a the url gets added into a responsive iframe. This isn't used if id or youtube_id is used. This can only be used to provide a responsive wrap to the iframe.

### These options may also be set:

`title="show/hide" (default: "show")`
Whether to show or hide the video's title. This option is only used when the video is output using the id attribute.

`title_style="[css-class]" (default: blank)`
Css class that gets added to the title to apply multiple custom styles. Can be used for styling the title. "Overlayed" is built in, which makes the title absolutely positioned in the bottom left hand corner of the video.

`thumbnail="show/hide" (default: "show")`
Shows or hides the custom thumbnail. If the video is brought in using the youtube_id attribute, or the video post has no featured image, then the youtube default image will be used. No thumbnail image will be shown when using the iframe_url attribute.

`type="embed/modal/gallery" (default: "embed")`
Changes which type of video is brought in. Embed or modal can be used with or without a video page set up. Gallery requires the videos be added by the id attribute.

`gallery_options="" (default: empty)` 
Only used when the type is set to gallery. Adds different carousel control options: 
* "arrows" : adds arrows that sit ontop of the video.
* "thumbnails" : adds a row of thumnail images below that can be used to select a different video.
* "indicators" : indicator buttons underneath the video.


## Shortcode examples

Youtube video embed with a custom thumbnail and a title:

`[video id="21" title="show" thumbnail="show"]`

Youtube video brought in by youtube_id displayed using a modal

`[video youtube_id="SW3o7rSn-TY" type="modal"]`

Video gallery using the thumbnail option:

`[video id="21, 22, 23" type="gallery" gallery_options="thumbnails"]`

[See the full documentation here](https://docs.google.com/document/d/1fUWAj2Yi6I0uLRp8ZyK2DwVdmiFEc0sY5Kb-TTzi3G4/edit?usp=sharing)

_This plugin uses modal and gallery js taken from Bootstrap 3._

== Installation ==
1. Upload the `video-base` folder to the `/wp-content/plugins/` directory
1. Activate the plugin through the 'Plugins' menu in Wordpress
1. Add the `[video]` shortcode where you want the video to be output

== Frequently Asked Questions ==
= Does this work with other video hosts? =

This plugin is primarily designed to work with youtube videos, but it can work with any iframe content.

== Screenshots ==
1. Example of the modal option
2. Example of the gallery option
3. Example of the embed option. The height of the video will respond with the width of the element.

== Changelog ==

= 1.2 =
Public launch version.

== Upgrade Notice ==

No upgrades yet possible 