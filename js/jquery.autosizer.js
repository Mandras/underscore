// jQuery Autosizer plugin v1.0
// Automatically resize an element by calculating his base ratio
// See more: https://github.com/Mandras/jquery.autosizer.js

(function($) {
	$.fn.autosizer = function(type, size) {
		if (typeof type === 'undefined') {
			console.log('Autosizer jQuery plugin: No method given');
			return (false);
		}

		if (type == 'resize') {
			return this.each(function() {
				$(this).trigger('resize');
				return (this);
			});
		}
		else if (type == 'destroy') {
			return this.each(function() {
				$(this).removeAttr('data-autosizer-ratio');
				$(this).removeAttr('data-autosizer-type');
				$(this).removeClass('jqautosizer');
				$(this).unbind('resize');
				return (this);
			});
		}

		if (type != 'width' && type != 'height' && type != 'auto') {
			console.log('Autosizer jQuery plugin: Unrecognized option: ' + type);
			return (false);
		}

		if ($(".jqautosizer").length == 0) {
			$(window).resize(function() { $(".jqautosizer").trigger('resize'); });
		}

		if (typeof size === 'undefined') {
			var size = this.width() + 'x' + this.height();
		}

		size = size.split("x");

		if (size.length != 2) {
			console.log("Autosizer jQuery plugin: Size parameter require a string of type: 480x360");
			return (false);
		}

		var ratio = parseInt(size[0]) / parseInt(size[1]);

		return this.each(function() {

			if (type == 'width') 		{ $(this).width($(this).height() * ratio); }
			else if (type == 'height') 	{ $(this).height($(this).width() / ratio); }
			else {
				$(this).width($(this).parent().innerHeight() * ratio);
				$(this).height($(this).parent().innerWidth() * ratio);
			}

			$(this).attr('data-autosizer-type', type);
			$(this).attr('data-autosizer-ratio', ratio);
			$(this).addClass('jqautosizer');

			$(this).bind("resize", function() {
				var ratio = parseFloat($(this).attr('data-autosizer-ratio'));
				var type = $(this).attr('data-autosizer-type');

				if (type == 'width') 		{ $(this).width($(this).height() * ratio); }
				else if (type == 'height') 	{ $(this).height($(this).width() / ratio); }
				else {
					$(this).width($(this).parent().innerHeight() * ratio);
					$(this).height($(this).parent().innerWidth() * ratio);
				}

				return (false);
			});

			return (this);
		});
	};
})(jQuery);