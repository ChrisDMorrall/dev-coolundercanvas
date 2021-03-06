/**
* Handles showing and hiding fields conditionally
*/
jQuery( document ).ready( function( $ ) {

	// Show/hide elements as necessary when a conditional field is changed
	$( '#envira-gallery-settings input:not([type=hidden]), #envira-gallery-settings select' ).conditions( 
		[

			{	// Proofing Elements Independant of Theme
				conditions: [
					{
						element: '[name="_envira_gallery[proofing]"]',
						type: 'checked',
						operator: 'is'
					}
				],
				actions: {
					if: {
						element: '#envira-proofing-edit-box, #envira-proofing-delete-box, #envira-proofing-multiple-enabled-box, #envira-proofing-save-notes-button-label-box, #envira-proofing-size-enabled-box, #envira-proofing-add-to-order-label-box, #envira-proofing-save-button-label-box, #envira-proofing-submit-button-label-box, #envira-proofing-submitted-message-box, #envira-proofing-email-box, #envira-proofing-email-subject-box, #envira-proofing-email-message-box, #envira-proofing-edit-button-label-box, #envira-proofing-delete-button-label-box, #envira-proofing-quantity-enabled-box',
						action: 'show'
					},
					else: {
						element: '#envira-proofing-edit-box, #envira-proofing-delete-box, #envira-proofing-multiple-enabled-box, #envira-proofing-save-notes-button-label-box, #envira-proofing-size-enabled-box, #envira-proofing-add-to-order-label-box, #envira-proofing-save-button-label-box, #envira-proofing-submit-button-label-box, #envira-proofing-submitted-message-box, #envira-proofing-email-box, #envira-proofing-email-subject-box, #envira-proofing-email-message-box, #envira-proofing-edit-button-label-box, #envira-proofing-delete-button-label-box, #envira-proofing-quantity-enabled-box', 
						action: 'hide'
					}
				}
			},
			{	// Proofing Elements Independant of Theme
				conditions: [
					{
						element: '[name="_envira_gallery[proofing]"]',
						type: 'checked',
						operator: 'is'
					},
					{
						element: '[name="_envira_gallery[proofing_multiple_enabled]"]',
						type: 'checked',
						operator: 'is'
					}
				],
				actions: {
					if: {
						element: '#envira-proofing-name-enabled-box, #envira-proofing-email-box-position-box, #envira-proofing-hide-until-email',
						action: 'show'
					},
					else: {
						element: '#envira-proofing-name-enabled-box, #envira-proofing-email-box-position-box, #envira-proofing-hide-until-email',
						action: 'hide'
					}
				}
			},
			{	// Proofing Elements Independant of Theme
				conditions: [
					{
						element: '[name="_envira_gallery[proofing]"]',
						type: 'checked',
						operator: 'is'
					},
					{
						element: '[name="_envira_gallery[proofing_size_enabled]"]',
						type: 'checked',
						operator: 'is'
					}
				],
				actions: {
					if: {
						element: '#envira-proofing-sizes-box',
						action: 'show'
					},
					else: {
						element: '#envira-proofing-sizes-box',
						action: 'hide'
					}
				}
			}

		]
	);

} );