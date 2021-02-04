<?php
/**
 * WCFM plugin views
 *
 * Plugin Epeken Products Manage Views
 *
 * @author 		WC Lovers
 * @package 	wcfm/views/product-manager
 * @version   4.1.0
 */
global $wp, $WCFM;

$product_origin = '';
$product_insurance_mandatory = '';
$product_wood_pack_mandatory = '';
$product_free_ongkir = '';

if( isset( $wp->query_vars['wcfm-products-manage'] ) && !empty( $wp->query_vars['wcfm-products-manage'] ) ) {
	$product_id = $wp->query_vars['wcfm-products-manage'];
	if( $product_id ) {
		$epeken_product_config = array (
			"product_origin" => get_post_meta($post->ID,'product_origin',true)
		);

		$product_origin = $epeken_product_config['product_origin'];
		
		$product_insurance_mandatory = get_post_meta($product_id,'product_insurance_mandatory',true);
		$product_wood_pack_mandatory = get_post_meta($product_id,'product_wood_pack_mandatory',true);
		$product_free_ongkir = get_post_meta($product_id,'product_free_ongkir',true);
	}
}

$license = get_option('epeken_wcjne_license_key');		  
$origins = epeken_get_valid_origin($license);
$origins = json_decode($origins,true);
$origins = $origins["validorigin"];

?>

<?php if( apply_filters( 'wcfm_is_allow_epeken', true ) ) { ?>
	<!-- collapsible 22 - Epeken Support -->
	<div class="page_collapsible products_manage_epeken simple variable nonvirtual booking" id="wcfm_products_manage_form_epeken_head"><label class="wcfmfa fa-truck"></label><?php _e('Epeken Product Config', 'wc-frontend-manager'); ?><span></span></div>
	<div class="wcfm-container simple variable nonvirtual booking">
		<div id="wcfm_products_manage_form_epeken_expander" class="wcfm-content">
			<table>
				<tr>
					<td colspan=2><strong>Note:</strong> <em>Setting Kota Asal Di level product ini tidak berlaku jika Anda menginstal plugin Marketplace yang sudah disupport oleh plugin Epeken, 
					seperti WC-Vendors, Dokan atau WC-Marketplace.</em></td>
				</tr>
				<tr>
					<td width=40% height=30px>Kota asal pengiriman produk ini </td>
					<td><strong><?php echo epeken_code_to_city($product_origin); ?></strong></td>
				</tr>
				<tr>
				  <td width=40%>Ubah Kota Asal Pengiriman Ke</td> 
				  <td>
				    <select name="epeken_valid_origin_option" class="wcfm-select" id="epeken_valid_origin_option">
							<?php
								foreach($origins as $origin) {
									?>
									<option value=<?php echo $origin["origin_code"]; ?> <?php if ($product_origin === $origin["origin_code"]) echo " selected";?>> <?php echo $origin["kota_kabupaten"];?></option>
									<?php
								}
							
								if (empty($origins)) {
									?>
									<option value=<?php echo get_option('epeken_data_asal_kota');?>> <?php echo epeken_code_to_city(get_option('epeken_data_asal_kota')); ?> </option>
									<?php
								}
							?> 
					  </select>
					</td>
				</tr>
			</table>
			<div style="margin-top: 10px;">
				<table>
					<tr><td valign="top">
					<input type="checkbox" name="epeken_product_insurance_mandatory" class="wcfm-checkbox" id="epeken_product_insurance_mandatory" <?php if($product_insurance_mandatory === 'on') echo 'checked'; ?> /></td><td> Wajib Dikirim Menggunakan Asuransi
					</td></tr>
					<tr><td valign="top">
					<input type="checkbox" name="epeken_product_wood_pack_mandatory" class="wcfm-checkbox" id="epeken_product_wood_pack_mandatory" <?php if($product_wood_pack_mandatory === 'on') echo 'checked';?> /></td><td> Wajib Dikirim Menggunakan Packing Kayu. Untuk mewajibkan packing kayu pada item ini, pastikan Anda sudah melakukan Enable Packing Kayu di WooCommerce > Shipping > Epeken Courier > Packing Kayu Settings.
					</td></tr>
					<tr><td valign="top">
					<input type="checkbox" name="epeken_product_free_ongkir" class="wcfm-checkbox" id="epeken_product_free_ongkir" <?php if($product_free_ongkir === 'on') echo 'checked'; ?> /> </td><td>Gratiskan Ongkos Kirim Untuk Produk Ini
					</td></tr>
        </table>
      </div>
			<script language='javascript'>
			  jQuery(document).ready(function($) {
					var chkfreeongkir = document.getElementById('epeken_product_free_ongkir');
					var chkinsman = document.getElementById('epeken_product_insurance_mandatory');
					var chkwoodpackman = document.getElementById('epeken_product_wood_pack_mandatory'); 
					chkfreeongkir.onclick = function() {
						if(chkfreeongkir.checked) {
							chkinsman.checked = false; chkwoodpackman.checked = false;      
						}
					}       
					chkinsman.onclick = function() {
						if (chkinsman.checked && chkfreeongkir.checked) {
							alert('Tidak bisa diset bersama dengan gratis ongkir.');
							chkinsman.checked = false;
						}
					}
					chkwoodpackman.onclick = function() {
						if (chkwoodpackman.checked && chkfreeongkir.checked) {
							alert('Tidak bisa diset bersama dengan gratis ongkir.');
							chkwoodpackman.checked = false;
						}
					}
				});
			</script>
		</div>
	</div>
	<!-- end collapsible -->
	<div class="wcfm_clearfix"></div>
<?php } ?>