(function ($) {
    "use strict";

    $( '.reset-button-wrap' ).on( 'click', 'a', function(e){
        if (confirm('Are you sure you want to remove all bid history?')) {
        	// go on and finish it
        } else {
        	e.preventDefault();
        }
    } );

}(jQuery));