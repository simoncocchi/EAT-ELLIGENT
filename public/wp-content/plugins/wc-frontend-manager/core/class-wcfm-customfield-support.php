<?php
/**
 * WCFM plugin core
 *
 * Custom Field Support Controller
 *
 * @author 		WC Lovers
 * @package 	wcfm/core
 * @version   2.3.7
 */
 
class WCFM_Custom_Field_Support {

	public function __construct() {
		global $WCFM;
		
		// Custom Fields Settings
		add_action( 'end_wcfm_settings', array( &$this, 'wcfm_customfield_settings' ) );
		add_action( 'wcfm_settings_update', array( &$this, 'wcfm_customfield_settings_update' ) );
		
		// Product Manage Custom Field View
    add_action( 'end_wcfm_products_manage', array( &$this, 'wcfm_custom_field_products_manage_views' ), 200 );
  }
  
  /**
   * Product Manage Custom Field Settings
   */
  function wcfm_customfield_settings() {
  	global $WCFM;
  	$product_types = apply_filters( 'wcfm_product_types', array('simple' => __('Simple Product', 'wc-frontend-manager'), 'virtual' => __('Virtual Product', 'wc-frontend-manager'), 'variable' => __('Variable Product', 'wc-frontend-manager'), 'grouped' => __('Grouped Product', 'wc-frontend-manager'), 'external' => __('External/Affiliate Product', 'wc-frontend-manager') ) );
  	$field_types = apply_filters( 'wcfm_product_custom_filed_types', array( 'text' => 'Text', 'number' => 'Number', 'textarea' => 'Textarea', 'editor' => 'Rich Editor', 'datepicker' => 'Date Picker', 'timepicker' => 'Time Picker', 'checkbox' => 'Check Box', 'select' => 'Select', 'mselect' => 'Multi-Select Drop Down', 'upload' => 'File/Image', 'html' => 'HTML' ) );
  	$visibility_options = apply_filters( 'wcfm_product_custom_visibility_options', array( '' => __( 'Do not show', 'wc-frontend-manager' ) ) );
		$wcfm_product_custom_fields = get_option( 'wcfm_product_custom_fields', array() );
  	?>
  	<!-- collapsible -->
		<div class="page_collapsible" id="wcfm_settings_form_custom_field_head">
			<label class="fab fa-superpowers"></label>
			<?php _e('Product Custom Field', 'wc-frontend-manager'); ?><span></span>
		</div>
		<div class="wcfm-container">
			<div id="wcfm_settings_form_custom_field_expander" class="wcfm-content">
			  <h2><?php _e('Product Custom Field', 'wc-frontend-manager'); ?></h2>
				<?php wcfm_video_tutorial( 'https://wclovers.com/knowledgebase/wcfm-custom-fields/' ); ?>
				<div class="wcfm_clearfix"></div>
				<?php
					$WCFM->wcfm_fields->wcfm_generate_form_field( apply_filters( 'wcfm_settings_fields_custom_field', array(
																																														"wcfm_product_custom_fields" => array('label' => __('Custom Fields', 'wc-frontend-manager') , 'type' => 'multiinput', 'class' => 'wcfm-text wcfm_ele', 'label_class' => 'wcfm_title', 'desc_class' => 'instructions', 'value' => $wcfm_product_custom_fields, 'desc' => __( 'You can integrate any Third Party plugin using Custom Fields, but you should use the same fields name as used by Third Party plugins.', 'wc-frontend-manager' ), 'options' => array(
																																															    "enable"   => array('label' => __('Enable', 'wc-frontend-manager'), 'type' => 'checkbox', 'class' => 'wcfm-checkbox wcfm_ele', 'label_class' => 'wcfm_title checkbox-title', 'value' => 'yes'),
																																																	"block_name"              => array('label' => __('Block Name', 'wc-frontend-manager'), 'type' => 'text', 'class' => 'wcfm-text wcfm_ele', 'label_class' => 'wcfm_title'),
																																																	"exclude_product_types"   => array( 'label' => __('Exlude Product Types', 'wc-frontend-manager'), 'type' => 'select', 'options' => $product_types, 'attributes' => array( 'multiple' => 'multiple', 'style' => 'width: 60%;' ), 'class' => 'wcfm-select wcfm-select2 wcfm_ele exclude_product_types', 'label_class' => 'wcfm_title', 'hints' => __( 'Choose product types for which you want to disable this field block.', 'wc-frontend-manager' ) ),
																																																	"visibility"              => array( 'label' => __('Visibility', 'wc-frontend-manager'), 'type' => 'select', 'options' => $visibility_options, 'class' => 'wcfm-select wcfm_ele visibility_options', 'label_class' => 'wcfm_title', 'hints' => __( 'Set where and how you want to visible this custom field block in single product page.', 'wc-frontend-manager' ) ),
																																																	"is_group"                => array('label' => __('Fields as Group?', 'wc-frontend-manager'), 'type' => 'checkbox', 'value' => 'yes', 'class' => 'wcfm-checkbox wcfm_ele custom_field_is_group', 'label_class' => 'wcfm_title checkbox_title'),
																																																	"group_name"              => array('type' => 'text', 'class' => 'wcfm-text wcfm_ele custom_field_is_group_name', 'placeholder' => __('Group name', 'wc-frontend-manager'), 'label_class' => 'wcfm_title'),
																																																	"wcfm_product_custom_block_fields" => array('label' => __('Fields', 'wc-frontend-manager') . '<span class="fields_collapser wcfmfa fa-arrow-circle-down"></span>', 'type' => 'multiinput', 'class' => 'wcfm-text wcfm_ele', 'label_class' => 'wcfm_title', 'options' => array(
																																																									"type" => array( 'label' => __('Field Type', 'wc-frontend-manager'), 'type' => 'select', 'options' => $field_types, 'class' => 'wcfm-select wcfm_ele field_type_options', 'label_class' => 'wcfm_title'),           
																																																									"label" => array( 'label' => __('Label', 'wc-frontend-manager'), 'type' => 'text', 'class' => 'wcfm-text wcfm_ele', 'label_class' => 'wcfm_title'),
																																																									"name" => array( 'label' => __('Name', 'wc-frontend-manager'), 'type' => 'text', 'class' => 'wcfm-text wcfm_ele wcfm_slug_input', 'label_class' => 'wcfm_title', 'hints' => __( 'This is will going to use as `meta_key` for storing this field value in database.', 'wc-frontend-manager' ) ),
																																																									"options" => array( 'label' => __('Options', 'wc-frontend-manager'), 'type' => 'textarea', 'class' => 'wcfm-textarea wcfm_ele field_type_select_options', 'label_class' => 'wcfm_title field_type_select_options', 'placeholder' => __( 'Insert option values | separated, leave first element empty to show as \'-Select-\'', 'wc-frontend-manager' ) ),
																																																									"content" => array( 'label' => __('Content', 'wc-frontend-manager'), 'type' => 'textarea', 'class' => 'wcfm-textarea wcfm_ele field_type_html_options', 'label_class' => 'wcfm_title field_type_html_options' ),
																																																									"help_text" => array( 'label' => __('Help Content', 'wc-frontend-manager'), 'type' => 'text', 'class' => 'wcfm-text wcfm_ele', 'label_class' => 'wcfm_title' ),
																																																									"required" => array( 'label' => __('Required?', 'wc-frontend-manager'), 'type' => 'checkbox', 'class' => 'wcfm-checkbox wcfm_ele', 'label_class' => 'wcfm_title checkbox_title', 'value' => 'yes' ),
																																																		) )
																																															) )
																																														) ) );
				?>
				<?php if( !WCFM_Dependencies::wcfmu_plugin_active_check() ) { ?>
					<script>
						//jQuery(document).ready(function(t){t(".field_type_options").each(function(){t(this).find("option").each(function(){-1==t.inArray(t(this).attr("value"),["number","text","textarea"])&&(t(this).attr("disabled","disabled"),t(this).text(t(this).text()+" (Ultimate)"))})})});
					</script>
				<?php } ?>
			</div>
		</div>
		<div class="wcfm_clearfix"></div>
		<!-- end collapsible -->
		<?php
  }
  
  /**
   * Product Manage Custom Field Settings Update
   */
  function wcfm_customfield_settings_update( $wcfm_settings_form ) {
  	global $WCFM;
  	
  	// Save Product Custom Fields
		if( isset( $wcfm_settings_form['wcfm_product_custom_fields'] ) ) {
			update_option( 'wcfm_product_custom_fields', $wcfm_settings_form['wcfm_product_custom_fields'] );
		} else {
			update_option( 'wcfm_product_custom_fields', array() ); 
		}
  }
  
  /**
   * Product Manage Custom Field views
   */
  function wcfm_custom_field_products_manage_views( ) {
		global $WCFM;
	  
	 $WCFM->template->get_template( 'products-manager/wcfm-view-customfield-products-manage.php' );
	}
}