//
// This is your script
//

$(document).ready(function() {
	// Les elements ayant la class "noselect" ne peuvent pas etre drag
	$('.noselect').on('dragstart', function(e) { e.preventDefault(); });

	// Les liens ayant la class "anchor" feront un scroll anime
	$('a.anchor').each(function() {
		if ($(this).attr('href').length == 0 || $($(this).attr('href')).length == 0) return ;
		$(this).click(function() {
			$('html, body').stop().animate({scrollTop: $($(this).attr('href')).offset().top},'slow');
		});
	});
});
