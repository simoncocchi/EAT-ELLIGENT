<?php
/**
 * WCFMvm plugin shortcode
 *
 * Plugin Membership Shortcode output
 *
 * @author 		WC Lovers
 * @package 	wcfmvm/includes/shortcode
 * @version   1.0.0
 */
 
class WCFM_Vendor_Membership_Shortcode {
	
	public function __construct() {
		
	}

	/**
	 * Output the WC Frontend Manager Membership shortcode.
	 *
	 * @access public
	 * @param array $atts
	 * @return void
	 */
	static public function output( $attr ) {
		global $WCFM, $WCFMvm, $wp, $WCFM_Query;
		$WCFM->nocache();
		
		echo '<div id="wcfm-main-contentainer"> <div id="wcfm-content"><div class="wcfm-membership-wrapper"> ';
		
		if ( isset( $wp->query_vars['page'] ) || is_wcfm_membership_page() ) {
			
			$steps = wcfm_membership_registration_steps();
			$current_step = wcfm_membership_registration_current_step();
				
			if( wcfm_is_allowed_membership() || current_user_can( 'administrator' ) || current_user_can( 'shop_manager' ) ) {
				
				if( WC()->session && ( !WC()->session->get( 'wcfm_membership' ) || !WC()->session->get( 'wcfm_membership_free_registration' ) ) ) {
					$WCFMvm->template->get_template( 'vendor_membership_steps.php' );
				} elseif( WC()->session && WC()->session->get( 'wcfm_membership' ) && WC()->session->get( 'wcfm_membership_free_registration' ) ) {
					WC()->session->__unset( 'wcfm_membership_free_registration' );
				}
				
				/*if( !isset( $_SESSION['wcfm_membership'] ) || !isset( $_SESSION['wcfm_membership']['free_registration'] ) ) {
					$WCFMvm->template->get_template( 'vendor_membership_steps.php' );
				} elseif( isset( $_SESSION['wcfm_membership'] ) && isset( $_SESSION['wcfm_membership']['free_registration'] ) ) {
					unset( $_SESSION['wcfm_membership']['free_registration'] );
				}*/
				
				switch( $current_step ) {
					case 'registration':
						$WCFMvm->template->get_template('vendor_registration.php');
					break;
					
					case 'payment':
						$WCFMvm->template->get_template('vendor_payment.php');
					break;
					
					case 'thankyou':
						$WCFMvm->template->get_template('vendor_thankyou.php');
					break;
					
					default:
						$WCFMvm->template->get_template('vendor_membership.php');
					break;
				}
			} else {
				switch( $current_step ) {
					case 'thankyou':
						$WCFMvm->template->get_template( 'vendor_membership_steps.php' );
						$WCFMvm->template->get_template('vendor_thankyou.php');
					break;
					
					default:
						$WCFMvm->template->get_template( 'vendor_membership_block.php' );
					break;
				}
			}
		}
		
		echo '</div></div></div>';
	}
}
