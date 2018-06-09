jQuery( document ).ready( function( $ ) {

	/**
	* Show or hide Envira Proofing Fields (Quantity, Size) for each image
	* based on the image's checkbox state
	*
	* @since 1.0
	*
	* @param obj checkbox_element Checkbox Element
	*/

	var the_image_ids = [],
		the_images    = []
		object        = false,
		current       = false,
		instance      = false;

	if ( Cookies.get('envira_proof_image_ids') !== undefined && Cookies.get('envira_proof_image_ids') != '' ) {
		var image_ids_json = Cookies.get('envira_proof_image_ids');
		the_image_ids = $.parseJSON( image_ids_json );
		console.log ('starting with ids:');
		console.log ( the_image_ids );
	}

	if ( Cookies.get('envira_proof_images') !== undefined && Cookies.get('envira_proof_images') != '' ) {
		var images_json = Cookies.get('envira_proof_images');
		the_images = $.parseJSON( images_json );
		console.log ('starting with images:');
		console.log ( the_images );
	}


	var envira_proofing_toggle_fields = function( checkbox_element ) {

		// console.log ('----');
		// console.log (checkbox_element);
		// console.log ('----');

		if ( $( checkbox_element ).prop( 'checked' ) ) {

			$( '.envira-proofing-fields', $( checkbox_element ).parent() ).fadeIn();
			$( checkbox_element ).closest( '.envira-gallery-item-inner' ).addClass( 'envira-proofing-selected' );

			// Add to summary
			var image = $( 'img', $( checkbox_element ).closest( '.envira-gallery-item-inner' ) ).attr( 'src' );
			var image_id = $( 'img', $( checkbox_element ).closest( '.envira-gallery-item-inner' ) ).data( 'envira-item-id' );
			var count = $( "div.envira-proofing-summary-box-inner div.images-inner div.image img[src='" + image + "'] ").length;
			if ( count == 0 ) {
				// prevent duplicates
				$( 'div.envira-proofing-summary-box-inner div.images-inner' ).append( '<div class="image" data-src="' + image + '"><img src="' + image + '" /></div>' );
			}

			var the_image_id = JSON.stringify(image_id);
			var the_image = JSON.stringify(image);

			if ($.inArray(the_image_id, the_image_ids) === -1) {
				the_image_ids.push(the_image_id);
			}
			image_ids_json = JSON.stringify(the_image_ids);
			if ( Cookies.get('envira_proof_image_ids') === undefined || Cookies.get('envira_proof_image_ids') == '' ) {
				Cookies.remove('envira_proof_image_ids', { path: '/' });
				Cookies.set('envira_proof_image_ids', image_ids_json, { expires: 2, path: '/' });
			} else {
				Cookies.set('envira_proof_image_ids', image_ids_json, { expires: 2, path: '/' });
			}

			if ($.inArray(the_image, the_images) === -1) {
				the_images.push(the_image);
			}
			images_json = JSON.stringify(the_images);
			if ( Cookies.get('envira_proof_images') === undefined || Cookies.get('envira_proof_images') == '' ) {
				Cookies.remove('envira_proof_images', { path: '/' });
				Cookies.set('envira_proof_images', images_json, { expires: 2, path: '/' });
			} else {
				Cookies.set('envira_proof_images', images_json, { expires: 2, path: '/' });
			}

			// If the summary bar isn't displayed, slide it in
			if ( $( 'div.envira-proofing-summary-box-inner' ).css('bottom') != '0' ) {
				$( 'div.envira-proofing-summary-box-inner' ).animate({
					bottom: '0'
				});
			}

		} else {

			var image = $( 'img', $( checkbox_element ).closest( '.envira-gallery-item-inner' ) ).attr( 'src' );
			var image_id = $( 'img', $( checkbox_element ).closest( '.envira-gallery-item-inner' ) ).data( 'envira-item-id' );

			var the_image_id = JSON.stringify(image_id);
			var the_image = JSON.stringify(image);

			the_image_ids.splice($.inArray(the_image_id, the_image_ids),1);
			image_ids_json = JSON.stringify(the_image_ids);
			the_images.splice($.inArray(the_image, the_images),1);
			images_json = JSON.stringify(the_images);

			Cookies.remove('envira_proof_image_ids', { path: '/' });
			Cookies.set('envira_proof_image_ids', image_ids_json, { expires: 2, path: '/' });

			Cookies.remove('envira_proof_images', { path: '/' });
			Cookies.set('envira_proof_images', images_json, { expires: 2, path: '/' });


			$( '.envira-proofing-fields', $( checkbox_element ).parent() ).fadeOut().promise().done(function() {

				if ( $( envira_container ).hasClass( 'enviratope' ) ) {

		            envira_container.enviraImagesLoaded()
		                .done(function() {
		                    envira_container.enviratope('layout');
		                })
		                .progress(function() {
		                    envira_container.enviratope('layout');
		                });

				}

			});
			$( checkbox_element ).closest( '.envira-gallery-item-inner' ).removeClass( 'envira-proofing-selected' );

			// If isotope is enabled, relayout so the fields display
			var envira_container    = $( checkbox_element ).closest( '.envira-gallery-public' );

			/* if ( $( envira_container ).hasClass( 'enviratope' ) ) {

	            envira_container.enviraImagesLoaded()
	                .done(function() {
	                    envira_container.enviratope('layout');
	                })
	                .progress(function() {
	                    envira_container.enviratope('layout');
	                });

			} */

			// Remove from summary
			var image = $( 'img', $( checkbox_element ).closest( '.envira-gallery-item-inner' ) ).attr( 'src' );
			$( 'div.envira-proofing-summary-box-inner div.images-inner div[data-src="' + image + '"]' ).remove();
		}

		// If the summary bar has no images in it, slide it out
		if ( $( 'div.envira-proofing-summary-box-inner div.images div.image' ).length == 0 ) {
			$( 'div.envira-proofing-summary-box-inner' ).animate({
				bottom: '-80px'
			});
		}

	}


	/**
	* Quantity Fields - Update Cookie
	*/
	$( document ).on( 'change', '.envira-gallery-wrap .envira-proofing-fields input[type=number]', function() {

		var a = $(this).attr('name'),
			matches = a.match(/\[(.*)\]{1}\[(.*)\]{1}\[(.*)\]{1}/),
			image_id = matches[2],
			size = matches[3],
			quantity = $(this).val(),
			image_fields_json = false,
			image_fields = Array(0);

			if ( Cookies.get('envira_proof_image_fields') !== undefined && Cookies.get('envira_proof_image_ids') != '' ) {
				image_fields_json = Cookies.get('envira_proof_image_fields');
				image_fields = $.parseJSON( image_fields_json );
			}

			var obj = {};
			var obj_to_insert = {};
			obj_to_insert['size'] = size;
			obj_to_insert['quantity'] = quantity;
			obj[image_id] = obj_to_insert;

			if ( image_fields_json !== false ) {
				// var image_fields = $.parseJSON( image_fields_json );
				// delete image_fields[image_id];
				image_fields.push(obj);
			} else {
				image_fields.push(obj);
			}

			Cookies.remove('envira_proof_image_fields', { path: '/' });
			Cookies.set('envira_proof_image_fields', image_fields, { expires: 2, path: '/' });

	} );

	/******* FANCYBOX *********/
    $(document).on( 'envirabox_api_before_show', function( e, o, i, c ){

		object = false,
		instance = false,
		current = false;

	} );

    $(document).on( 'envirabox_api_after_show', function( e, o, i, c ){

		object = o,
		instance = i,
		current = c;

	} );


	$( document ).on( 'change', '.envirabox-inner .envira-proofing-fields input[type=number]', function() {

		var actual_enviraItemId = current.enviraItemId;
		var a = $(this).attr('name'),
			matches = a.match(/\[(.*)\]{1}\[(.*)\]{1}/),
			image_id = actual_enviraItemId,
			size = matches[2],
			quantity = $(this).val(),
			image_fields_json = false,
			image_fields = Array(0);

			console.log('ON CHANGE actual_enviraItemId');
			console.log(actual_enviraItemId);
			console.log('image_id');
			console.log(image_id);

			if ( Cookies.get('envira_proof_image_fields') !== undefined && Cookies.get('envira_proof_image_ids') != '' ) {
				image_fields_json = Cookies.get('envira_proof_image_fields');
				image_fields = $.parseJSON( image_fields_json );
			}

			var obj_parent = {};
			var obj_to_insert = {};
			obj_to_insert['size'] = size;
			obj_to_insert['quantity'] = quantity;
			obj_parent[image_id] = obj_to_insert;

			if ( image_fields !== false ) {

					var foundit = false;

					// go through the object to make sure we aren't duplicating an image/size

					$.each( image_fields, function( key, value ) {
						$.each( value, function( key1, value1 ) {
						  // key 1 would be the id, like 12345
						  if (key1 == image_id && value1.size == size) {
							  foundit = true;
							  value1.quantity = quantity;
						  }
						});
					});

					if ( foundit === false ) {
						image_fields.push(obj_parent)
					}

			}


			console.log('image_fields');
			console.log(image_fields);

			Cookies.remove('envira_proof_image_fields', { path: '/' });
			Cookies.set('envira_proof_image_fields', image_fields, { expires: 2, path: '/' });

	} );
	/**
	* Summary Box - Load Images From Cookie
	*/

	var image_ids_json = Cookies.get('envira_proof_image_ids');

	if ( image_ids_json !== undefined ) {
		// console.log ('image_ids_json');
		// console.log (image_ids_json);

		var image_ids = $.parseJSON( image_ids_json );

		// If the summary bar isn't displayed, slide it in
		if ( $( 'div.envira-proofing-summary-box-inner' ).css('bottom') != '0' ) {
			$( 'div.envira-proofing-summary-box-inner' ).animate({
				bottom: '0'
			});
		}

		$.each( image_ids, function( key, image_id ) {
			var the_image           = $(document).find('[data-envira-item-id="' + image_id + '"]');
			var the_image_url       = the_image.attr('src');
			var the_checkbox        = the_image.closest('.envira-gallery-item-inner').find('input.envira-proofing-select-image' );
			var the_proofing_fields = the_checkbox.parent().find('.envira-proofing-fields');
			// console.log (the_image);
			// console.log (the_image_url);
			// console.log (the_checkbox);
			//$('#envira_proofing_images_9854').attr('checked','checked');
			var count = $( "div.envira-proofing-summary-box-inner div.images-inner div.image img[src='" + the_image_url + "'] ").length;
			if ( count == 0 ) {
				// console.log('checking off ' + the_image_url);
				// prevent duplicates
				the_checkbox.attr('checked','checked');
				// show size/quantity box if it's checked, assuming it exists
				if ( the_proofing_fields !== undefined ) {
					console.log ( the_proofing_fields );
					the_proofing_fields.css('display','block');
				}
				// envira_proofing_toggle_fields( the_checkbox );
			}
		});

	} else {
		// just make sure all checkboxes on the page are unchecked
		$('input.envira-proofing-select-image' ).prop( "checked", false );
	}

	var proof_images_json = Cookies.get('envira_proof_images');

	if ( proof_images_json !== undefined ) {
		// console.log ('proof_images_json');
		// console.log (proof_images_json);

		var images = $.parseJSON( proof_images_json );

		$.each( images, function( key, image_url ) {
			// var the_image = $(document).find('[data-envira-item-id="' + image_id + '"]');
			// var the_image_url = the_image.attr('src');
			// var the_checkbox = the_image.closest('.envira-gallery-item-inner').find('input.envira-proofing-select-image' );
			// console.log (the_image);
			// console.log (the_image_url);
			// console.log (the_checkbox);
			//$('#envira_proofing_images_9854').attr('checked','checked');

			image_url = image_url.replace(/\"/g, "");

			var count = $( "div.envira-proofing-summary-box-inner div.images-inner div.image img[src='" + image_url + "'] ").length;
			if ( count == 0 ) {
				// console.log('appending ' + image_url);
				$( 'div.envira-proofing-summary-box-inner div.images-inner' ).append( '<div class="image" data-src="' + image_url + '"><img src="' + image_url + '" /></div>' );
			}
		});

	}

	// Show/hide proofing fields on select/deselect
	// $( 'input.envira-proofing-select-image' ).each( function() {
	//  envira_proofing_toggle_fields( $( this ) );
	// } );
	$( document ).on( 'change', 'input.envira-proofing-select-image', function() {

		var completed = envira_proofing_toggle_fields( $( this ) );

		// If isotope is enabled, relayout so the fields display
		var envira_container    = $( this ).closest( '.envira-gallery-public' ),
			envira_container_arr= $( envira_container ).attr( 'id' ).split( '-' ),
			envira_gallery_id   = envira_container_arr[2];

		if ( $( envira_container ).hasClass( 'enviratope' ) ) {
			$( envira_container ).enviratope('layout');
		}
	} );

	// Show/hide proofing fields on lightbox select/deselect
	$( document ).on( 'change', 'input.envira-proofing-lightbox-select-image', function() {

		// If isotope is enabled, relayout so the fields display
		var envira_image    = $( this ).closest( '.envirabox-image' ),
			envira_gallery_id   = $( envira_image ).data( 'envira-gallery-id' ),
			envira_container = $( '#envira-gallery-' + envira_gallery_id );

		if ( $( this ).prop( 'checked' ) ) {
			$( '.envira-proofing-fields', $( this ).parent().parent() ).fadeIn();
		} else {
			$( '.envira-proofing-fields', $( this ).parent().parent() ).fadeOut();
			if ( $( envira_container ).hasClass( 'enviratope' ) ) {
				$( envira_container ).enviratope('layout');
			}
		}
	} );

	/**
	* Summary Box
	*/
	$( 'div.envira-proofing-summary-box-inner div.images-inner' ).mousewheel(function(event, delta) {
		this.scrollLeft -= (delta * 10);
		event.preventDefault();
	});

	/**
	* Summary Box: Save/Submit Buttons
	*/
	$( 'div.envira-proofing-summary-box-inner div.buttons button' ).on( 'click', function(e) {
		e.preventDefault();

		// Find form
		var gallery_id  = $( this ).closest( 'div.envira-proofing-summary-box' ).data( 'envira-id' ),
			form        = $( 'form[data-envira-id="' + gallery_id + '"]' );

		// Update hidden fields in form from cookies
		form.find('input#envira_proofing_selected_images').val( Cookies.get('envira_proof_images') );
		form.find('input#envira_proofing_selected_images_ids').val( Cookies.get('envira_proof_image_ids') );
		form.find('input#envira_proofing_selected_images_fields').val( Cookies.get('envira_proof_image_fields') );

		// Simulate click on the equivalent form button to save/submit the order
		$( 'input[name=' + $( this ).attr( 'name' ) + ']', $( form ) ).trigger( 'click' );
	} );


    /******* LIGHTBOX *********/

    $(document).on( 'envirabox_api_after_show', function( e, obj, instance, current ){

		console.log('obj');
        console.log(obj);
		console.log('current');
        console.log(current);

        if ( obj.get_config( 'proofing' ) === false || obj.get_config( 'proofing_lightbox' ) === false ) {
            return;
        }

		console.log('made it');

		$('.envirabox-container').addClass('envirabox-proofing');

		var quantity_enabled    = obj.data.proofing_quantity_enabled,
			sizes_enabled       = obj.data.proofing_size_enabled,
			sizes               = obj.data.proofing_sizes,
			output              = false;
			item_id             = current.enviraItemId;
			proofing_add_to_order_label = ( obj.data.proofing_submit_button_label ) ? obj.data.proofing_submit_button_label : 'Add To Order';

		console.log('quantity_enabled');
        console.log(quantity_enabled);
		console.log('sizes_enabled');
        console.log(sizes_enabled);
		console.log('sizes');
        console.log(sizes);
		console.log('sizes length');
        console.log(Object.keys(sizes).length);

        // Build HTML for Lightbox
        output = '<form action="#" method="post" class="envira-proofing-form envira-proofing-form-lightbox">' +
            '<div class="envira-proofing-field">' +
                '<label for="envira_proofing_lightbox_quantity">' + proofing_add_to_order_label + '</label>' +
                '<input type="checkbox" name="envira_proofing_lightbox[images]" value="1" class="envira-proofing-lightbox-select-image envira-proofing-checkbox" />' +
            '</div>' +
            '<div class="envira-proofing-fields">';

        if ( quantity_enabled === 1 && sizes_enabled === 1 && Object.keys(sizes).length > 0 ) {
            console.log('choice 1');
            // Output sizes with quantity beside each
            for (prop in sizes) {
                console.log( sizes[prop] );
                output += '<div class="envira-proofing-field">' +
                    '<label for="envira_proofing_lightbox_quantity_' + sizes[prop].slug + '">' + sizes[prop].name + '</label>' +
                    '<input type="number" name="envira_proofing_lightbox[quantities][' + sizes[prop].name + ']" id="envira_proofing_lightbox_quantity_' + sizes[prop].slug + '" min="0" class="envira-proofing-number" />' +
                '</div>';
            }
        } else if ( quantity_enabled && !sizes_enabled ) {
            console.log('choice 2');
            // Output quantity field only
            output += '<div class="envira-proofing-field">' +
                '<label for="envira_proofing_lightbox_quantity">Quantity</label>' +
                '<input type="number" name="envira_proofing_lightbox[quantities]" id="envira_proofing_lightbox_quantity" min="0" value="0" class="envira-proofing-number" />' +
            '</div>';
        } else if ( quantity_enabled === 0 && sizes_enabled === 1 && Object.keys(sizes).length > 0 ) {
            console.log('choice 3');
            // Output sizes only
            for (prop in sizes) {
                console.log( sizes[prop] );
                output += '<div class="envira-proofing-field">' +
                     '<label for="envira_proofing_lightbox_quantity_' + sizes[prop].slug + '">' + sizes[prop].name + '</label>' +
                     '<input type="checkbox" name="envira_proofing_lightbox[quantities][' + sizes[prop].name + ']" id="envira_proofing_lightbox_quantity_' + sizes[prop].slug + '" value="1" class="envira-proofing-checkbox" />' +
                '</div>';
            }
        } else {
            console.log('choice 4');
            // return;
        }

        // Close .envira-proofing-fields
        output += '</div></form>';

        $('.envirabox-stage .envira-proofing input').off('change.envira_proofing_lightbox');
        $('.envirabox-stage .envira-proofing').remove();

        $('.envirabox-stage .envirabox-slide--current .envirabox-image-wrap').append('<div class="envira-proofing"></div>');
        $('.envirabox-stage .envira-proofing').append(output);

        envira_proofing_populate_lightbox_fields(item_id);
        $('.envirabox-stage .envira-proofing input').on('change.envira_proofing_lightbox', function() {
            envira_proofing_populate_gallery_fields(item_id);
        });

    });


	/**
	* Populate Lightbox Proofing Fields with Gallery Proofing Field values
	*
	* @since 1.0
	*
	* @param int item_id Gallery Image ID
	*/
	function envira_proofing_populate_lightbox_fields( item_id ) {

		$('.envirabox-proofing form.envira-proofing-form-lightbox input').each(function() {

	        var field_name = $(this).attr('name'),
	            field_name_parts = field_name.split( '][' );

	        // Find non-lightbox form field and use its value
	        switch ( $(this).attr('type') ) {
	            /**
	            * Checkbox
	            */
	            case 'checkbox':
	                if ( field_name_parts.length == 1 ) {
						var is_checked = $( 'input[name="envira_proofing[images][' + item_id + ']"]' ).prop( 'checked' );
	                }
	                if ( field_name_parts.length == 2) {
	                    var size = field_name_parts[1].replace(']', ''),
	                        is_checked = $( 'input[name="envira_proofing[quantities][' + item_id + '][' + size + ']"]' ).prop( 'checked' );
	                }

	                $( this ).prop( 'checked', is_checked );
	                break;

	            /**
	            * All other inputs
	            */
	            default:
	                if ( field_name_parts.length == 1 ) {
						var field_value = $( 'input[name="envira_proofing[quantities][' + item_id + ']"]' ).val();
	                }
	                if ( field_name_parts.length == 2) {
	                    var size = field_name_parts[1].replace(']', ''),
	                        field_value = $( 'input[name="envira_proofing[quantities][' + item_id + '][' + size + ']"]' ).val();
	                }

	                $( this ).val( field_value );
	                break;

	        }

	    });

	}



	/**
	* Populate Gallery Proofing Fields with Lightbox Proofing Field values
	*
	* @since 1.0
	*/
	function envira_proofing_populate_gallery_fields( item_id ) {

		jQuery(document).ready(function($) {
			$('.envirabox-proofing form.envira-proofing-form-lightbox input').each(function() {

		        var field_name = $(this).attr('name'),
		            field_name_parts = field_name.split( '][' );

		        // Find non-lightbox form field and use its value
		        switch ( $(this).attr('type') ) {
		            /**
		            * Checkbox
		            */
		            case 'checkbox':
		                var is_checked = $( this ).prop( 'checked' );

		                if ( field_name_parts.length == 1 ) {
							$( 'input[name="envira_proofing[images][' + item_id + ']"]' ).prop( 'checked', is_checked ).trigger( 'change' );
		                }
		                if ( field_name_parts.length == 2) {
		                    var size = field_name_parts[1].replace(']', '');
		                    $( 'input[name="envira_proofing[quantities][' + item_id + '][' + size + ']"]' ).prop( 'checked', is_checked );
		                }

		                break;

		            /**
		            * All other inputs
		            */
		            default:
		                if ( field_name_parts.length == 1 ) {
							var field_value = $( this ).val();
							$( 'input[name="envira_proofing[quantities][' + item_id + ']"]' ).val( field_value )
		                }
		                if ( field_name_parts.length == 2) {
		                    var size = field_name_parts[1].replace(']', ''),
		                        field_value = $( this ).val();
		                    $( 'input[name="envira_proofing[quantities][' + item_id + '][' + size + ']"]' ).val( field_value );
		                }

		                break;
		        }

		    });
	    });

	}


} );