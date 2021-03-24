var woomulti_bulk_run_job = function (data) {
    jQuery.post(ajaxurl, {
        action: "run_woomulti_bulk_sync",
        data: data
    }, function (response) {
        window.woomulti_queue_size++;
        response = JSON.parse(response);
        woomulti_bulk_update_progress(response);

        if (response.queue_id && response.queue_id.length > 0) {
            window.location.href = window.location.href + '&queue_id=' + response.queue_id;
            return;
        }

        if (response.status == 'failed') {
            jQuery('#woomulti-restart-sync').remove();
            jQuery('.sync-progress p').append("<span style='color:red;'>" + response.message + '</span><br />');
            jQuery('.sync-progress p').append("<button type='button' id='woomulti-restart-sync' class='button button-primary'> Continue Sync </button> <br />");
        } else if (response.status != 'completed') {
            woomulti_bulk_run_job('');
        } else if (response.status == 'completed') {
            jQuery('#bulk-sync-cancel-button').remove();
            jQuery('#bulk-sync-reload').show();
        }

    }).fail(function (error) {
        if (error.statusText != 'abort') {
            jQuery('#woomulti-restart-sync').remove();
            jQuery('.sync-progress p').append("<span style='color:red;'>" + error.statusText + '</span><br />');
            jQuery('.sync-progress p').append("<button type='button' id='woomulti-restart-sync' class='button button-primary'> Continue Sync </button> <br />");
        }
    });
};

var woomulti_bulk_update_progress = function (response) {
    if (response.status == 'completed') {
        jQuery('.sync-progress img').css('display', 'none');
    }

    if (response.message) {
        jQuery('.sync-progress p').append(response.message + '<br />');
    }
};

function woomulti_sleep(milliseconds) {
    const date = Date.now();
    let currentDate = null;
    do {
        currentDate = Date.now();
    } while (currentDate - date < milliseconds);
}

jQuery(document).ready(function ($) {
    $('.select-all-products').on('change', function () {
        if ($('.select-all-products').is(':checked')) {
            $('.select-categories').attr('disabled', 'disabled');
        } else {
            $('.select-categories').removeAttr('disabled');
        }
    });

    //disable selected sites on page load
    $('.child-sites-id-' + $('select[name=select-parent-site]').val()).attr('disabled', 'disabled');
    $('.child-sites-id-' + $('select[name=select-parent-site]').val()).prop('checked', false);

    $('select[name=select-parent-site]').on('change', function () {
        var value = $('select[name=select-parent-site]').val();
        $('.select-child-sites').removeAttr('disabled');
        $('.child-sites-id-' + value).attr('disabled', 'disabled');
        $('.child-sites-id-' + value).prop('checked', false);
    });

    $('#bulk-sync-button').on('click', function () {
        if (!confirm("The sync may take a long time depending on the number of produts. Do you really want to begin the sync?")) {
            return;
        }

        //begin the sync
        $('.sync-progress').show();
        $('#bulk-sync-cancel-button').css('visibility', 'visible');
        woomulti_bulk_run_job($('#bulk-sync-form').serialize());
    });

    $('#bulk-sync-cancel-button').on('click', function () {
        if (confirm("Do you really want to cancel sync?")) {
            // window.woomulti_bulk_sync_request_object.abort();
            // $.post(ajaxurl, {
            //     action: 'cancel_woomulti_bulk_sync'
            // }, function(response) {
            //     window.location.href = $('#bulk-sync-cancel-button').attr('data-attr');
            // });
            window.location.href = $('#bulk-sync-cancel-button').attr('data-attr');
        }
    });

    $('#bulk-sync-reload').on('click', function () {
        window.location.href = $(this).attr('data-attr');
    });

    $('#woomulti-restart-sync').on('click', function () {
        if (confirm("Do you really want to restart the sync?")) {
            woomulti_bulk_run_job('');
        }
    });

    if ($('#start-sync-operation').length) {
        $('.sync-progress').show();
        $('#bulk-sync-button').remove();
        $('#bulk-sync-cancel-button').css('visibility', 'visible');

        woomulti_bulk_run_job('');
    }
});