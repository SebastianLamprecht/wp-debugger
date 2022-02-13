var $ = jQuery;

$(document).ready(function() {
	$('#debug .debug-action-bar .toggle-size').click(function() {
		event.preventDefault();
		var debug = $('#debug');
		debug.toggleClass( 'minimized' );
		debug.find('.toggle-size').toggle();
	});

	$('#debug .data-block .caller a').click(function(event) {
		event.preventDefault();
		$(this).parents('.data-block').toggleClass('minimized');
	})
});

hljs.highlightAll();