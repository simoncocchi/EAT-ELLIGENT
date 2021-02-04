jQuery(document).ready(function($) {
		
	$wcfm_memberships_table = $('#wcfm-memberships').DataTable( {
		"processing": true,
		"serverSide": true,
		"responsive": true,
		"language"  : $.parseJSON(dataTables_language),
		"columns"   : [
										{ responsivePriority: 1 },
										{ responsivePriority: 5 },
										{ responsivePriority: 2 },
										{ responsivePriority: 3 },
										{ responsivePriority: 2 },
										{ responsivePriority: 4 },
								],
		"columnDefs": [ { "targets": 0, "orderable" : false }, 
									  { "targets": 1, "orderable" : false }, 
										{ "targets": 2, "orderable" : false },
										{ "targets": 3, "orderable" : false },
										{ "targets": 4, "orderable" : false },
										{ "targets": 5, "orderable" : false },
									],
		'ajax': {
			"type"   : "POST",
			"url"    : wcfm_params.ajax_url,
			"data"   : function( d ) {
				d.action       = 'wcfm_ajax_controller',
				d.controller   = 'wcfm-memberships'
			},
			"complete" : function () {
				initiateTip();
				
				// Fire wcfm-memberships table refresh complete
				$( document.body ).trigger( 'updated_wcfm-memberships' );
			}
		}
	} );
	
	// Delete Group
	$( document.body ).on( 'updated_wcfm-memberships', function() {
		$('.wcfm_membership_restrict_delete').each(function() {
			$(this).click(function(event) {
				event.preventDefault();
				alert("You are not allowed to delete this 'Membership'.\nFirst change associate vendors' membership ...");
				return false;
			});
		});
		
		$('.wcfm_membership_delete').each(function() {
			$(this).click(function(event) {
				event.preventDefault();
				var rconfirm = confirm("Are you sure and want to delete this 'Membership'?\nYou can't undo this action ...");
				if(rconfirm) deleteWCFMMembership($(this));
				return false;
			});
		});
	});
	
	function deleteWCFMMembership(item) {
		jQuery('#wcfm-memberships_wrapper').block({
			message: null,
			overlayCSS: {
				background: '#fff',
				opacity: 0.6
			}
		});
		var data = {
			action       : 'delete_wcfm_membership',
			membershipid : item.data('membershipid')
		}	
		jQuery.ajax({
			type:		'POST',
			url: wcfm_params.ajax_url,
			data: data,
			success:	function(response) {
				if($wcfm_memberships_table) $wcfm_memberships_table.ajax.reload();
				jQuery('#wcfm-memberships_wrapper').unblock();
			}
		});
	}
} );