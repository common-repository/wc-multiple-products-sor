jQuery(document).ready(function($){

	$('.check-column input').mousedown(function(e) {
	    e.preventDefault();
	    $(this).prop('checked', !$(this).prop('checked'));
	    jQuery(this).parents('tr').addClass('selected');
	    return false;
	});

	var ajax = false;

	jQuery('table.wp-list-table.posts tbody').multisortable({
		items: 'tr',
		selectedClass: "selected",
		click: function(event,uitem){},
		stop: function(event,suitem){
			console.log("I've been sorted.");
			var uitem = suitem.item;

			$( 'table.widefat tbody th, table.widefat tbody td' ).css( 'cursor', 'default' );
			$( 'table.widefat tbody' ).sortable( 'disable' );

			var prevAttrid = uitem.prevAll("tr").not(".selected").first().attr('id');
			if(prevAttrid){
				var prevId = prevAttrid.replace("post-", "");
			}
			var lastAttrid = uitem.nextAll("tr").not(".selected").first().attr('id');
			if(lastAttrid){
				var lastId = lastAttrid.replace("post-", "");
			}
			var nextAttrid = uitem.next().attr('id');
			if(nextAttrid){
				var nextId = nextAttrid.replace("post-", "");
			}
			var myAttrid = uitem.attr('id');
			if(myAttrid){
				var myId = myAttrid.replace("post-", "");
			}
			var multiSorting = [];
			var finalProductIds = [];
			var fEl = {'previd':prevId,'id': myId,'nextid': nextId };

			multiSorting.push(fEl);
			finalProductIds.push(myId);
		
			uitem.siblings('.selected').each(function(){
				var sortedItem = $(this);
				var myAttrid = sortedItem.attr('id')
				if(myAttrid){
					var myId = myAttrid.replace("post-", "");
				}
				var prevAttrid = sortedItem.prev().attr('id');
				if(prevAttrid){
					var prevId = prevAttrid.replace("post-", "");
				}
				var nextAttrid = sortedItem.next().attr('id');
				if(nextAttrid){
					var nextId = nextAttrid.replace("post-", "");
				}
				var nMs = {'previd':prevId,'id': myId,'nextid': nextId };
				multiSorting.push(nMs);
				finalProductIds.push(myId);
			});

			arrayLenght = multiSorting.length-1;
			var finalPrevId = multiSorting[0]['previd'];
			var finalNextId = multiSorting[arrayLenght]['nextid'];
			var myAjaxUrl = woocommerce_admin_meta_boxes.ajax_url;

			$.each( finalProductIds, function( key, value ) {
				$('tr#post-'+value).find( '.check-column input' )
					.hide()
					.after( '<img alt="processing" src="images/wpspin_light.gif" class="waiting" style="margin-left: 6px;" />' );
			});

			var postData = {
				action: 'woocommerce_custom_product_sorting',
				previd: finalPrevId,
				nextid: finalNextId,
				productIds: finalProductIds
			};

			ajax = $.ajax({
				type: "POST",
				url: myAjaxUrl,
				data: postData,
				success: function (response) {
					//console.log(response);
					$.each( response, function( key, value ) {
						$( '#inline_' + key + ' .menu_order' ).html( value );
					});
					$.each( finalProductIds, function( key, value ) {
						$('tr#post-'+value).find( '.check-column input' ).show().siblings( 'img' ).remove();
					});
					$( 'table.widefat tbody th, table.widefat tbody td' ).css( 'cursor', 'move' );
					$( 'table.widefat tbody' ).sortable( 'enable' );
				}
			});

		}
	});
});
