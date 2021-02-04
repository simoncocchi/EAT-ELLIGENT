/**
 * Dismisses plugin notices
 *
 */
( function( $ ) {
	"use strict";
	$( document ).ready( function() {
		$( '.notice.is-dismissible.login-logout-register-menu .notice-dismiss').on( 'click', function() {

			$.ajax( {
				url: llrm.ajax_url,
				data: {
					action: 'llrm_notice_dismiss'
				}
			} );

		} );
	} );
} )( jQuery );