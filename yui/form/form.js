/**
 * This is JavaScript code that handles SCMC qtype radio buttons and form elements.
 * @package    qtype
 * @subpackage scmc
 * @copyright  ETHZ LET <amr.hourani@id.ethz.ch>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

(function ($) {

    $(document).ready(function () {
		//var InitiateSCMC = function (howmanyanswers, $tr, $cell, text, $draggable, check_boxes) {
			//alert("hello.. "+$('#id_howmanyanswers').val());
			
			

			$('#id_howmanyanswers').on('change', function() {
				howmanyanswers = $('#id_howmanyanswers').val();
				scmctypechanged(howmanyanswers);
			});
			
			scmctypechanged = function(howmanyanswers){
				if (howmanyanswers == 1) {
					$('#judgmentoptionsspan').hide();
					$('input[data-colscmc="negative"]').hide(); // Hide second radio (FALSE)
					$('input[data-colscmc="negative"]').parent().hide(); // Hide the label of radio button
				} else{
					$('#judgmentoptionsspan').show();
					$('input[data-colscmc="negative"]').show();
					$('input[data-colscmc="negative"]').parent().show(); // Show the label of radio button
				}
			};
			var howmanyanswers = $('#id_howmanyanswers').val();
			scmctypechanged(howmanyanswers);
		//};
		//var howmanyanswers = $('#id_howmanyanswers').val();
		//InitiateSCMC();
		
	});	
})(jQuery);
// qtype_scmc : END		
