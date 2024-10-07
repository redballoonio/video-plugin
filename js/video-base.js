/* Video Base - Scripts */
// Adds youtube API script onto the page
var tag = document.createElement('script');
var firstScriptTag = document.getElementsByTagName('script')[0];
var vidPageReady = false;

var players = [];
var iframeElements = document.querySelectorAll('.iframe-element');

// Function that is called when the YouTube IFrame API is ready
function onYouTubeIframeAPIReady() {
  if (vidPageReady && document.body !== null) {
    var firstID = iframeElements[0].getAttribute('id').split('_');
    var firstIDno = firstID[firstID.length - 1];
    if (firstIDno != 0) {
      console.log("There is an issue with the video base plugin. The first video element needs to have the ID video_element_[n]. The ID's have been adjusted but the video may not function properly.");
      iframeElements.forEach(function (el, index) {
        el.setAttribute('id', 'video_element_' + index);
      });
    }

    // Create YouTube players for each iframe element
    iframeElements.forEach(function (el, i) {
      players[i] = new YT.Player('video_element_' + i, {
        height: '390',
        width: '640',
        videoId: el.getAttribute('data-id'),
        playerVars: {
          rel: 0,
        },
        events: {
          onReady: onPlayerReady,
          onStateChange: onPlayerStateChange,
        },
      });
    });
    //console.log(players[0]);
  } else {
    setTimeout(function () {
      onYouTubeIframeAPIReady();
    }, 200);
  }
}

// Function that is called when the player is ready
function onPlayerReady(event) {
  console.log('Player ready');
  if (document.querySelector('section.hero-video')) {
    event.target.playVideo();
  }
}

// Function that is called when the player's state changes
function onPlayerStateChange(event) {
  var playerThatChanged = event.target.getIframe();

  if (event.data == YT.PlayerState.ENDED && playerThatChanged.closest('#video-modals')) {
    document.querySelector('#video-modals .modal').classList.remove('show');
  }
  if (event.data == YT.PlayerState.ENDED) {
    var playVideoElement = playerThatChanged.closest('.play-video');
    if (playVideoElement) {
      playVideoElement.classList.remove('play-video');
    }
  }
}

// Function to reset the video player
function resetVideo(video) {
  if (typeof video === 'number') {
    var playVideoElement = players[video].getIframe().closest('.play-video');
    if (playVideoElement) {
      playVideoElement.classList.remove('play-video');
    }
    players[video].stopVideo();
  }
}

// Function to get the player object from the parent element
function getChildPlayer(parent) {
  var iframe = parent.querySelector('iframe');
  var thisPlayer = iframe.id.split('_');
  var thisPlayerID = parseInt(thisPlayer[thisPlayer.length - 1]);
  return players[thisPlayerID];
}

// JavaScript for the video base plugin
var loadedPage = false;
var isIOS = false;

// Check if the user is on an iOS device
if (navigator.userAgent.match(/(iPod|iPhone|iPad)/)) {
  isIOS = true;
}

// Function to check if a CSS property is supported
function isPropertySupported(property) {
  return property in document.body.style;
}

// Document Ready
// Initialize the script when the DOM is fully loaded
document.addEventListener('DOMContentLoaded', function () {
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

  iframeElements = document.querySelectorAll('.iframe-element');

  // Set a timeout to indicate the page is ready
  setTimeout(function () {
    vidPageReady = true;
  }, 500);

  // If there are iframe elements on the page
  if (iframeElements.length > 0) {
    // Load YouTube IFrame API dynamically
    tag.src = 'https://www.youtube.com/iframe_api';
    firstScriptTag.parentNode.insertBefore(tag, firstScriptTag);

    // Add click event listeners to video content iframes
    document.querySelectorAll('.video-content-iframe').forEach(function (el) {
      el.addEventListener('click', function () {
        if (!el.classList.contains('play-video') && (el.parentNode.classList.contains('type--embed') || el.parentNode.classList.contains('type--gallery'))) {
          el.classList.add('play-video');
          var videoPlayingElement = el.closest('.video-playing');
          if (videoPlayingElement) {
            videoPlayingElement.classList.add('video-playing');
          }
          if (el.querySelector('iframe')) {
            getChildPlayer(el).playVideo();
          }
        }
      });
    });
  } else {
    console.log('Video-base: No videos on page');
  }

  // Add click event listeners to video content thumbnails
  document.querySelectorAll('.video-content-thumb').forEach(function (el) {
    el.addEventListener('click', function () {
      if (!el.classList.contains('play-video') && (el.parentNode.classList.contains('type--embed') || el.parentNode.classList.contains('type--gallery'))) {
        el.classList.add('play-video');
        var videoPlayingElement = el.closest('.video-playing');
        if (videoPlayingElement) {
          videoPlayingElement.classList.add('video-playing');
        }

        var iframeWrap = el.querySelector('.iframe-wrap');
        var videoId = iframeWrap.getAttribute('data-video-id');
        var thumbnail = iframeWrap.querySelector('.video-thumbnail');

        // Load YouTube API and create player
        loadYouTubeAPI(function () {
          var iframeContainer = document.createElement('div');
          iframeContainer.id = 'player-' + videoId + '-' + Math.floor(Math.random() * 100000);
          if (thumbnail) {
            thumbnail.remove();
          }
          iframeWrap.appendChild(iframeContainer);

          var player = new YT.Player(iframeContainer.id, {
            videoId: videoId,
            playerVars: {
              autoplay: 1,
              rel: 0,
            },
            events: {
              onStateChange: function (event) {
                if (event.data == YT.PlayerState.ENDED) {
                  el.classList.remove('play-video');
                  var videoPlayingElement = el.closest('.video-playing');
                  if (videoPlayingElement) {
                    videoPlayingElement.classList.remove('video-playing');
                  }
                  iframeWrap.innerHTML = '';
                  if (thumbnail) {
                    iframeWrap.appendChild(thumbnail);
                  }
                }
              },
            },
          });
        });
      }
    });
  });

  // Automatically play the article video after a delay if it exists
  setTimeout(function () {
    var articleVideo = document.querySelector('.article-video');
    if (articleVideo && players[0] && players[0].playVideo) {
      players[0].playVideo();
    }
  }, 3000);
});

function openModalWithCompatibility(element, modalId) {
  // Add 'modal-open' class to the clicked element
  element.classList.add('modal-open');

  // Check if jQuery is available, indicating Bootstrap 4
  if (typeof jQuery !== 'undefined' && typeof jQuery.fn.modal !== 'undefined') {
    // Use jQuery to open the modal for Bootstrap 4
    jQuery('#' + modalId).modal('show');
  } else if (typeof bootstrap !== 'undefined') {
    // Use Bootstrap's JavaScript API for Bootstrap 5
    var modalElement = document.getElementById(modalId);
    if (modalElement) {
      var bootstrapModal = new bootstrap.Modal(modalElement);
      bootstrapModal.show();
    }
  } else {
    console.error('Neither jQuery nor Bootstrap modal found. Ensure Bootstrap 4 or 5 is loaded.');
  }
}