/**
 * This is JavaScript code that handles SCMC qtype radio buttons and form elements.
 * @package    qtype
 * @subpackage scmc
 * @copyright  ETHZ LET <amr.hourani@id.ethz.ch>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

(function ($) {
	// before ready document, disable id_numberofcolumns
	$('#id_numberofcolumns').css("background-color", "#EEEEEE");
	$('#id_numberofcolumns').prop('disabled', true);
	
	// before ready document, disable id_numberofrows
	$('#id_numberofrows').css("background-color", "#EEEEEE");
	$('#id_numberofrows').prop('disabled', true);	
	
    $(document).ready(function () {
			// Number of Answers
			$('#id_numberofcolumns').on('change', function() {
				howmanyanswers = $('#id_numberofcolumns').val();
				scmctypechanged(howmanyanswers,'changed');
			});
			// Number of Qestions
			$('#id_numberofrows').on('change', function() {
				numberofrows = $('#id_numberofrows').val();
				scmcnumberchanged(numberofrows,'changed');
			});		
			// For radio one right solution only
			$('input[data-colscmc="positive"]').on('click', function() {
				var howmanyanswers = $('#id_numberofcolumns').val();
				if( howmanyanswers == 1){
					var radioscmcid = $(this).attr('id');
					$('input[data-colscmc="positive"]').attr('checked', false); // UN-Tick all TRUE radios
					$('input[data-colscmc="negative"]').attr('checked', true); // Tick all FALSE radios
					 $('#'+radioscmcid).prop('checked', true); // Tick the originally clicked on radio
				}
			});					
			scmctypechanged = function(howmanyanswers, loadorchanged){
				var scmcradionegative = 'input[data-colscmc="negative"]';
				var scmcradiopositive = 'input[data-colscmc="positive"]';
				if (howmanyanswers == 1) {
					$('#judgmentoptionsspan').hide();
					$(scmcradionegative).hide(); // Hide second radio (FALSE)
					$(scmcradionegative).parent().hide(); // Hide the label of radios
					if (loadorchanged == 'changed') {
						$(scmcradionegative).prop('checked', true); // Tick all FALSE radios
						// Tobias: untick all; add the below line
						$(scmcradiopositive).prop('checked', false); // UN-Tick all TRUE radios
					}
					// id_scoringmethod_scmconezero
					// Hide scoring methods.
					$('#id_scoringmethod_subpoints').prop('checked', false); // Untick subpoints  LMDL-131
					$('#id_scoringmethod_subpoints').hide(); // Do not allow change
					$('#id_scoringmethod_subpoints').parent().hide(); // Do not allow change
					
					$('#id_scoringmethod_scmconezero').prop('checked', true); // ALWAYS scmconezero
					$('#id_scoringmethod_scmconezero').hide(); // Do not allow change
					$('#id_scoringmethod_scmconezero').parent().hide(); // Do not allow change
					
					
				} else {
					$('#judgmentoptionsspan').show();
					$(scmcradionegative).show();
					$(scmcradionegative).parent().show(); // Show the label of radio button
					$('#id_scoringmethod_subpoints').show();
					$('#id_scoringmethod_subpoints').parent().show();
					$('#id_scoringmethod_subpoints').prop('checked', true); // Tick subpoints LMDL-130
					$('#id_scoringmethod_scmconezero').show();
					$('#id_scoringmethod_scmconezero').parent().show();
				}
				
				// If changed by human, then tick first TRUE
				if (loadorchanged == 'changed') {
					$('#id_weightbutton_1_1').prop('checked', true);
				}				
			};
			scmcnumberchanged = function(numberofrows, loadorchanged){
				numberofrows = parseInt(numberofrows);
				var maxscmcoptions = 5;
				var allowedtochangeresult = 1;
				var optionboxes = '#optionbox_response_';
				var lasttimerows = $("input[name=qtype_scmc_lastnumberofcols]").val();
				var remainingscmcoptions = maxscmcoptions - numberofrows;

				if (lasttimerows > numberofrows) {
					var scmcdiffraws = lasttimerows - numberofrows;
					 if (confirm(M.util.get_string('deleterawswarning', 'qtype_scmc', scmcdiffraws))) {
						 allowedtochangeresult = 1;
					 } else {
						allowedtochangeresult = 0;
						// reset the select box to original number
						$("#id_numberofrows").val(lasttimerows);
					 }
				}
				
				if (allowedtochangeresult == 1) {				
					// set the current no of rows to choosen one.
					$("input[name=qtype_scmc_lastnumberofcols]").val(numberofrows);
					
					if (numberofrows < maxscmcoptions) { // if I have more but want less..
					//alert(numberofrows);
						// hide all the maxscmcoptions	- if confirmed
						for (i = maxscmcoptions; i > numberofrows; i--) { 
							$(optionboxes+i).hide();
						}
						// Show all the numberofrows again					
						for (i = 1; i <= numberofrows; i++) {
							$(optionboxes+i).show();
						}
					
					} else { // if I have less but want more..
						for (i = 1; i <= maxscmcoptions; i++) {
							$(optionboxes+i).show();
						}						
					}
				}
				
			};

			// initialise the script and do magic :-)
			
			// Enable id_numberofcolumns select
			$('#id_numberofcolumns').prop('disabled', false);
			$('#id_numberofcolumns').css("background-color", "#FFFFFF");
			
			// Enable id_numberofrows select
			$('#id_numberofrows').prop('disabled', false);
			$('#id_numberofrows').css("background-color", "#FFFFFF");	
			
			var howmanyanswers = $('#id_numberofcolumns').val();
			scmctypechanged(howmanyanswers, 'load');
			
			var numberofrows = $('#id_numberofrows').val();
			scmcnumberchanged(numberofrows, 'load');
			
			// If firsttime loading, then tick first TRUE
			if (!$('input[data-colscmc="positive"]:checked').val()) {
				// Tick first TRUE
				$('#id_weightbutton_1_1').prop('checked', true);
			}				

		
	});	
})(jQuery);
// qtype_scmc : END		
