/**
* Handles showing and hiding fields conditionally
*/
jQuery( document ).ready( function( $ ) {

	// Show/hide elements as necessary when a conditional field is changed
	$( '#envira-gallery-settings input:not([type=hidden]), #envira-gallery-settings select' ).conditions( 
		[

			{	// Gallery Elements
				conditions: {
					element: '[name="_envira_gallery[watermarking]"]',
					type: 'checked',
					operator: 'is'
				},
				actions: {
					if: [
						{
							element: '#envira-watermarking-image-box, #envira-watermarking-position-box, #envira-watermarking-margin-box, #envira-watermarking-watermark-existing-images-box',
							action: 'show'
						}
					],
					else: [
						{
							element: '#envira-watermarking-image-box, #envira-watermarking-position-box, #envira-watermarking-margin-box, #envira-watermarking-watermark-existing-images-box',
							action: 'hide'
						}
					]
				}
			}

		]
	);

} );