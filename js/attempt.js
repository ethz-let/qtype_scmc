require(['jquery'], function($) {
$.noConflict();
(function ($) {
    $(document).ready(function () {
		$('input[data-multiscmc="0"]').each(function(){
			if ($(this).is(':checked')) {
				var qtypescmc_whichid = $(this).attr('data-scmc');
				var qtypescmc_whichelementid = $(this).attr('id');	
				$('input[data-hiddenscmc="'+qtypescmc_whichid+'"]').attr("disabled", false); // remove all values from hidden
			}
		});
		$('input[data-scmc^=qtype_scmc]').on('click', function() {
		 if ( $(this).attr("data-multiscmc") == 0 ) {
			var radioscmcid = $(this).attr('id');
			var radioscmcdatascmc = $(this).attr('data-scmc');
			$('input[data-scmc="'+radioscmcdatascmc+'"]').prop('checked', false);			
			$('input[data-hiddenscmc="'+radioscmcdatascmc+'"]').attr("disabled", false); // remove all values from hidden
			//$('input[data-hiddenscmc="'+radioscmcdatascmc+'"]').val("2"); // remove all values from hidden
			$('input[id="hidden_' + radioscmcid + '"]').attr("disabled", true); // disable this element			
			$('input[id="' + radioscmcid + '"]').prop('checked', true); // Tick the originally clicked on radio
		 }
		});
	});	
})(jQuery);
});
// qtype_scmc : END		
