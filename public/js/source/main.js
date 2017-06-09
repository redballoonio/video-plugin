var videoBaseScript = require('./video-base.js');
(function(w, d, $){

    function jqueryLoaded() {
        // JS for the modals
        require('./modal.js');
        // JS for the gallery
        require('./gallery.js');

        videoBaseScript(w, d, $);
    }

    if(typeof $ =='undefined') {
        var headTag = document.getElementsByTagName("head")[0];
        var jqTag = document.createElement('script');
        jqTag.type = 'text/javascript';
        jqTag.integrity = 'sha256-ZosEbRLbNQzLpnKIkEdrPv7lOy9C27hHQ+Xp8a4MxAQ=';
        jqTag.crossorigin = 'anonymous';
        jqTag.src = 'https://cdnjs.cloudflare.com/ajax/libs/jquery/1.12.4/jquery.min.js';
        jqTag.onload = jqueryLoaded;
        headTag.appendChild(jqTag);
    } else {
        jqueryLoaded();
    }
})(window, document, jQuery)