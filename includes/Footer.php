<?php
function outputModalHTML(){
    // Global variables set in ../video-base.php
    if ( $GLOBALS['videoBaseModals'] > 0) {
        $modalOutput  = '<div id="video-modals">';
        $modalOutput .= $GLOBALS['modalsHTML'];
        $modalOutput .= '</div><!--video-modals-->';
        echo $modalOutput;
    }
}
?>
