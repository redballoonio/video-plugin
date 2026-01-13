# RB Video Base — Readme

RB Video Base adds a **Video** custom post type to WordPress and provides a simple way to embed **YouTube** or **MP4** videos using a single shortcode stored against each video post.

It also includes:
- A video archive (`/videos`) and single video templates (theme-overridable)
- Grid controls for the archive layout
- Optional **Bunny CDN MP4 offload** (Edge Storage + Pull Zone)
- A Classic Editor button + admin UI helpers (metabox + media pickers)

---

## What this plugin does

### 1) Video Custom Post Type
Creates a `video` post type with:
- Title
- Editor content (used as description)
- Featured image support (optional)
- Archive at: **/videos**

### 2) Video Meta (video_post)
Each video post stores the final shortcode in the post meta key:

- `video_post`

This meta is used by:
- the archive card output (preview/embed)
- the single video template (embed above content)

### 3) Shortcode output
The shortcode renders either:
- YouTube iframe (using a YouTube ID)
- MP4 video tag (using a file URL)

YouTube defaults:
- Thumbnail is **shown by default** unless explicitly hidden

MP4 defaults:
- Poster only used if supplied (or if your shortcode logic fetches it)

### 4) Templates (theme override supported)
The plugin provides templates and looks for theme overrides first.

**Archive template override paths:**
- `your-theme/rb-video-base/archive-video.php`
- `your-theme/archive-video.php`

**Single template override paths:**
- `your-theme/rb-video-base/single-video.php`
- `your-theme/single-video.php`

**Card partial override path:**
- `your-theme/rb-video-base/parts/card-video.php`

If no override exists, the plugin uses its own templates in:
- `templates/archive-video.php`
- `templates/single-video.php`
- `templates/parts/card-video.php`

Templates use Bootstrap 5-friendly markup (grid/classes) and can be styled from theme or plugin CSS.

### 5) Settings page (Videos → Settings)
Settings include:
- Video index title + intro description (WYSIWYG)
- Videos per page
- Grid columns (desktop/tablet/mobile) + gap
- Bunny CDN offload settings (optional)

### 6) Bunny CDN MP4 Offload (optional)
When enabled, MP4 uploads can be offloaded to Bunny Edge Storage and served via a Pull Zone URL.
The Media Library URL filter will return the Bunny URL when available.

---

## How it works

### Video archive (`/videos`)
The archive uses WP’s main query and pagination.
Posts per page and grid layout are controlled via the plugin settings.

### Video card output
Each card reads:
- `video_post` meta for the embed/thumbnail output
- the post title
- editor content (if present)
- link to the single video page

### Single video output
On single `video` posts, the template will:
1. Render the `video_post` shortcode (if present)
2. Render the editor content underneath

---

## Files / Structure (current)

- `rb-video-base.php` (main bootstrap / OOP loader)
- `/includes/`
  - `CustomPostType.php` (registers the Video CPT)
  - `Metabox.php` (Video Attributes metabox + saving)
  - `Enqueue.php` (frontend + admin assets registration/enqueue)
  - `Templates.php` (archive + single template routing)
  - `Settings.php` (Videos → Settings page)
  - `BunnyOffload.php` (optional MP4 offload)
- `/templates/`
  - `archive-video.php`
  - `single-video.php`
  - `/parts/`
    - `card-video.php`
    - `more-videos.php` (optional section if used)
- `/js/`
  - `video-base.js` (frontend)
  - `admin.js` (TinyMCE button + metabox + settings UI)
- `/css/`
  - `video-base.min.css` (frontend styles)

---

## Requirements / Notes

- If you use Bootstrap modals or Bootstrap grid styling, ensure Bootstrap 5 is loaded by the theme.
- Bunny offload requires:
  - A Bunny Storage Zone (Edge Storage)
  - Storage endpoint + zone password (AccessKey)
  - A Pull Zone Base URL (recommended for public delivery)

---

## Support / Overrides

To override templates safely:
1. Copy the template file from `/templates/` into your theme under:
   - `rb-video-base/`
2. Modify it there.

Example:
- Copy `templates/archive-video.php` → `your-theme/rb-video-base/archive-video.php`

---

## Common Troubleshooting

- **Video not showing**: confirm `video_post` meta exists and contains a valid shortcode.
- **Pagination not working**: ensure archive template uses the main query (not a custom WP_Query without correct `paged` handling).
- **Bunny URL not appearing**: confirm offload is enabled and the attachment has Bunny meta stored.
