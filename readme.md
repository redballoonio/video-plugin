# RB Video Base

**Contributors:** redballoondesignltd, rajeshredballoon, markredballoon  
**Tags:** videos, youtube, modal, responsive, gallery  
**Requires at least:** 4.3  
**Tested up to:** 4.8  
**Stable tag:** 4.8  
**License:** GPLv2 or later  
**License URI:** http://www.gnu.org/licenses/gpl-2.0.html  

RB Video Base adds a **Video** Custom Post Type and a flexible shortcode system for embedding **YouTube** and **MP4** videos, with theme-overridable **archive** and **single** templates. Includes optional **Bunny CDN MP4 offload** integration.

---

## Description

RB Video Base is a plugin for managing and displaying videos on your website.

It provides:

- A **Videos** Custom Post Type (`video`)
- A **Video Attributes** admin metabox which stores the generated shortcode in:
  - `video_post` (post meta)
- A video archive at **/videos** using a plugin template (theme override supported)
- A plugin **single video** template (theme override supported)
- A **More videos** section template/partial (theme override supported)
- A **Video Card** template/partial (theme override supported)
- A **Settings page** under **Videos → Settings** (Video Index + Grid + Bunny CDN tabs)
- Optional **Bunny CDN MP4 Offload** (Edge Storage + Pull Zone)
- Classic Editor **Insert Video** button (TinyMCE) to insert a video shortcode into content

> Note: This plugin has been refactored into an OOP structure with separate include files for CPT, metabox, templates, settings, enqueue, and Bunny offload.

---

## How it works

### Video content storage (`video_post`)
Each Video post stores a shortcode string in the `video_post` meta key, e.g.:

- YouTube:  
  `[video youtube_id="dQw4w9WgXcQ" thumbnail="show"]`

- MP4:  
  `[video mp4="https://example.com/uploads/video.mp4" poster="https://example.com/uploads/poster.jpg"]`

Your templates and cards render the video by running the stored shortcode.

### Templates + theme overrides
The plugin provides templates but allows themes to override them.

**Archive template override paths:**
- `your-theme/rb-video-base/archive-video.php`
- `your-theme/archive-video.php`

**Single template override paths:**
- `your-theme/rb-video-base/single-video.php`
- `your-theme/single-video.php`

**Card partial override path:**
- `your-theme/rb-video-base/parts/card-video.php`

**More-videos partial override path (if used):**
- `your-theme/rb-video-base/parts/more-videos.php`

If no overrides exist, the plugin uses its own templates in:
- `templates/archive-video.php`
- `templates/single-video.php`
- `templates/parts/card-video.php`
- `templates/parts/more-videos.php`

### Archive grid (Bootstrap 5 friendly)
Archive output uses Bootstrap-style markup and supports configurable grid values (desktop/tablet/mobile columns and gap) via settings. The grid values are output as data attributes / CSS variables (depending on your implementation) so you can control layout without breaking pagination.

### Settings page (Videos → Settings)
The settings page includes:

**Video Index**
- Index Title (fallback: “Videos”)
- Index Description (WYSIWYG content)

**Grid Layout**
- Videos per page
- Columns per row: Desktop / Tablet / Mobile
- Grid gap

**Bunny CDN**
- Enable Bunny MP4 offload
- Storage Zone + Endpoint + AccessKey
- Pull Zone Base URL
- Base folder + keep WP subfolders + delete local option
- Connection summary + “Test connection” button (AJAX)

### Bunny CDN MP4 Offload (optional)
When enabled:
- MP4 uploads are uploaded to Bunny Edge Storage
- The attachment stores Bunny metadata (remote path + public URL)
- Attachment URLs are filtered to return the Bunny URL where available
- Deleting the attachment can optionally delete the remote file (best-effort)

---

### Shortcode examples 

Youtube video embed with a custom thumbnail and a title using a video custom post:

`[video id="21" title="show" thumbnail="show"][/video]`

Youtube video brought in by youtube_id displayed using a modal:

`[video youtube_id="SW3o7rSn-TY" type="modal"][/video]`

Video gallery using the thumbnail option:

`[video id="21, 22, 23" type="gallery" gallery_options="thumbnails"][/video]`

Video embed using a non-youtube video:

`[video iframe_url="https://player.vimeo.com/video/197202895"][/video]`

---

### Shortcode options 

#### One of these options must be set:

**Output a video from a custom post:**

`id='[video id]' (default: blank)`

If the video has been added as a video custom post type, add the post id of the video to add it here. The ID can be found in the url of the post in Wordpress. If you set the `type` to gallery then you can add multiple comma separated ids.


**Output a video from youtube**

`youtube_id="[youtube-video-id]" (default: blank)`

This is the id of the youtube video you want to link to. This is the 11 digit code at the end of a youtube video's url. https://www.youtube.com/watch?v= **SW3o7rSn-TY**


