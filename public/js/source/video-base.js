module.exports = function(w, d, $){

    var tag = document.createElement('script');
    var firstScriptTag = document.getElementsByTagName('script')[0];
    var vidPageReady = false;

    var players = [];
    var $iframeElements = $('.rbd-iframe-element');

    w.onYouTubeIframeAPIReady = function() {
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
        } else {
            setTimeout(function() {
                onYouTubeIframeAPIReady()
            }, 200);
        }
    };

    w.onPlayerReady = function(event) {
        // Event that fies on player ready
        if ($('section.hero-video').length > 0) {
            event.target.playVideo();
        }
    }

    w.onPlayerStateChange = function(event) {

        var $playerThatChanged = $(event.target.getIframe());

        if (event.data == YT.PlayerState.ENDED && $playerThatChanged.closest('#video-modals').length > 0) {
            $('#video-modals .modal').modal('hide');
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

    $(d).ready(function($) {

        setTimeout(function() {
            vidPageReady = true;
        }, 500);

        if ($iframeElements.length > 0) {
            // load videos:
            tag.src = "https://www.youtube.com/iframe_api";
            firstScriptTag.parentNode.insertBefore(tag, firstScriptTag);

            $('.rbd-video-content').on('click', function(event) {
                if (!$(this).find('.play-video').length > 0 && ($(this).parent().hasClass('type--embed') || $(this).parent().hasClass('type--gallery'))) {
                    $(this).addClass('play-video');
                    if ($(this).find('iframe').length > 0) {
                        getChildPlayer($(this)).playVideo();
                    }
                }
            });

            $('.rbd-video-base-carousel').on('slide.bs.carousel', function(event) {
                $(this).find('.rbd-iframe-wrap').each(function(index, el) {
                    getChildPlayer($(this)).stopVideo();
                    $(this).removeClass('play-video');
                });
            });

            $('.rbd-modal').on({
                'shown.bs.modal': function() {
                    setTimeout( () => {
                        getChildPlayer($(this)).playVideo();
                    }, 100);
                },
                'hide.bs.modal': function() {
                    getChildPlayer($(this)).stopVideo();
                }
            });

            // play videos automatically when clicking thumbnail control.

            $('.rbd-video-thumbnail-controls .thumbnail-control').on('click', function() {
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
};