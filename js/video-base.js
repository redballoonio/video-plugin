// Adds youtube API script onto the page
var tag = document.createElement('script');
var firstScriptTag = document.getElementsByTagName('script')[0];
var vidPageReady = false;

var players = [];
var $iframeElements = jQuery('.iframe-element');

function onYouTubeIframeAPIReady() {
    if (vidPageReady && document.body !== null) {

        var firstID = $iframeElements.eq(0).attr('id').split('_');
        var firstIDno = firstID[(firstID.length - 1)];
        if (firstIDno != 0) {
            console.log('There is an issue with the video base plugin. The first video element needs to have the ID video_element_[n]. The ID\'s have been adjusted but the video may not function properly.')
            $iframeElements.each(function(index, el) {
                $(this).attr('id', 'video_element_' + index);
            });
        };

        for (var i = 0; i < $iframeElements.length; i++) {
            // if there is an issue with load times these can be changed to run consecutivly.
            players[i] = new YT.Player('video_element_' + i, {
                height: '390',
                width: '640',
                videoId: $iframeElements.eq(i).attr('data-id'),
                playerVars: {
                    rel: 0
                },
                events: {
                    'onReady': onPlayerReady,
                    'onStateChange': onPlayerStateChange
                }
            });
        }
        console.log(players[0]);
    } else {
        setTimeout(function() {
            onYouTubeIframeAPIReady()
        }, 200);
    }
};

function onPlayerReady(event) {
    // Event that fies on player ready
    console.log('Player ready')
    if ($('section.hero-video').length > 0) {
        event.target.playVideo();
    }
}

function onPlayerStateChange(event) {

    var $playerThatChanged = jQuery(event.target.getIframe());

    if (event.data == YT.PlayerState.ENDED && $playerThatChanged.closest('#video-modals').length > 0) {
        jQuery('#video-modals .modal').modal('hide');
    }
    if (event.data == YT.PlayerState.ENDED) {
        $playerThatChanged.closest('.play-video').removeClass('play-video');
    }
}

function resetVideo(video) {
    if (typeof video === 'number') {
        $(players[video].getIframe()).parent('.play-video').removeClass('play-video');
        players[video].stopVideo();
    }
}

function getChildPlayer(parent) {
    // Get player object from parent element.
    var thisPlayer = parent.find('iframe')[0].id.split('_');
    var thisPlayerID = parseInt(thisPlayer[thisPlayer.length - 1]);
    return players[thisPlayerID];
}
// Javascript for the video base plugin
var loadedPage = false; // Was used to prevent click before loading. Doesn't work on ios however.
var isIOS = false; // Checks if the device is ios.

if (navigator.userAgent.match(/(iPod|iPhone|iPad)/)) {
    isIOS = true;
}

function isPropertySupported(property) {
    return property in document.body.style;
}
jQuery(document).ready(function($) {
    setTimeout(function() {
        vidPageReady = true;
    }, 500);

    if ($iframeElements.length > 0) {
        // load videos:
        tag.src = "https://www.youtube.com/iframe_api";
        firstScriptTag.parentNode.insertBefore(tag, firstScriptTag);

        $('.video-content').on('click', function(event) {
            if (!$(this).find('.play-video').length > 0 && ($(this).parent().hasClass('type--embed') || $(this).parent().hasClass('type--gallery'))) {
                $(this).addClass('play-video');
                if ($(this).find('iframe').length > 0) {
                    getChildPlayer($(this)).playVideo();
                }
            }
        });

        $('.video-base-carousel').on('slide.bs.carousel', function(event) {
            console.log('slide');
            $(this).find('.iframe-wrap').each(function(index, el) {
                getChildPlayer($(this)).stopVideo();
                $(this).removeClass('play-video');
            });
        });

        $('#video-modals .modal').on({
            'shown.bs.modal': function() {
                getChildPlayer($(this)).playVideo();
            },
            'hide.bs.modal': function() {
                getChildPlayer($(this)).stopVideo();
            }
        });

        // play videos automatically when clicking thumbnail control.

        $('.video-thumbnail-controls .thumbnail-control').on('click', function() {
            var $targetCarousel = $($(this).attr('data-target'));
            if (parseInt($targetCarousel.find('.active').prevAll('.item').length) == parseInt($(this).attr('data-slide-to'))) {
                var $curslide = $targetCarousel.find('.active');
                getChildPlayer($curslide).playVideo();
                $curslide.find('.video-content').addClass('play-video');
            } else {
                $targetCarousel.one('slid.bs.carousel', function(event) {
                    var $newslide = $(this).find('.active');
                    getChildPlayer($newslide).playVideo();
                    $newslide.find('.video-content').addClass('play-video');
                });
            }
        });
    } else {
        console.log('Video-base: No videos on page');
    }

});