**Output a video from another location**

`iframe_url="[non-youtube-url]" (default:blank)`

If the video isn't a youtube video then a the url gets added into a responsive iframe.

#### These options may also be set:

**Dispay type**

`type="embed/modal/gallery" (default: "embed")`

Changes how the video is output on the site:
* "embed" outputs the video inline with the content and allows it to respond as the screen size changes.
* "modal" outputs the video thumbnail inline with the content. Clicking the thumbnail opens up a modal window ontop of the page where the video plays. Options for the modal can be set witht the `modal_options` attribute. This option doesn't work with videos brought in using the `iframe_url` attribute.
* "gallery" outputs the videos in a gallery. One of the videos appears like the embed option. The options for the gallery can be set using the `gallery_options` attribute. This option doesn't work with videos brought in using the `iframe_url` attribute.

**Show or hide title**

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

[See the full documentation here](https://docs.google.com/document/d/18Se4PmefkpOZ5T-g66nJqBglMcDRObhmUDvzVsVtQMY/edit)

*This plugin uses modal and gallery js taken from Bootstrap 3.*

---

## Installation 

1. Upload the plugin folder to `/wp-content/plugins/`
2. Activate **RB Video Base**
3. Go to **Videos → Add New** and add your video details
4. Optionally configure:
   - **Videos → Settings** (index title, description, grid layout)
   - **Bunny CDN** tab (MP4 offload)
5. Add the `[video]` shortcode where you want the video to be output
6. Use the shortcode options to customise how the video gets displayed


---

## Classic Editor: Insert Video button

RB Video Base includes a TinyMCE button (Classic Editor) that opens a dialog to insert a shortcode into the editor.  
The latest implementation was consolidated into a single admin script (`js/admin.js`) which includes:

- TinyMCE plugin registration (Insert Video button)
- Metabox UX helpers (media pickers / field toggles / URL parsing)

---

## Frequently Asked Questions

### Does this work with other video hosts?
The refactored version focuses on **YouTube** and **MP4**. If your shortcode renderer supports `iframe_url` (legacy), it can still be used, but modern usage is YouTube ID or MP4 URL.

### How do I override templates?
Copy the plugin templates into your theme under `rb-video-base/` and modify them there. Example:

- Copy `templates/archive-video.php` → `your-theme/rb-video-base/archive-video.php`

### Where do we get Bunny CDN details?
From Bunny dashboard:
- **Storage Zone Name**: Storage → select zone → zone name
- **Storage AccessKey** (zone password): Storage → zone → FTP & API Access / Password
- **Storage Endpoint**: Storage → zone → region endpoint (e.g. `uk.storage.bunnycdn.com`)
- **Pull Zone Base URL**: Pull Zones → open zone → copy `https://xxxx.b-cdn.net`

---

## Screenshots 

### 1. Example of the modal option 

![Example of the modal](https://github.com/redballoonio/video-plugin/blob/master/screenshots/modal.png "Example of the modal")

### 2. Example of the gallery option 

![Example of the gallery](https://github.com/redballoonio/video-plugin/blob/master/screenshots/gallery.png "Example of the gallery")

### 3. Example of the embed option. The height of the video will respond with the width of the element. 

![Example of the embed](https://github.com/redballoonio/video-plugin/blob/master/screenshots/embed.png "Example of the embed")



## Changelog 

### 1.3.0
- Major refactor to OOP architecture with modular includes (`includes/`).
- Added plugin templates for:
  - Video archive (`/videos`)
  - Single video template
  - Card template partial
  - More videos partial
  - Theme overrides supported for all templates/partials.
- Added Videos → Settings page:
  - Index title + description (WYSIWYG)
  - Pagination size (videos per page)
  - Grid layout controls (desktop/tablet/mobile columns + gap)
  - Settings UI improved with tabs.
- Added Bunny CDN MP4 Offload integration:
  - Bunny settings in Bunny tab
  - Connection summary and “Test connection” button
  - Attachment URL rewritten to Bunny URL when available
  - Optional deletion of remote file on attachment delete
- Admin improvements:
  - Consolidated admin JS into `js/admin.js`
  - Classic Editor “Insert Video” option to insert shortcode into content
  - Cleaner “Video Attributes” workflow using the generated `video_post` meta

### 1.2.1
* Updated the JavaScript to remove jQuery dependencies and ensure compatibility with Bootstrap 5.

### 1.2.0
* Public launch version.
* Updated the plugin to include modal and gallery code from bootstrap


### 1.1.0
* Updated the javascript to use the youtube api version 3
* Added the gallery option for videos


### 1.0.0
* Added video post type, Modals and controlling the videos using the youtube api


## Upgrade Notice 

### 1.3.0
Refactor + new templates/settings/Bunny offload. If you previously used legacy attributes (modal/gallery/iframe_url), confirm your current shortcode renderer still supports them before upgrading.