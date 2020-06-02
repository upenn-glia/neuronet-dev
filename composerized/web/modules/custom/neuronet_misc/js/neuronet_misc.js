(function ($, Drupal) {
	$('.are-you-sure').on('click', function () {
	    return confirm('Are you sure?');
	});

	Drupal.behaviors.neuronet_thumbnails = {
  		attach: function (context, settings) {
	    	$.ajax({
			  url: 'https://randomuser.me/api/?lego&results=12',
			  dataType: 'json',
			  success: function(data) {
			    $('.student-thumb img').each(function(i){
					if ($(this).attr('src').indexOf('default_images') != -1) {
						$(this).attr('src', data.results[i]['picture']['large']);
					}
				});
			  }
			});
		}
	}
})(jQuery, Drupal);