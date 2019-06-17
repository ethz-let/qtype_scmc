require(['jquery'], function($) {
    $.noConflict();
    (function ($) {
        $(document).ready(function () {
            $('input[data-multiscmc="0"]').each(function(){
                if ($(this).is(':checked')) {
                    var qtypescmc_whichid = $(this).attr('data-scmc');
                    // Remove all values from hidden.
                    $('input[data-hiddenscmc="' + qtypescmc_whichid + '"]').attr("disabled", false);
                }
            });
            $('input[data-scmc^=qtype_scmc]').on('click', function() {
                if ( $(this).attr("data-multiscmc") == 0 ) {
                    var radioscmcid = $(this).attr('id');
                    var radioscmcdatascmc = $(this).attr('data-scmc');
                    $('input[data-scmc="' + radioscmcdatascmc + '"]').prop('checked', false);
                    // Remove all values from hidden.
                    $('input[data-hiddenscmc="' + radioscmcdatascmc + '"]').attr("disabled", false);
                    // Disable this element.
                    $('input[id="hidden_' + radioscmcid + '"]').attr("disabled", true);
                    // Tick the originally clicked on radio.
                    $('input[id="' + radioscmcid + '"]').prop('checked', true);
                }
            });
        });
    })(jQuery);
});
