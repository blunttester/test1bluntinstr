var woomulti_run_job = function(data) {

     window.woomulti_product_sync_request_object = jQuery.post(ajaxurl, {
        action: "woomulti_process_job"
    }, function(data){

        data = JSON.parse(data);

        if (data.status == 'failed') {
            woomulti_update_failed();
        } else {
            woomulti_update_sync_message('Sync in progress..');

            //update progress percentage
            if ( data.progress_percentage ) {
                woomulti_update_progress(data.progress_percentage);
            }

            //update product count
            if ( data.product_count ) {
                woomulti_update_product_count( data.product_count );
            }

            if ( data.status == 'completed' ) {
                woomulti_update_complete();
                return true;
            } else {
                woomulti_run_job();
            }
        }
    }).fail(function(error) {
        if (error.statusText != 'abort') {
            woomulti_update_failed("Sync failed due to server error. Please try again.");
        }
    });
};

var woomulti_update_sync_message = function (msg) {
    jQuery('.woo-sync-message').text(msg);
};

var woomulti_update_product_count = function (msg) {
    jQuery('.woo-sync-product-count').text(msg);
};

var woomulti_update_progress = function (percentage) {
    jQuery( "#woo-product-update-progress-bar" ).progressbar({
        value:  percentage
    });
};

var woomulti_update_complete = function () {
    jQuery('.woomultistore_sync_completed').show();
    woomulti_update_sync_message('Sync completed');
    jQuery('.woomultistire_sync_container input[name=submit]').hide();
    woomulti_close_counter();
};

var woomulti_update_failed = function (msg='Sync failed.') {
    jQuery('.woomultistore_sync_failed').show();
    woomulti_update_sync_message(msg);
    jQuery('.woomultistire_sync_container input[name=submit]').hide();
};

var woomulti_close_counter = function (msg='Close (10)') {
    jQuery('.close-sync-screen').show();

    window.woo_multi_close_counter = setInterval(function(){
        var counter = jQuery('.close-sync-screen a').attr('data-attr');

        if (counter <= 0) {
            jQuery('.woomulti-panel').slideUp();
            window.clearInterval( window.woo_multi_close_counter );
            window.location.href = window.location.href;
        } else {
            jQuery('.close-sync-screen a').text("Close (" + counter + ") ");
            counter = counter - 1;
            jQuery('.close-sync-screen a').attr('data-attr', counter);
        }
    }, 1000);
};


jQuery( document ).ready(function($) {
    $( "#woo-product-update-progress-bar" ).progressbar({
        value: 1
    });

    $('.woomulti-cancel-sync').on('click', function() {
        if (confirm("Do you really want to cancel sync?") ) {
            window.woomulti_product_sync_request_object.abort();
            $.post(ajaxurl, {
                action: 'woomulti_cancel_sync'
            }, function(response) {
                window.location.href = window.location.href;
            });
        }
    });

    $('.close-sync-screen a').on( 'click', function() {
        $('.woomulti-panel').slideUp();
        window.location.href = window.location.href;
    });

    if ( $('#woo-product-update-progress-bar').length >= 1) {
        if ( $('#wp__notice-list').length ) {
            $('#wp__notice-list').show(); // show sync dialogue hidden by woocommerce admin.
        }
        
        woomulti_update_sync_message('Sync started..');
        woomulti_run_job();
    };
});