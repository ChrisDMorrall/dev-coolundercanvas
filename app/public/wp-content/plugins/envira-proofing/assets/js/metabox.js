jQuery(document).ready(function($) {

	/**
	* Add Size
	*/
	$( '#envira-proofing-sizes-box a.add' ).on( 'click', function( e ) {

		e.preventDefault();

		// Setup vars
		var sizes_container 	= $( 'table#envira-proofing-sizes tbody' ),
			size 				= $( 'tr.hidden', $( sizes_container ) ).html();

		// Clone size
		$( sizes_container ).append( '<tr class="sortable">' + size + '</tr>' );

		// Reload sortable
		$( sizes_container ).sortable( 'refresh' );

	});

	/**
	* Reorder Sizes
	*/
	$( 'table#envira-proofing-sizes tbody' ).sortable({
		containment: 'parent',
		items: '.sortable',
	});

	/**
	* Disable Click on Reorder Size Move Icon
	*/
	$( 'table#envira-proofing-sizes tbody a.move' ).on( 'click', function(e) {
		e.preventDefault();
	} );

	/**
	* Delete Size
	*/
	$( 'table#envira-proofing-sizes tbody' ).on( 'click', 'a.delete', function( e ) {

		e.preventDefault();

		// Confirm deletion
		var result = confirm( envira_proofing_metabox.delete_size );
		if ( ! result ) {
			return;
		}

		// Get size and container
		var size 				= $( this ).closest( 'tr.sortable' ),
			sizes_container 	= $( size ).closest( 'table' );

		// Delete size
		$( size ).remove();

	});

	/**
	* Unlock Order
	*/
	$( 'a.envira-proofing-unlock-order' ).on( 'click', function( e ) {

		e.preventDefault();

		// Confirm clear
		var result = confirm( envira_proofing_metabox.unlock_order );
		if ( ! result ) {
			return;
		}

		// AJAX call to remove metadata
		$.post(
            envira_gallery_metabox.ajax,
            {
                action:  'envira_proofing_unlock_order',
                post_id: envira_gallery_metabox.id,
                email: 	 $( this ).data( 'email' ),
                nonce:   envira_proofing_metabox.nonce
            },
            function( response ) {
                // Unlock order
            	$( 'a.envira-proofing-unlock-order' ).remove();
            },
            'json'
        );

	} );

	/**
	* Clear Order Details
	*/
	$( 'a.envira-proofing-clear-order' ).on( 'click', function( e ) {

		e.preventDefault();

		var button = $( this );

		// Confirm clear
		var result = confirm( envira_proofing_metabox.clear_order );
		if ( ! result ) {
			return;
		}

		// AJAX call to remove metadata
		$.post(
            envira_gallery_metabox.ajax,
            {
                action:  'envira_proofing_clear_order',
                post_id: envira_gallery_metabox.id,
                email: 	 $( this ).data( 'email' ),
                nonce:   envira_proofing_metabox.nonce
            },
            function( response ) {
                // Clear order   
                $( button ).closest( 'div.envira-proofing-order' ).remove();
            },
            'json'
        );

	} );

});