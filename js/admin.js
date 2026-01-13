(function() {
  if (typeof tinymce === 'undefined') return;

  function escapeAttr(str){ return String(str || '').replace(/"/g, '&quot;'); }
  function buildShortcode(opts) {
    var type = opts.type || 'youtube';
    var value = (opts.value || '').trim();
    var poster = (opts.poster || '').trim();
    var thumbnail = !!opts.thumbnail;

    if (!value) return '';

    if (type === 'youtube') {
      var thumbVal = thumbnail ? 'show' : 'hide';
      return '[video youtube_id="' + escapeAttr(value) + '" thumbnail="' + thumbVal + '"]';
    } else {
      return '[video mp4="' + escapeAttr(value) + '"' +
             (poster ? ' poster="' + escapeAttr(poster) + '"' : '') +
             ']';
    }
  }

  // TinyMCE 5+
  function registerV5(editor) {
    function openDialogV5() {
      var dialogApi;
      editor.windowManager.open({
        title: 'Insert Video',
        size: 'normal',
        body: {
          type: 'panel',
          items: [
            { type: 'selectbox', name: 'type', label: 'Video Type',
              items: [
                { text: 'YouTube', value: 'youtube' },
                { text: 'MP4 (self-hosted)', value: 'mp4' }
              ] },
            { type: 'input', name: 'value', label: 'Value', placeholder: 'YouTube ID (e.g. dQw4w9WgXcQ) or MP4 URL' },
            { type: 'input', name: 'poster', label: 'Poster Image URL' },
            { type: 'button', name: 'posterPick', text: 'Select Poster from Media Library' },
            { type: 'checkbox', name: 'thumbnail', label: 'Show Thumbnail (YouTube only)', checked: true }
          ]
        },
        initialData: { type: 'youtube', value: '', poster: '', thumbnail: true },
        buttons: [
          { type: 'cancel', text: 'Cancel' },
          { type: 'submit', text: 'Insert', primary: true }
        ],
        onAction: function(api, details) {
          if (details.name === 'posterPick') {
            if (typeof wp === 'undefined' || !wp.media) {
              editor.windowManager.alert('WordPress media library not available.');
              return;
            }
            var frame = wp.media({ title: 'Select Poster Image', library: { type: 'image' }, button: { text: 'Use this image' }, multiple: false });
            frame.on('select', function() {
              var a = frame.state().get('selection').first();
              var url = a && a.get('url') ? a.get('url') : '';
              if (url) {
                var data = api.getData();
                data.poster = url;
                api.setData(data);
              }
            });
            frame.open();
          }
        },
        onSubmit: function(api) {
          var data = api.getData();
          if (!data.value) { editor.windowManager.alert('Please provide a value.'); return; }
          var sc = buildShortcode(data);
          if (sc) editor.insertContent(sc);
          api.close();
        }
      });
    }

    editor.ui.registry.addButton('rb_video_button', {
      icon: 'media',
      tooltip: 'Insert Video',
      text: 'Insert Video',
      onAction: openDialogV5
    });

    return { name: 'RB Video Inserter (v5)', url: 'https://redballoon.io' };
  }

  // TinyMCE 4
  function registerV4(editor) {
    function openDialogV4() {
      editor.windowManager.open({
        title: 'Insert Video',
        body: [
          { type: 'listbox', name: 'type', label: 'Video Type',
            values: [
              { text: 'YouTube', value: 'youtube' },
              { text: 'MP4 (self-hosted)', value: 'mp4' }
            ] },
          { type: 'textbox', name: 'value', label: 'Value', tooltip: 'YouTube ID or MP4 URL' },
          { type: 'textbox', name: 'poster', label: 'Poster Image URL', tooltip: 'Optional' },
          { type: 'checkbox', name: 'thumbnail', label: 'Show Thumbnail (YouTube only)', checked: true }
        ],
        onsubmit: function(e) {
          var data = e.data || {};
          if (!data.value) { editor.windowManager.alert('Please provide a value.'); return; }
          var sc = buildShortcode(data);
          if (sc) editor.insertContent(sc);
        }
      });
    }

    editor.addButton('rb_video_button', {
      icon: 'media',
      tooltip: 'Insert Video',
      text: 'Insert Video',
      onclick: openDialogV4
    });

    return { name: 'RB Video Inserter (v4)', url: 'https://redballoon.io' };
  }

  tinymce.PluginManager.add('rb_video_button', function(editor) {
    try {
      if (editor.ui && editor.ui.registry && typeof editor.ui.registry.addButton === 'function') {
        return registerV5(editor);
      } else {
        return registerV4(editor);
      }
    } catch (e) {
      // Fail gracefully so TinyMCE doesn't throw "Failed to initialize plugin"
      console && console.error && console.error('RB Video Inserter init error:', e);
      return null;
    }
  });
})();


jQuery(function ($) {
  var $shortcode = $('#rbvb_video_post');
  if (!$shortcode.length) return;

  var $builder = $('#rbvb_builder');
  var $toggle = $('#rbvb_toggle_builder');
  var $apply = $('#rbvb_apply');
  var $cancel = $('#rbvb_cancel');

  var $value = $('#rbvb_value');
  var $poster = $('#rbvb_poster_url');

  function escapeAttr(str) {
    return String(str || '').replace(/"/g, '&quot;');
  }

  function detectYouTubeId(input) {
    var val = (input || '').trim();
    if (!val) return '';

    // Raw ID
    if (/^[a-zA-Z0-9_-]{6,}$/.test(val) && val.indexOf('http') !== 0) {
      return val;
    }

    // URL patterns
    try {
      var url = new URL(val);
      var host = (url.hostname || '').toLowerCase();

      if (host.includes('youtu.be')) {
        return url.pathname.replace(/^\//, '').split('/')[0] || '';
      }

      if (host.includes('youtube.com') || host.includes('youtube-nocookie.com')) {
        // watch?v=
        var v = url.searchParams.get('v');
        if (v) return v;

        // /shorts/<id> or /embed/<id>
        var m = url.pathname.match(/\/(shorts|embed)\/([^/?#]+)/);
        if (m && m[2]) return m[2];
      }
    } catch (e) {
      // not a URL
    }

    return '';
  }

  function isMp4Url(input) {
    var val = (input || '').trim().toLowerCase();
    if (!val) return false;

    // Simple check: ends with .mp4 or has .mp4? query
    return /\.mp4(\?.*)?$/.test(val);
  }

  function buildShortcode(value, poster) {
    var v = (value || '').trim();
    var p = (poster || '').trim();
    if (!v) return '';

    var yt = detectYouTubeId(v);

    if (yt) {
      // Always show thumbnail for youtube
      var sc = '[video youtube_id="' + escapeAttr(yt) + '" thumbnail="show"';
      if (p) sc += ' poster_img_url="' + escapeAttr(p) + '"';
      sc += ']';
      return sc;
    }

    if (isMp4Url(v)) {
      var sc2 = '[video mp4="' + escapeAttr(v) + '"';
      if (p) sc2 += ' poster="' + escapeAttr(p) + '"';
      sc2 += ']';
      return sc2;
    }

    // If neither, assume YouTube ID (fallback), but don’t guess too hard:
    // return '' to force valid input.
    return '';
  }

  function openBuilder() {
    $builder.slideDown(150);
    $toggle.text('Close');
  }
  function closeBuilder() {
    $builder.slideUp(150);
    $toggle.text('Edit Video');
  }

  $toggle.on('click', function () {
    if ($builder.is(':visible')) closeBuilder();
    else openBuilder();
  });

  $cancel.on('click', function () {
    closeBuilder();
  });

  $apply.on('click', function () {
    var sc = buildShortcode($value.val(), $poster.val());
    if (!sc) {
      alert('Please enter a valid YouTube URL/ID or an MP4 URL.');
      return;
    }
    $shortcode.val(sc).trigger('change');
    closeBuilder();
  });

  function hasWpMedia() {
    return typeof window.wp !== 'undefined' && !!window.wp.media;
  }

  function pickMedia(opts, cb) {
    if (!hasWpMedia()) return;

    var frame = wp.media({
      title: opts.title || 'Select file',
      library: opts.library || {},
      button: { text: opts.buttonText || 'Use this' },
      multiple: false
    });

    frame.on('select', function () {
      var a = frame.state().get('selection').first();
      var url = a && a.get('url') ? a.get('url') : '';
      if (url) cb(url);
    });

    frame.open();
  }

  $('#rbvb_pick_mp4').on('click', function (e) {
    e.preventDefault();
    pickMedia(
      { title: 'Select MP4', library: { type: 'video' }, buttonText: 'Use MP4' },
      function (url) {
        $value.val(url).trigger('change');
      }
    );
  });

  $('#rbvb_pick_poster').on('click', function (e) {
    e.preventDefault();
    pickMedia(
      { title: 'Select Poster', library: { type: 'image' }, buttonText: 'Use Image' },
      function (url) {
        $poster.val(url).trigger('change');
      }
    );
  });
});




jQuery(function ($) {
  var $wrap = $('.rbvb-settings-wrap');
  if (!$wrap.length) return; // only run on settings page

  // -------------------------
  // Tabs
  // -------------------------
  function showTab(id) {
    $('.rbvb-tab-panel').hide();
    $('#' + id).show();
    $('.nav-tab-wrapper .nav-tab').removeClass('nav-tab-active');
    $('.nav-tab-wrapper .nav-tab[data-rbvb-tab="' + id + '"]').addClass('nav-tab-active');
  }

  $(document).on('click', '.nav-tab-wrapper .nav-tab', function (e) {
    e.preventDefault();
    var id = $(this).data('rbvb-tab');
    if (id) showTab(id);
  });

  // -------------------------
  // Bunny fields toggle (slide)
  // -------------------------
  var bunnyKeys = [
    'bunny_storage_zone',
    'bunny_storage_endpoint',
    'bunny_storage_access_key',
    'bunny_pullzone_base_url',
    'bunny_base_folder',
    'bunny_keep_wp_subdirs',
    'bunny_delete_local'
  ];

  function bunnyRowSelector(key) {
    // Works because WP Settings API names are rbvb_settings[key]
    return 'input[name="rbvb_settings[' + key + ']"], select[name="rbvb_settings[' + key + ']"], textarea[name="rbvb_settings[' + key + ']"]';
  }

  var $enabled = $('input[name="rbvb_settings[bunny_enabled]"]');
  function toggleBunnyRows() {
    var on = $enabled.is(':checked');
    bunnyKeys.forEach(function (k) {
      var $row = $(bunnyRowSelector(k)).closest('tr');
      if (on) $row.stop(true, true).slideDown(150);
      else $row.stop(true, true).slideUp(150);
    });
  }

  if ($enabled.length) {
    // start hidden rows (but keep checkbox visible)
    toggleBunnyRows();
    $enabled.on('change', toggleBunnyRows);
  }

  // -------------------------
  // Test connection button (AJAX)
  // -------------------------
  $(document).on('click', '#rbvb-bunny-test', function () {
    var $btn = $(this);
    var $out = $('#rbvb-bunny-test-result');

    if (typeof window.RBVBSettings === 'undefined') {
      console.error('RBVBSettings missing. Ensure wp_localize_script ran on rbvb-admin and admin.js is enqueued on this page.');
      $out.text('Error: Settings JS not initialised.').css({ color: '#b32d2e' });
      return;
    }

    $btn.prop('disabled', true);
    $out.text('Testing...').css({ color: '' });

    $.post(window.RBVBSettings.ajaxUrl, {
      action: 'rbvb_bunny_test',
      nonce: window.RBVBSettings.nonce
    })
    .done(function (resp) {
      if (resp && resp.success) {
        $out.text(resp.data && resp.data.message ? resp.data.message : 'Connection OK.').css({ color: '#1e8e3e' });
      } else {
        $out.text(resp && resp.data && resp.data.message ? resp.data.message : 'Test failed.').css({ color: '#b32d2e' });
      }
    })
    .fail(function (xhr) {
      var msg = 'Test failed.';
      try {
        var json = xhr.responseJSON;
        if (json && json.data && json.data.message) msg = json.data.message;
      } catch (e) {}
      $out.text(msg).css({ color: '#b32d2e' });
    })
    .always(function () {
      $btn.prop('disabled', false);
    });
  });
});
