<?php
/**
 * WCFM plugin view
 *
 * WCFMgs Memberships Template
 *
 * @author 		WC Lovers
 * @package 	wcfmvm/templates
 * @version   1.0.0
 */

global $WCFM, $WCFMvm;

$wcfm_membership_options = get_option( 'wcfm_membership_options', array() );
$membership_feature_lists = array();
if( isset( $wcfm_membership_options['membership_features'] ) ) $membership_feature_lists = $wcfm_membership_options['membership_features'];
if( empty($membership_feature_lists) ) return;

$membership_type_settings = array();
if( isset( $wcfm_membership_options['membership_type_settings'] ) ) $membership_type_settings = $wcfm_membership_options['membership_type_settings'];
$free_membership = isset( $membership_type_settings['free_membership'] ) ? $membership_type_settings['free_membership'] : 0;
$featured_membership = isset( $membership_type_settings['featured_membership'] ) ? $membership_type_settings['featured_membership'] : 0;
$default_subscribe_button = isset( $membership_type_settings['subscribe_button_label'] ) ? $membership_type_settings['subscribe_button_label'] : __( "Subscribe Now", 'wc-multivendor-membership' );

$membership_visibility_priority = array();
if( isset( $wcfm_membership_options['membership_visibility_priority'] ) ) $membership_visibility_priority = $wcfm_membership_options['membership_visibility_priority'];

$wcfm_memberships_list = get_wcfm_memberships();
$wcfm_memberships_array = array();
if( !empty( $wcfm_memberships_list ) ) {
	foreach( $wcfm_memberships_list as $wcfm_membership_list ) {
		$wcfm_memberships_array[$wcfm_membership_list->ID] = array( 'title' => $wcfm_membership_list->post_title, 'priority' => $wcfm_membership_list->ID );
		
		if( !empty( $membership_visibility_priority ) ) {
			foreach( $membership_visibility_priority as $membership_visibility_index => $membership_visibility_membership_id ) {
				if( $membership_visibility_membership_id ) {
					if( $membership_visibility_membership_id == $wcfm_membership_list->ID ) {
						$wcfm_memberships_array[$wcfm_membership_list->ID]['priority'] = $membership_visibility_index;
					}
				}
			}
		}
	}
}

uasort( $wcfm_memberships_array, array( $WCFM, 'wcfm_sort_by_priority' ) );

$subscription_mode = 'new';
if( WC()->session && WC()->session->get( 'wcfm_membership_mode' ) ) $subscription_mode = WC()->session->get( 'wcfm_membership_mode' );
//if( isset( $_SESSION['wcfm_membership'] ) && isset( $_SESSION['wcfm_membership']['mode'] ) ) $subscription_mode = $_SESSION['wcfm_membership']['mode'];
$current_plan = wcfm_get_membership();

if( empty($wcfm_memberships_list) ) return;

$has_feature_box = false;
if( !empty( $membership_feature_lists ) ) {
	foreach( $membership_feature_lists as $membership_feature_list ) {
		if( $membership_feature_list['feature'] ) $has_feature_box = true;
	}
}

$wcfm_restricted_memberships = array();
//if( $current_plan ) {
	$member_id = apply_filters( 'wcfm_current_vendor_id', get_current_user_id() );
	$wcfm_restricted_memberships = get_user_meta( $member_id, 'wcfm_restricted_memberships', true ); 
	if( !$wcfm_restricted_memberships ) $wcfm_restricted_memberships = array();
//}

$wcfm_membership_table_plan_count = 0;
?>

