<?php
  global $WCFM;
?>
<div class="wcfmmp-collapse wcfm_popup_wrapper" id="wcfmmp_shipping_method_add_container">
  <div class="wcfm-collapse-content" >
    <form id="wcfmmp_shipping_method_manage_form" >
      <div class="page_collapsible  modal_head" id="wcfmmp_shipping_method_add_form_general_head">
        <label class="wcfmfa fa-truck"></label>
        <span>
          <?php _e( 'Add Shipping Methods', 'wc-multivendor-marketplace' ); ?>
        </span>
      </div>
      <div class="modal_body" id="wcfmmp_shipping_method_add_form_general_body">
        <p>
          <?php _e( 'Choose the shipping method you wish to add. Only shipping methods which support zones are listed.', 'wc-multivendor-marketplace' ); ?>
        </p>
        <?php 
          $vendor_shipping_methods = wcfmmp_get_shipping_methods();
          $WCFM->wcfm_fields->wcfm_generate_form_field ( 
              array(
                "shipping_method" => array(
                  'label' => __('Select Shipping Method', 'wc-multivendor-marketplace') , 
                  'name' => 'wcfmmp_shipping_method',
                  'type' => 'select', 
                  'class' => 'wcfm-select wcfm-select2 wcfm_ele', 
                  'label_class' => 'wcfm_title select_title', 
                  'attributes' => array( 'width' => '60%' ),
                  'options' => $vendor_shipping_methods
                )
              )
            );
        ?>
      </div>
      <div class="modal_footer" id="wcfmmp_shipping_method_add_form_general_footer">
        <div class="inner">
          <button class="wcfmmp_submit_button wcfm_popup_button" id="wcfmmp_shipping_method_add_button">
            <?php _e( 'Add Shipping Method', 'wc-multivendor-marketplace' ); ?>
          </button>
        </div>
      </div>
    </form>
  </div>
</div>