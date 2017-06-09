var videoBaseScript = require('./video-base.js');
(function(){

    function jqueryLoaded() {
        var $ = jQuery;
        
        // JS for the modals
        require('./modal.js');
        // JS for the gallery
        require('./gallery.js');

        videoBaseScript(window, document, $);
    }

    if(typeof jQuery =='undefined') {
        var headTag = document.getElementsByTagName("head")[0];
        var jqTag = document.createElement('script');
        jqTag.type = 'text/javascript';
        jqTag.src = 'https://cdnjs.cloudflare.com/ajax/libs/jquery/1.12.4/jquery.min.js';
        jqTag.onload = jqueryLoaded;
        headTag.appendChild(jqTag);
    } else {
        jqueryLoaded();
    }
})();