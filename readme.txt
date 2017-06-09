=== RB Responsive Video Output ===
Contributors: markredballoon, redballoondesignltd
Tags: videos, youtube, modal, responsive, gallery
Requires at least: 4.3
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

= Shortcode examples =

Youtube video embed with a custom thumbnail and a title using a video custom post:

`[video id="21" title="show" thumbnail="show"][/video]`

Youtube video brought in by youtube_id displayed using a modal:

`[video youtube_id="SW3o7rSn-TY" type="modal"][/video]`

Video gallery using the thumbnail option:

`[video id="21, 22, 23" type="gallery" gallery_options="thumbnails"][/video]`

Video embed using a non-youtube video:

`[video iframe_url="https://player.vimeo.com/video/197202895"][/video]`


= Shortcode options =

**One of these options must be set:**

**Output a video from a custom post**

`id='[video id]' (default: blank)`

If the video has been added as a video custom post type, add the post id of the video to add it here. The ID can be found in the url of the post in Wordpress. If you set the `type` to gallery then you can add multiple comma separated ids.


**Output a video from youtube**

`youtube_id="[youtube-video-id]" (default: blank)`

This is the id of the youtube video you want to link to. This is the 11 digit code at the end of a youtube video's url. https://www.youtube.com/watch?v= **SW3o7rSn-TY**


**Output a video from another location**

`iframe_url="[non-youtube-url]" (default:blank)`

If the video isn't a youtube video then a the url gets added into a responsive iframe.

**These options may also be set:**

**Dispay type**

`type="embed/modal/gallery" (default: "embed")`

Changes how the video is output on the site:
* "embed" outputs the video inline with the content and allows it to respond as the screen size changes.
* "modal" outputs the video thumbnail inline with the content. Clicking the thumbnail opens up a modal window ontop of the page where the video plays. Options for the modal can be set witht the `modal_options` attribute. This option doesn't work with videos brought in using the `iframe_url` attribute.
* "gallery" outputs the videos in a gallery. One of the videos appears like the embed option. The options for the gallery can be set using the `gallery_options` attribute. This option doesn't work with videos brought in using the `iframe_url` attribute.

**Show or hide the title**

`title="show/hide" (default: "show")`

Whether to show or hide the video's title (only works with videos added as a custom post).

**Add custom styles to the title**

`title_style="[css-class]" (default: blank)`

css class that gets added to the title to apply multiple custom styles. Can be used for styling the title. "Overlaid" is built in, which makes the title absolutely positioned in the bottom left hand corner of the video.


**Show or hide the thumbnail**

`thumbnail="show/hide" (default: "show")`

Shows or hides the custom thumbnail. If the video is brought in using the `youtube_id` attribute, or the video post has no featured image, then the youtube default image will be used. No thumbnail image will be shown when using the `iframe_url` attribute.

**Display options for Modals**

`modal_options="[options]" (default: empty)`

Only used when the type is set to modal. Adds buttons types that close the modal. Multiple options can be selected, add them as a comma separated list.
* "cross" : adds a cross in the top right of the modal.
* "button" : adds a button beneath the modal to close it.

Clicking the background will always close the modal.

**Display options for Galleries**

`gallery_options="[options]" (default: empty)` 

Only used when the type is set to gallery. Adds different carousel control options. Multiple options can be selected, add them as a comma separated list.
* "arrows" : adds arrows that sit on top of the video.
* "thumbnails" : adds a row of thumbnail images below that can be used to select a different video.
* "indicators" : indicator buttons underneath the video.

[See the full documentation here](https://docs.google.com/document/d/1fUWAj2Yi6I0uLRp8ZyK2DwVdmiFEc0sY5Kb-TTzi3G4/edit?usp=sharing)

*This plugin uses modal and gallery js taken from Bootstrap 3.*

== Installation ==
1. Download the zip file from the github page
1. Upload the unzipped folder to the `/wp-content/plugins/` directory of your wordpress site
1. Activate the plugin through the 'Plugins' menu in Wordpress
1. Add the `[video]` shortcode where you want the video to be output
1. Use the shortcode options to customise how the video gets displayed

== Frequently Asked Questions ==
= Does this work with other video hosts? =

This plugin is primarily designed to work with youtube videos, but it can work with any iframe content.

== Screenshots ==
1. Example of the modal option
2. Example of the gallery option
3. Example of the embed option. The height of the video will respond with the width of the element.

== Changelog ==

= 1.2 =
* Public launch version.
* Updated the plugin to include modal and gallery code from bootstrap

= 1.1 =
* Updated the javascript to use the youtube api version 3
* Added the gallery option for videos

= 1.0 =
* Added video post type, Modals and controlling the videos using the youtube api

== Upgrade Notice ==

No upgrades yet possible 