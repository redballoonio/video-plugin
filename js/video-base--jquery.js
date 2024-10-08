/* Video Base - Scripts */
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

// Document Ready
jQuery(document).ready(function($) {

  // Function to load YouTube IFrame API dynamically
  function loadYouTubeAPI(callback) {
    if (typeof YT === 'undefined' || typeof YT.Player === 'undefined') {
      var tag = document.createElement('script');
      tag.src = 'https://www.youtube.com/iframe_api';
      var firstScriptTag = document.getElementsByTagName('script')[0];
      firstScriptTag.parentNode.insertBefore(tag, firstScriptTag);

      // Wait for the API to be ready
      window.onYouTubeIframeAPIReady = function() {
        callback();
      };
    } else {
      // API already loaded
      callback();
    }
  }

  // Gets the iframe elements again.
  $iframeElements = jQuery('.iframe-element');

  setTimeout(function() {
    vidPageReady = true;
  }, 500);

  if ($iframeElements.length > 0) {
    // load videos:
    tag.src = "https://www.youtube.com/iframe_api";
    firstScriptTag.parentNode.insertBefore(tag, firstScriptTag);

    // If thumbnail is hide
    $('.video-content-iframe').on('click', function(event) {
      if (!$(this).find('.play-video').length > 0 && ($(this).parent().hasClass('type--embed') || $(this).parent().hasClass('type--gallery'))) {
        $(this).addClass('play-video');
        $(this).parent().parent().addClass('video-playing');
        if ($(this).find('iframe').length > 0) {
          getChildPlayer($(this)).playVideo();
        }
      }
    });

    // Event handler for carousel slide
    $('.video-base-carousel').on('slide.bs.carousel', function(event) {
      console.log('slide');
      $(this).find('.iframe-wrap').each(function(index, el) {
        var $iframeWrap = $(this);
        $iframeWrap.closest('.video-content').removeClass('play-video');
        $iframeWrap.parent().parent().removeClass('video-playing');
        // Remove iframe to stop the video
        $iframeWrap.empty();
        // Optionally, re-add the thumbnail if needed
        var $thumbnail = $iframeWrap.data('thumbnail');
        if ($thumbnail) {
          $iframeWrap.append($thumbnail);
        }
      });
    });

    // Modal event handlers
    $('#video-modals .modal').on({
      'shown.bs.modal': function() {
        var $this = $(this);
        var $iframeWrap = $this.find('.iframe-wrap');
        var videoId = $iframeWrap.attr('data-video-id');

        // Save the thumbnail HTML to re-add it later
        var $thumbnail = $iframeWrap.find('.video-thumbnail');

        // Load YouTube IFrame API and create player
        loadYouTubeAPI(function() {
          var player;
          var iframeContainer = document.createElement('div');
          iframeContainer.id = 'player-' + videoId + '-' + Math.floor(Math.random() * 100000);
          
          // Remove the thumbnail
          $thumbnail.remove();
          
          $iframeWrap.append(iframeContainer);

          player = new YT.Player(iframeContainer.id, {
            videoId: videoId,
            playerVars: {
              autoplay: 1,
              rel: 0
            },
            events: {
              'onStateChange': function(event) {
                if (event.data == YT.PlayerState.ENDED) {
                  // Video ended, remove iframe and show thumbnail
                  $iframeWrap.empty();
                  $iframeWrap.append($thumbnail);
                  // Close modal
                  $this.modal('hide');
                }
              }
            }
          });
        });
      },
      'hide.bs.modal': function() {
        var $this = $(this);
        var $iframeWrap = $this.find('.iframe-wrap');
        // Remove iframe to stop video
        $iframeWrap.empty();
        // Optionally, re-add the thumbnail
        var $thumbnail = $iframeWrap.data('thumbnail');
        if ($thumbnail) {
          $iframeWrap.append($thumbnail);
        }
      }
    });

    // Play videos automatically when clicking thumbnail control
    $('.video-thumbnail-controls .thumbnail-control').on('click', function() {
      var $targetCarousel = $($(this).attr('data-target'));
      var slideTo = parseInt($(this).attr('data-slide-to'));
      var activeIndex = $targetCarousel.find('.active').index();

      if (activeIndex == slideTo) {
        var $currentSlide = $targetCarousel.find('.active');
        $currentSlide.find('.video-content').trigger('click');
      } else {
        $targetCarousel.one('slid.bs.carousel', function(event) {
          var $newSlide = $(this).find('.active');
          $newSlide.find('.video-content').trigger('click');
        });
        $targetCarousel.carousel(slideTo);
      }
    });
  } else {
    console.log('Video-base: No videos on page');
  }

  // If thumbnail is show
  $('.video-content-thumb').on('click', function(event) {
    var $this = $(this);
    if (!$this.hasClass('play-video') && ($this.parent().hasClass('type--embed') || $this.parent().hasClass('type--gallery'))) {
      $this.addClass('play-video');
      $this.parent().parent().addClass('video-playing');

      var $iframeWrap = $this.find('.iframe-wrap');
      var videoId = $iframeWrap.attr('data-video-id');

      // Save the thumbnail HTML to re-add it later
      var $thumbnail = $iframeWrap.find('.video-thumbnail');

      // Load YouTube IFrame API and create player
      loadYouTubeAPI(function() {
        var player;
        var iframeContainer = document.createElement('div');
        iframeContainer.id = 'player-' + videoId + '-' + Math.floor(Math.random() * 100000);
        
        // Remove the thumbnail
        $thumbnail.remove();
        
        $iframeWrap.append(iframeContainer);

        player = new YT.Player(iframeContainer.id, {
          videoId: videoId,
          playerVars: {
            autoplay: 1,
            rel: 0
          },
          events: {
            'onStateChange': function(event) {
              if (event.data == YT.PlayerState.ENDED) {
                // Video ended, remove iframe and show thumbnail
                $this.removeClass('play-video');
                $this.parent().parent().removeClass('video-playing');
                $iframeWrap.empty();
                // Re-add the thumbnail
                $iframeWrap.append($thumbnail);
              }
            }
          }
        });
      });
    }
  });

  // Optional: Automatically play video if present (e.g., hero video)
  setTimeout(function() {
    if ($('.article-video').length > 0) {
      if (undefined !== players[0].playVideo) {
        players[0].playVideo();
      }
    }
  }, 3000);

});
