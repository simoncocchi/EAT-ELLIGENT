<?php
  global $WCFM;
?>
<div class="wcfmmp-collapse wcfm_popup_wrapper" id="wcfmmp_shipping_method_edit_container">
  <div class="wcfm-collapse-content" >
      <div class="page_collapsible  modal_head" id="wcfmmp_shipping_method_edit_general_head">
        <label class="wcfmfa fa-truck"></label>
        <span>
          <?php _e( 'Edit Shipping Methods', 'wc-multivendor-marketplace' ); ?>
        </span>
      </div>
      <div class="modal_body" id="wcfmmp_shipping_method_edit_form_general_body">
        <?php
          $WCFM->wcfm_fields->wcfm_generate_form_field ( 
            array(
              "method_id_selected" => array(
                'label' => false, 
                'name' => 'method_id_selected',
                'type' => 'hidden', 
                'class' => 'wcfm-text wcfm_ele', 
                'value' => ''  
              )
            )
          );
          $WCFM->wcfm_fields->wcfm_generate_form_field ( 
            array(
              "instance_id_selected" => array(
                'label' => false, 
                'name' => 'instance_id_selected',
                'type' => 'hidden', 
                'class' => 'wcfm-text wcfm_ele', 
                'value' => ''  
              )
            )
          );
        ?>
        <div class="shipping_form" id="free_shipping">
          <?php
          
            $WCFM->wcfm_fields->wcfm_generate_form_field ( 
            	apply_filters( 'wcfmmp_shipping_zone_title_fields',
								array(
									"method_title_fs" => array(
										'label' => __('Method Title', 'wc-multivendor-marketplace'), 
										'name' => 'method_title',
										'type' => 'text', 
										'class' => 'wcfm-text wcfm_ele', 
										'label_class' => 'wcfm_title wcfm_ele', 
										'placeholder' => __('Enter method title', 'wc-multivendor-marketplace'),
										'value' => ''  
									)
								)
							)
            );
          ?>
          <?php
            $WCFM->wcfm_fields->wcfm_generate_form_field ( 
            	apply_filters( 'wcfmmp_shipping_zone_cost_fields',
								array(
									"minimum_order_amount_fs" => array(
										'label' => __('Minimum order amount for free shipping', 'wc-multivendor-marketplace'), 
										'name' => 'minimum_order_amount',
										'type' => 'number', 
										'class' => 'wcfm-text wcfm_ele wcfm_non_negative_input', 
										'label_class' => 'wcfm_title wcfm_ele', 
										'placeholder' => __('0.00', 'wc-multivendor-marketplace'),
										'value' => ''  
									)
								)
							)
            );
          ?>
          <?php
            $WCFM->wcfm_fields->wcfm_generate_form_field ( 
            	apply_filters( 'wcfmmp_shipping_zone_description_fields',
								array(
									"method_description_fs" => array(
										'label' => __('Description', 'wc-multivendor-marketplace'), 
										'name' => 'method_description',
										'type' => 'textarea', 
										'class' => 'wcfm-textarea wcfm_ele', 
										'label_class' => 'wcfm_title wcfm_ele', 
										'value' => ''  
									)
								)
							)
            );
          ?>
        </div>
        <!-- Local Pickup -->
        <div class="shipping_form" id="local_pickup">
          <?php
          
            $WCFM->wcfm_fields->wcfm_generate_form_field ( 
            	apply_filters( 'wcfmmp_shipping_zone_title_fields',
								array(
									"method_title_lp" => array(
										'label' => __('Method Title', 'wc-multivendor-marketplace'), 
										'name' => 'method_title',
										'type' => 'text', 
										'class' => 'wcfm-text wcfm_ele', 
										'label_class' => 'wcfm_title wcfm_ele', 
										'placeholder' => __('Enter method title', 'wc-multivendor-marketplace'),
										'value' => ''  
									)
								)
							)
            );
          ?>
          <?php
            $WCFM->wcfm_fields->wcfm_generate_form_field ( 
            	apply_filters( 'wcfmmp_shipping_zone_cost_fields',
								array(
									"method_cost_lp" => array(
										'label' => __('Cost', 'wc-multivendor-marketplace'), 
										'name' => 'method_cost',
										'type' => 'number', 
										'class' => 'wcfm-text wcfm_non_negative_input wcfm_ele', 
										'label_class' => 'wcfm_title wcfm_ele', 
										'placeholder' => __('0.00', 'wc-multivendor-marketplace'),
										'value' => ''  
									)
								)
							)
            );
          ?>
          
          <?php
          if(apply_filters('show_shipping_zone_tax', true) && apply_filters('wcfmmp_is_allow_show_shipping_zone_tax', true) ) {
            $WCFM->wcfm_fields->wcfm_generate_form_field ( 
            	apply_filters( 'wcfmmp_shipping_zone_tax_fields',
								array(
									"method_tax_status_lp" => array(
										'label' => __('Tax Status', 'wc-multivendor-marketplace'), 
										'name' => 'method_tax_status',
										'type' => 'select', 
										'class' => 'wcfm-select wcfm_ele', 
										'label_class' => 'wcfm_title wcfm_ele', 
										'options' => array(
												'none' => __('None', 'wc-multivendor-marketplace'), 
												'taxable' => __('Taxable' , 'wc-multivendor-marketplace') 
												)  
									)
								)
              )
            );
          }
          ?>
          
          <?php
            $WCFM->wcfm_fields->wcfm_generate_form_field ( 
            	apply_filters( 'wcfmmp_shipping_zone_description_fields',
								array(
									"method_description_lp" => array(
										'label' => __('Description', 'wc-multivendor-marketplace'), 
										'name' => 'method_description',
										'type' => 'textarea', 
										'class' => 'wcfm-textarea wcfm_ele', 
										'label_class' => 'wcfm_title wcfm_ele', 
										'value' => ''  
									)
								)
							)
            );
          ?>
        </div>
        
        <div class="shipping_form" id="flat_rate">
          <?php
          
            $WCFM->wcfm_fields->wcfm_generate_form_field ( 
            	apply_filters( 'wcfmmp_shipping_zone_title_fields',
								array(
									"method_title_fr" => array(
										'label' => __('Method Title', 'wc-multivendor-marketplace'), 
										'name' => 'method_title',
										'type' => 'text', 
										'class' => 'wcfm-text wcfm_ele', 
										'label_class' => 'wcfm_title wcfm_ele', 
										'placeholder' => __('Enter method title', 'wc-multivendor-marketplace'),
										'value' => ''  
									)
								)
							)
            );
          ?>
          <?php
            $WCFM->wcfm_fields->wcfm_generate_form_field ( 
            	apply_filters( 'wcfmmp_shipping_zone_cost_fields',
								array(
									"method_cost_fr" => array(
										'label' => __('Cost', 'wc-multivendor-marketplace'), 
										'name' => 'method_cost',
										'type' => 'text', 
										'class' => 'wcfm-text wcfm_ele', 
										'label_class' => 'wcfm_title wcfm_ele', 
										'placeholder' => __('0.00', 'wc-multivendor-marketplace'),
										'value' => '',
									)
								)
              )
            );
          ?>
          <div class="description">
						<?php _e( 'Enter a cost (excl. tax) or sum, e.g. <code>10.00 * [qty]</code>.', 'wc-multivendor-marketplace' ) . '<br/><br/>' . _e( 'Use <code>[qty]</code> for the number of items, <br/><code>[cost]</code> for the total cost of items, and <code>[fee percent="10" min_fee="20" max_fee=""]</code> for percentage based fees.', 'wc-multivendor-marketplace' ); ?>
					</div>
          
          <?php
          if(apply_filters('show_shipping_zone_tax', true) && apply_filters('wcfmmp_is_allow_show_shipping_zone_tax', true) ) {
            $WCFM->wcfm_fields->wcfm_generate_form_field (
            	apply_filters( 'wcfmmp_shipping_zone_tax_fields',
								array(
									"method_tax_status_fr" => array(
										'label' => __('Tax Status', 'wc-multivendor-marketplace'), 
										'name' => 'method_tax_status',
										'type' => 'select', 
										'class' => 'wcfm-select wcfm_ele', 
										'label_class' => 'wcfm_title wcfm_ele', 
										'options' => array(
												'none' => __('None', 'wc-multivendor-marketplace'), 
												'taxable' => __('Taxable' , 'wc-multivendor-marketplace') 
												)  
									)
								)
							)
            );
          }
          ?>
          
          <?php
            $WCFM->wcfm_fields->wcfm_generate_form_field ( 
            	apply_filters( 'wcfmmp_shipping_zone_description_fields',
								array(
									"method_description_fr" => array(
										'label' => __('Description', 'wc-multivendor-marketplace'), 
										'name' => 'method_description',
										'type' => 'textarea', 
										'class' => 'wcfm-textarea wcfm_ele', 
										'label_class' => 'wcfm_title wcfm_ele', 
										'value' => ''  
									)
								)
							)
            );
          ?>
          <?php if( apply_filters( 'wcfmmp_is_allow_store_shipping_by_shipping_classes', true ) ) { ?>
            <div class="wcfmmp_shipping_classes">
              <h3><?php _e('Shipping Class Cost', 'wc-multivendor-marketplace'); ?></h3> 
              <div class="description">
                <?php _e('These costs can be optionally entered based on the shipping class set per product( This cost will be added with the shipping cost above).', 'wc-multivendor-marketplace'); ?>
              </div>
              <?php
              
              $shipping_classes =  WC()->shipping->get_shipping_classes();
              //print_r($shipping_classes);
              if( !empty( $shipping_classes ) ) {
                foreach ($shipping_classes as  $shipping_class ) {
                	if ( ! isset( $shipping_class->term_id ) ) {
										continue;
									}
                  
                  $WCFM->wcfm_fields->wcfm_generate_form_field ( 
                  	apply_filters( 'wcfmmp_shipping_zone_description_fields',
											array(
												$shipping_class->slug => array(
													'label' => __('Cost of Shipping Class: "', 'wc-multivendor-marketplace') . $shipping_class->name . '"' , 
													'name' => 'shipping_class_cost[]',
													'type' => 'text', 
													'class' => 'wcfm-text wcfm_ele sc_vals', 
													'label_class' => 'wcfm_title wcfm_ele', 
													'placeholder' => __('N/A', 'wc-multivendor-marketplace'),
													'value' => '',
													'custom_attributes' => array('shipping_class_id' => $shipping_class->term_id),
												)
											), $shipping_class->term_id, $shipping_class->name, $shipping_class
										)
                  ); ?>
                  <div class="description">
                    <?php _e( 'Enter a cost (excl. tax) or sum, e.g. <code>10.00 * [qty]</code>.', 'wc-multivendor-marketplace' ) . '<br/><br/>' . _e( 'Use <code>[qty]</code> for the number of items, <br/><code>[cost]</code> for the total cost of items, and <code>[fee percent="10" min_fee="20" max_fee=""]</code> for percentage based fees.', 'wc-multivendor-marketplace' ); ?>
                  </div>
                  
                <?php }
              }
              
              $WCFM->wcfm_fields->wcfm_generate_form_field ( 
								array(
									'no_class_cost' => array(
										'label' => __( 'No shipping class cost', 'woocommerce' ), 
										'name' => 'shipping_class_cost[]',
										'type' => 'text', 
										'class' => 'wcfm-text wcfm_ele sc_vals', 
										'label_class' => 'wcfm_title wcfm_ele', 
										'placeholder' => __('N/A', 'wc-multivendor-marketplace'),
										'value' => '',
										'custom_attributes' => array('shipping_class_id' => 'no_class_cost' ),
									)
								)
							); ?>
							<div class="description">
								<?php _e( 'Enter a cost (excl. tax) or sum, e.g. <code>10.00 * [qty]</code>.', 'wc-multivendor-marketplace' ) . '<br/><br/>' . _e( 'Use <code>[qty]</code> for the number of items, <br/><code>[cost]</code> for the total cost of items, and <code>[fee percent="10" min_fee="20" max_fee=""]</code> for percentage based fees.', 'wc-multivendor-marketplace' ); ?>
							</div>
              
							<?php
							$WCFM->wcfm_fields->wcfm_generate_form_field ( 
									array(
										"calculation_type" => array(
											'label' => __('Calculation type', 'wc-multivendor-marketplace'),
											'name' => 'calculation_type',
											'type' => 'select', 
											'class' => 'wcfm-select wcfm_ele', 
											'label_class' => 'wcfm_title wcfm_ele', 
											'options' => array(
													'class' => __('Per class: Charge shipping for each shipping class individually', 'wc-multivendor-marketplace' ),
													'order' => __('Per order: Charge shipping for the most expensive shipping class', 'wc-multivendor-marketplace' ),
											)
											
										)
									)
								);
              ?>
            </div>
          <?php } ?>
          
        </div>
        
        <?php do_action( 'wcfmmp_shipping_method_edit_popup_end' ); ?>
        
      </div>
      <div class="modal_footer" id="wcfmmp_shipping_method_edit_general_footer">
        <div class="inner">
          <button class="wcfmmp_submit_button wcfm_popup_button" id="wcfmmp_shipping_method_edit_button">
            <?php _e( 'Save Method Settings', 'wc-multivendor-marketplace' ); ?>
          </button>
        </div>
      </div>
    
  </div>
</div>