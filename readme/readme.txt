# Readme for the video base Plugin

This plugin creates responsive wraps for youtube videos. These videos can be set to display in a modal or embedded into the document. This plugin also allows for custom descriptions, titles and thumbnails to customize the look of the video on the page.

## How it works:
The shortcode can either link to a video page in wordpress which contains a youtube video ID as a custom field or just use a youtube video id in the shortcode. This youtube video id is then used to create an iframe linking to the video. The video is is used so options in the embed url can be changed, such as allowing javascript to interact with the video.
The video id is also used to query the youtube oembed API to get the base height and width of the video. Using this the exact aspect ratio of the video is calculated and using The Padding Trick (http://www.fredparke.com/blog/css-padding-trick-responsive-intrinsic-ratios) the video remains a constant aspect ratio.

The thumbnail for the video can be set for pages in wordpress or by default the default HD video thumbnail is brought in from the youtube website.
The title and description are both set in the back end.

If the video isn't on youtube then the iframe url can be added as an attribute in the shortcode. This will not allow for custom thumbnails, icons or descriptions but will put the video in a responsive 16:9 aspect ratio wrap.

### Modal
The youtube video can be brought onto the page inside a bootstrap modal.
The thumbnail is brought into the main content of the page and all video modals are brought in at the end of the html document (just above where the scripts are brought in).
When opening the modal the video is played automatically. When the modal is closed the video is stopped (this prevents it from continuing to load if it has been closed).


## Files:

inc/shortcode.php is where the function the shortcode runs is located.
inc/footer.php is where the html modal videos is created and brought in.
css/video-base.less is where the base styles are.
js/video-base.js is where the JS is located.

## Extra notes:

To use the modal output Bootstrap must be loaded onto the site before hand (including the javascript).
