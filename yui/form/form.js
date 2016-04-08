/**
 * This is JavaScript code that handles SCMC qtype radio buttons and form elements.
 * @package    qtype
 * @subpackage scmc
 * @copyright  ETHZ LET <amr.hourani@id.ethz.ch>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

(function ($) {
	// before ready document, disable id_scmchowmanyanswers
	$('#id_scmchowmanyanswers').css("background-color", "#EEEEEE");
	$('#id_scmchowmanyanswers').prop('disabled', true);
	
	// before ready document, disable id_numberofoptions
	$('#id_numberofoptions').css("background-color", "#EEEEEE");
	$('#id_numberofoptions').prop('disabled', true);	
	
    $(document).ready(function () {
			// Number of Answers
			$('#id_scmchowmanyanswers').on('change', function() {
				howmanyanswers = $('#id_scmchowmanyanswers').val();
				scmctypechanged(howmanyanswers);
			});
			// Number of Qestions
			$('#id_numberofoptions').on('change', function() {
				numberofoptions = $('#id_numberofoptions').val();
				scmcnumberchanged(numberofoptions);
			});		
			// For radio one right solution only
			$('input[data-colscmc="positive"]').on('click', function() {
				var howmanyanswers = $('#id_scmchowmanyanswers').val();
				if( howmanyanswers == 1){
					var radioscmcid = $(this).attr('id');
					$('input[data-colscmc="positive"]').attr('checked', false); // UN-Tick all TRUE radios
					$('input[data-colscmc="negative"]').attr('checked', true); // Tick all FALSE radios
					$('#'+radioscmcid).prop('checked', true); // Tick the originally clicked on radio
				}
			});					
			scmctypechanged = function(howmanyanswers){
				var scmcradionegative = 'input[data-colscmc="negative"]';
				var scmcradiopositive = 'input[data-colscmc="positive"]';
				if (howmanyanswers == 1) {
					$('#judgmentoptionsspan').hide();
					$(scmcradionegative).hide(); // Hide second radio (FALSE)
					$(scmcradionegative).parent().hide(); // Hide the label of radios
					$(scmcradionegative).attr('checked', true); // Tick all FALSE radios
				} else{
					$('#judgmentoptionsspan').show();
					$(scmcradionegative).show();
					$(scmcradionegative).parent().show(); // Show the label of radio button
				}
			};
			scmcnumberchanged = function(numberofoptions){
				numberofoptions = parseInt(numberofoptions);
				var maxscmcoptions = 5;
				var optionboxes = '#optionbox_response_';
				var remainingscmcoptions = maxscmcoptions - numberofoptions;
				
				if (numberofoptions < maxscmcoptions) { // if I have more but want less..
					// hide all the maxscmcoptions	
					for (i = maxscmcoptions; i > numberofoptions; i--) { 
						$(optionboxes+i).hide();
					}
					// Show all the numberofoptions again					
					for (i = 1; i <= numberofoptions; i++) {
						$(optionboxes+i).show();
					}					
				} else { // if I have less but want more..
					for (i = 1; i <= maxscmcoptions; i++) {
						$(optionboxes+i).show();
					}						
				}
				// Now update the hidden field with actual No of options
				$('#id_choosennoofoptions').val(numberofoptions);
			};			
			// Enable id_scmchowmanyanswers select
			$('#id_scmchowmanyanswers').prop('disabled', false);
			$('#id_scmchowmanyanswers').css("background-color", "#FFFFFF");
			
			// Enable id_numberofoptions select
			$('#id_numberofoptions').prop('disabled', false);
			$('#id_numberofoptions').css("background-color", "#FFFFFF");	
			
			var howmanyanswers = $('#id_scmchowmanyanswers').val();
			scmctypechanged(howmanyanswers);
			
			var numberofoptions = $('#id_numberofoptions').val();
			scmcnumberchanged(numberofoptions);
			
			// Now update the hidden field with actual No of options
			$('#id_choosennoofoptions').val(numberofoptions);
					

		
	});	
})(jQuery);
// qtype_scmc : END		