<div id="wcfm_membership_container">
  <div class="wcfm_membership_boxes">
  
    <?php if( $has_feature_box ) { ?>
			<div class="wcfm_membership_box_wrraper wcfm_membership_feature_box_wrraper">
				<div class="wcfm_membership_box wcfm_membership_feature_box">
					<div class="wcfm_membership_box_head wcfm_membership_feature_box_head"><div class="wcfm_membership_title"><div class="wcfm_membership_title_text">&nbsp;</div></div></div>
					
					<div class="wcfm_membership_box_body wcfm_membership_feature_box_body">
						<?php
						foreach( $membership_feature_lists as $membership_feature_list ) {
							?>
							<div class="wcfm_membership_element wcfm_membership_feature_element">
								<span class="wcfm_membership_element_content"><?php echo wcfm_removeslashes( __( $membership_feature_list['feature'], 'WCfM' ) ); ?>
								  <?php if( isset( $membership_feature_list['help'] ) && !empty( $membership_feature_list['help'] ) ) { ?>&nbsp;<i class="wcfmfa fa-question-circle text_tip" data-tip="<?php echo wcfm_removeslashes( __( $membership_feature_list['help'], 'WCfM' ) ); ?>"></i> <?php } ?>
								</span>
							</div>
							<?php
						}
						$wcfm_membership_table_plan_count++;
						?>
					</div>
					
					<div class="wcfm_membership_box_foot wcfm_membership_feature_box_foot">&nbsp;</div>
				</div>
			</div>
		<?php } ?>
		
    <?php
    foreach( $wcfm_memberships_array as $wcfm_membership_id => $wcfm_membership_data ) {
    	$is_wcfm_membership_plan_disable = get_post_meta( $wcfm_membership_id, 'is_wcfm_membership_plan_disable', true ) ? 'yes' : 'no';
    	$is_wcfm_membership_plan_disable = apply_filters( 'wcfm_membership_is_plan_disable', $is_wcfm_membership_plan_disable, $wcfm_membership_id );
    	if( $is_wcfm_membership_plan_disable == 'yes' ) continue;
    	
    	// Restricted Membership Check
    	$subscription = (array) get_post_meta( $wcfm_membership_id, 'subscription', true );
    	$is_restricted = isset( $subscription['is_restricted'] ) ? 'yes' : 'no';
    	if( ($is_restricted == 'yes' ) && in_array( $wcfm_membership_id, $wcfm_restricted_memberships ) ) continue;
    	
    	$wcfm_membership_table_plan_count++;
    	
    	$wcfm_membership = get_post( $wcfm_membership_id );
    	$subscription = (array) get_post_meta( $wcfm_membership_id, 'subscription', true );
    	$features = (array) get_post_meta( $wcfm_membership_id, 'features', true );
    	
    	$subscribe_button_label = get_post_meta( $wcfm_membership_id, 'subscribe_button_label', true );
    	if( !$subscribe_button_label ) $subscribe_button_label = $default_subscribe_button;
    	
    	$is_free = isset( $subscription['is_free'] ) ? 'yes' : 'no';
    	$subscription_type = isset( $subscription['subscription_type'] ) ? $subscription['subscription_type'] : 'one_time';
			$one_time_amt = isset( $subscription['one_time_amt'] ) ? floatval($subscription['one_time_amt']) : '1';
			$trial_amt = isset( $subscription['trial_amt'] ) ? $subscription['trial_amt'] : '';
			$trial_period = isset( $subscription['trial_period'] ) ? $subscription['trial_period'] : '';
			$trial_period_type = isset( $subscription['trial_period_type'] ) ? $subscription['trial_period_type'] : 'M';
			$billing_amt = isset( $subscription['billing_amt'] ) ? floatval($subscription['billing_amt']) : '1';
			$billing_period = isset( $subscription['billing_period'] ) ? $subscription['billing_period'] : '1';
			$billing_period_count = isset( $subscription['billing_period_count'] ) ? $subscription['billing_period_count'] : '';
			$billing_period_type = isset( $subscription['billing_period_type'] ) ? $subscription['billing_period_type'] : 'M';
			$period_options = array( 'D' => __( 'Day(s)', 'wc-multivendor-membership' ), 'M' => __( 'Month(s)', 'wc-multivendor-membership' ), 'Y' => __( 'Year(s)', 'wc-multivendor-membership' ) );
    	
    	?>
    	
    	<div class="wcfm_membership_box_wrraper wcfm_membership_box_wrraper_<?php echo $wcfm_membership_id; ?> <?php if( $wcfm_membership_id == $featured_membership ) { echo 'wcfm_featured_membership_box_wrraper'; } ?>">
    		<div class="wcfm_membership_box_head wcfm_membership_box_head_inside">
					<?php if( $wcfm_membership_id == $featured_membership ) { ?>
						<div class="wcfm_membership_featured_top">
							<?php _e( 'Most Popular', 'wc-multivendor-membership' ); ?>
						</div>
					<?php } ?>
					<div class="wcfm_membership_title">
					  <div class="wcfm_membership_title_text">
						  <?php _e( $wcfm_membership->post_title, 'WCfM' ); ?>
						</div>
					</div>
					<div class="wcfm_membership_price">
						<?php 
						if( $is_free == 'yes' ) {
							echo apply_filters( 'wcfm_membership_price_display', wc_price(0), 0, $wcfm_membership_id, true );
							echo '<div class="wcfm_membership_price_description">' . __( 'No payment required', 'wc-multivendor-membership' ) . '</div>';
						} else {
							if( $subscription_type == 'one_time' ) {
								echo apply_filters( 'wcfm_membership_price_display', wc_price($one_time_amt), $one_time_amt, $wcfm_membership_id, false );
								echo '<div class="wcfm_membership_price_description">' . __( 'One time payment', 'wc-multivendor-membership' ) . '</div>';
							} else {
								echo apply_filters( 'wcfm_membership_price_display', wc_price($billing_amt), $billing_amt, $wcfm_membership_id, false );
								$price_description = sprintf( __( 'for each %s %s', 'wc-multivendor-membership' ), $billing_period, $period_options[$billing_period_type] );
								if( !empty( $trial_period ) && !empty( $trial_amt ) ) {
									$price_description .= ' ' . sprintf( __( 'with %s for first %s %s', 'wc-multivendor-membership' ), get_woocommerce_currency_symbol() . $trial_amt, $trial_period, $period_options[$trial_period_type] );
								} elseif( !empty( $trial_period ) && empty( $trial_amt ) ) {
									$price_description .= ' ' . sprintf( __( 'with %s %s free trial', 'wc-multivendor-membership' ), $trial_period, $period_options[$trial_period_type] );
								}
								echo '<div class="wcfm_membership_price_description">' . $price_description . '</div>';
							}
							wcfmvm_membership_table_tax_display();
						}
						?>
					</div>
					<div class="wcfm_membership_description">
						<span class="wcfm_membership_description_content">
						  <?php _e( $wcfm_membership->post_excerpt, 'WCfM' ); ?>
						  <?php do_action( 'after_wcfm_membership_description_content', $wcfm_membership->ID ); ?>
						</span>
					</div>
				</div>
				<div class="wcfm-clearfix"></div>
				
				<?php if( $has_feature_box ) { ?>
					<div class="wcfm_membership_box wcfm_membership_feature_box wcfm_membership_feature_box_inside">
						<div class="wcfm_membership_box_head wcfm_membership_feature_box_head"><div class="wcfm_membership_title"><div class="wcfm_membership_title_text">&nbsp;</div></div></div>
						
						<div class="wcfm_membership_box_body wcfm_membership_feature_box_body">
							<?php
							foreach( $membership_feature_lists as $membership_feature_list ) {
								?>
								<div class="wcfm_membership_element wcfm_membership_feature_element">
									<span class="wcfm_membership_element_content">
									  <?php _e( $membership_feature_list['feature'], 'WCfM' ); ?>
									  <?php if( isset( $membership_feature_list['help'] ) && !empty( $membership_feature_list['help'] ) ) { ?>&nbsp;<i class="wcfmfa fa-question-circle text_tip" data-tip="<?php _e( $membership_feature_list['help'], 'WCfM' ); ?>"></i> <?php } ?>
									</span>
								</div>
								<?php
							}
							?>
						</div>
						
						<div class="wcfm_membership_box_foot wcfm_membership_feature_box_foot">&nbsp;</div>
					</div>
				<?php } ?>
    	
				<div class="wcfm_membership_box <?php if( $wcfm_membership_id == $featured_membership ) { echo 'wcfm_featured_membership_box'; } elseif( $wcfm_membership_id == $free_membership ) { echo 'wcfm_free_membership_box'; } ?>">
					<div class="wcfm_membership_box_head">
						<?php if( $wcfm_membership_id == $featured_membership ) { ?>
							<div class="wcfm_membership_featured_top">
								<?php _e( 'Most Popular', 'wc-multivendor-membership' ); ?>
							</div>
						<?php } ?>
						<div class="wcfm_membership_title">
						  <div class="wcfm_membership_title_text">
							  <?php _e( $wcfm_membership->post_title, 'WCfM' ); ?>
							</div>
						</div>
						<div class="wcfm_membership_price">
							<?php 
							if( $is_free == 'yes' ) {
								echo apply_filters( 'wcfm_membership_price_display', wc_price(0), 0, $wcfm_membership_id, true );
								echo '<div class="wcfm_membership_price_description">' . __( 'No payment required', 'wc-multivendor-membership' ) . '</div>';
							} else {
								if( $subscription_type == 'one_time' ) {
									echo apply_filters( 'wcfm_membership_price_display', wc_price($one_time_amt), $one_time_amt, $wcfm_membership_id, false );
									echo '<div class="wcfm_membership_price_description">' . __( 'One time payment', 'wc-multivendor-membership' ) . '</div>';
								} else {
									echo apply_filters( 'wcfm_membership_price_display', wc_price($billing_amt), $billing_amt, $wcfm_membership_id, false );
									$price_description = sprintf( __( 'for each %s %s', 'wc-multivendor-membership' ), $billing_period, $period_options[$billing_period_type] );
									if( !empty( $trial_period ) && !empty( $trial_amt ) ) {
										$price_description .= ' ' . sprintf( __( 'with %s for first %s %s', 'wc-multivendor-membership' ), get_woocommerce_currency_symbol() . $trial_amt, $trial_period, $period_options[$trial_period_type] );
									} elseif( !empty( $trial_period ) && empty( $trial_amt ) ) {
										$price_description .= ' ' . sprintf( __( 'with %s %s free trial', 'wc-multivendor-membership' ), $trial_period, $period_options[$trial_period_type] );
									}
									echo '<div class="wcfm_membership_price_description">' . $price_description . '</div>';
								}
								wcfmvm_membership_table_tax_display();
							}
							?>
						</div>
						<div class="wcfm_membership_description">
							<span class="wcfm_membership_description_content">
							  <?php _e( $wcfm_membership->post_excerpt, 'WCfM' ); ?>
							  <?php do_action( 'after_wcfm_membership_description_content', $wcfm_membership->ID ); ?>
							</span>
						</div>
					</div>
					
					<?php if( $has_feature_box ) { ?>
						<div class="wcfm_membership_box_body">
							<?php
							foreach( $membership_feature_lists as $membership_feature_list ) {
								$feature_val = '';
								$feature_name = sanitize_title($membership_feature_list['feature']);
								if( !empty( $features ) && isset( $features[$feature_name] ) && !empty( $features[$feature_name] ) ) $feature_val = $features[$feature_name];
								if( !empty( $features ) && !$feature_val && isset( $features[$membership_feature_list['feature']] ) && !empty( $features[$membership_feature_list['feature']] ) ) $feature_val = $features[$membership_feature_list['feature']];
								if( !$feature_val ) $feature_val = 'x';
								?>
								<div class="wcfm_membership_element">
									<span class="wcfm_membership_element_content"><?php _e( $feature_val, 'WCfM' ); ?></span>
								</div>
								<?php
							}
							?>
						</div>
					<?php } ?>
					
					<div class="wcfm_membership_box_foot">
					  <?php if( $current_plan && ( $current_plan == $wcfm_membership_id ) && $billing_period_count ) { ?>
					  	<?php if( apply_filters( 'wcfm_is_allow_extend_membership', true ) ) { ?>
								<div class="wcfm_membership_subscribe_button_wrapper">
									<input class="wcfm_membership_subscribe_button wcfm_submit_button button" type="button" data-membership="<?php echo $wcfm_membership_id; ?>" value="<?php _e( 'Extend Subscription', 'wc-multivendor-membership' ); ?>">
								</div>
							<?php } else { ?>
								<h2 class="wcfm_membership_your_plan_label"><?php _e( 'Your Plan', 'wc-multivendor-membership' ); ?></h2>
							<?php } ?>
					  <?php } elseif( wcfm_is_allowed_membership() ) { ?>
					  	<div class="wcfm_membership_subscribe_button_wrapper">
					  	  <input class="wcfm_membership_subscribe_button wcfm_submit_button button" type="button" data-membership="<?php echo $wcfm_membership_id; ?>" value="<?php _e( $subscribe_button_label, 'wc-multivendor-membership' ); ?>">
					  	</div>
					  <?php } else { ?>
					  	<?php _e( 'Kindly logout from Admin account to have "Subscribe Now" button.', 'wc-multivendor-membership' ); ?>
					  <?php } ?>
					  <div class="wcfm-clearfix"></div>
					</div>
				</div>
				<div class="wcfm-clearfix"></div>
				<div class="wcfm_membership_box_foot wcfm_membership_box_foot_inside">
				  <?php if( $current_plan && ( $current_plan == $wcfm_membership_id ) && $billing_period_count ) { ?>
				  	<?php if( apply_filters( 'wcfm_is_allow_extend_membership', true ) ) { ?>
							<div class="wcfm_membership_subscribe_button_wrapper">
								<input class="wcfm_membership_subscribe_button wcfm_submit_button button" type="button" data-membership="<?php echo $wcfm_membership_id; ?>" value="<?php _e( 'Extend Subscription', 'wc-multivendor-membership' ); ?>">
							</div>
						<?php } else { ?>
							<h2 class="wcfm_membership_your_plan_label"><?php _e( 'Your Plan', 'wc-multivendor-membership' ); ?></h2>
						<?php } ?>
				  <?php } elseif( wcfm_is_allowed_membership() ) { ?>
				  	<div class="wcfm_membership_subscribe_button_wrapper">
				  	  <input class="wcfm_membership_subscribe_button wcfm_submit_button button" type="button" data-membership="<?php echo $wcfm_membership_id; ?>" value="<?php _e( $subscribe_button_label, 'wc-multivendor-membership' ); ?>">
				  	</div>
				  <?php } else { ?>
					  	<?php _e( 'Kindly logout from Admin account to have "Subscribe Now" button.', 'wc-multivendor-membership' ); ?>
				  <?php } ?>
				  <div class="wcfm-clearfix"></div>
				</div>
			</div>
    	<?php
    }
    
    if( apply_filters( 'wcfm_is_allow_membership_table_fix_css', true ) ) {
    	if( $wcfm_membership_table_plan_count > 1 ) {
				$membership_column_width = 100/$wcfm_membership_table_plan_count;
				?>
				<style>
					#wcfm-main-contentainer .wcfm_membership_box_wrraper { width: <?php echo $membership_column_width; ?>%; }
					@media only screen and (max-width: 768px) {
						#wcfm-main-contentainer .wcfm_membership_box_wrraper { 
							width: 100%; 
						}
					}
				</style>
				<?php
			}
    }
    ?>
  </div>
</div>