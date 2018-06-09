/**
* WordPress 3.5+ Media Library Uploader
*/
(function($){
	media = {
		// Process when a button with class .insert-media-url is clicked
		init: function() {
			// Open Media Library
			$('#wpbody').on('click', '.insert-media-url', function(e) {
				e.preventDefault();
				$(media.open);

				// Get input fields
				var attachmentIDField = $(this).data('id');
				var attachmentURLField = $(this).data('editor');
				
				// Open media manager
				var mediaManager = wp.media({
					frame:    'post',
					title:    'Insert Media',
					multiple: false
				});
				mediaManager.open();
				
				// When the insert button is clicked in the media manager, store
				// the attachment ID and/or image
				mediaManager.on('insert', function(selection) {
					selection.map(function(attachment) {
						// Store attachment ID
						if (typeof attachmentIDField != 'undefined') {
							$('input#'+attachmentIDField).val(attachment.get('id'))
						}
						
						// Store attachment URL
						if (typeof attachmentURLField != 'undefined') {
							$('input#'+attachmentURLField).val(attachment.get('url'));	
						}

						// Display Image
						$('span.envira-watermarking-image').html('<img src="' + attachment.get('url') + '" />');
					});
				});	
			});
			
			// Delete
			$('a.delete').click(function(e) {
				// Check delete button corresponds to a media uploader
				var editor = $(this).data('editor');
				if (!editor) return;
				
				// Remove image + image value in hidden field
				$('input#'+editor).val('').trigger('change');
				if ($('span.'+editor).length > 0) {
					$('span.'+editor).html('');
				}
				
				// Hide delete button
				$(this).addClass('hidden');
			});
		}
	};

	// Start init process
	$(media.init);
}(jQuery));