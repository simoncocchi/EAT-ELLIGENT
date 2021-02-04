<?php
defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'WPCleverWoosb' ) && class_exists( 'WC_Product' ) ) {
	class WPCleverWoosb {
		protected static $_instance = null;
		protected static $_types = array(
			'bundle',
			'woosb',
			'variable',
			'composite',
			'grouped',
			'woosg',
			'variation',
			'external'
		);

		public static function instance() {
			if ( is_null( self::$_instance ) ) {
				self::$_instance = new self();
			}

			return self::$_instance;
		}

		function __construct() {
			// Shortcode
			add_shortcode( 'woosb_form', array( $this, 'woosb_shortcode_form' ) );
			add_shortcode( 'woosb_bundled', array( $this, 'woosb_shortcode_bundled' ) );
			add_shortcode( 'woosb_bundles', array( $this, 'woosb_shortcode_bundles' ) );

			// Menu
			add_action( 'admin_menu', array( $this, 'woosb_admin_menu' ) );

			// Enqueue frontend scripts
			add_action( 'wp_enqueue_scripts', array( $this, 'woosb_wp_enqueue_scripts' ) );

			// Enqueue backend scripts
			add_action( 'admin_enqueue_scripts', array( $this, 'woosb_admin_enqueue_scripts' ) );

			// Backend AJAX search
			add_action( 'wp_ajax_woosb_get_search_results', array( $this, 'woosb_get_search_results' ) );

			// Add to selector
			add_filter( 'product_type_selector', array( $this, 'woosb_product_type_selector' ) );

			// Product data tabs
			add_filter( 'woocommerce_product_data_tabs', array( $this, 'woosb_product_data_tabs' ), 10, 1 );

			// Product tab
			if ( ( get_option( '_woosb_bundled_position', 'above' ) === 'tab' ) || ( get_option( '_woosb_bundles_position', 'no' ) === 'tab' ) ) {
				add_filter( 'woocommerce_product_tabs', array( $this, 'woosb_product_tabs' ) );
			}

			// Bundled products position
			switch ( get_option( '_woosb_bundled_position', 'above' ) ) {
				case 'below_title';
					add_action( 'woocommerce_single_product_summary', array(
						$this,
						'woosb_single_product_summary_bundled'
					), 6 );
					break;
				case 'below_price':
					add_action( 'woocommerce_single_product_summary', array(
						$this,
						'woosb_single_product_summary_bundled'
					), 11 );
					break;
				case 'below_excerpt';
					add_action( 'woocommerce_single_product_summary', array(
						$this,
						'woosb_single_product_summary_bundled'
					), 21 );
					break;
			}

			// Bundles position
			switch ( get_option( '_woosb_bundles_position', 'no' ) ) {
				case 'above':
					add_action( 'woocommerce_single_product_summary', array(
						$this,
						'woosb_single_product_summary_bundles'
					), 29 );
					break;
				case 'below':
					add_action( 'woocommerce_single_product_summary', array(
						$this,
						'woosb_single_product_summary_bundles'
					), 31 );
					break;
			}

			// Product filters
			add_filter( 'woocommerce_product_filters', array( $this, 'woosb_product_filters' ) );

			// Product data panels
			add_action( 'woocommerce_product_data_panels', array( $this, 'woosb_product_data_panels' ) );
			add_action( 'woocommerce_process_product_meta', array( $this, 'woosb_delete_option_fields' ) );
			add_action( 'woocommerce_process_product_meta_woosb', array( $this, 'woosb_save_option_fields' ) );

			// Add to cart form & button
			add_action( 'woocommerce_woosb_add_to_cart', array( $this, 'woosb_add_to_cart_form' ) );
			add_action( 'woocommerce_before_add_to_cart_button', array( $this, 'woosb_add_to_cart_button' ) );

			// Add to cart
			if ( get_option( '_woosb_exclude_unpurchasable', 'no' ) !== 'yes' ) {
				// Check validation
				add_filter( 'woocommerce_add_to_cart_validation', array(
					$this,
					'woosb_add_to_cart_validation'
				), 10, 2 );
			}

			add_filter( 'woocommerce_add_cart_item_data', array( $this, 'woosb_add_cart_item_data' ), 10, 2 );
			add_action( 'woocommerce_add_to_cart', array( $this, 'woosb_add_to_cart' ), 10, 6 );
			add_filter( 'woocommerce_get_cart_item_from_session', array(
				$this,
				'woosb_get_cart_item_from_session'
			), 10, 2 );

			// Cart item
			add_filter( 'woocommerce_cart_item_name', array( $this, 'woosb_cart_item_name' ), 10, 2 );
			add_filter( 'woocommerce_cart_item_quantity', array( $this, 'woosb_cart_item_quantity' ), 10, 3 );
			add_filter( 'woocommerce_cart_item_remove_link', array( $this, 'woosb_cart_item_remove_link' ), 10, 2 );
			add_filter( 'woocommerce_cart_contents_count', array( $this, 'woosb_cart_contents_count' ) );
			add_action( 'woocommerce_cart_item_removed', array( $this, 'woosb_cart_item_removed' ), 10, 2 );
			add_filter( 'woocommerce_cart_item_price', array( $this, 'woosb_cart_item_price' ), 10, 2 );
			add_filter( 'woocommerce_cart_item_subtotal', array( $this, 'woosb_cart_item_subtotal' ), 10, 2 );

			// Order
			add_filter( 'woocommerce_get_item_count', array( $this, 'woosb_get_item_count' ), 10, 3 );

			// Hide on cart & checkout page
			if ( get_option( '_woosb_hide_bundled', 'no' ) !== 'no' ) {
				add_filter( 'woocommerce_cart_item_visible', array( $this, 'woosb_item_visible' ), 10, 2 );
				add_filter( 'woocommerce_order_item_visible', array( $this, 'woosb_item_visible' ), 10, 2 );
				add_filter( 'woocommerce_checkout_cart_item_visible', array( $this, 'woosb_item_visible' ), 10, 2 );
			}

			// Hide on mini-cart
			if ( get_option( '_woosb_hide_bundled_mini_cart', 'no' ) === 'yes' ) {
				add_filter( 'woocommerce_widget_cart_item_visible', array( $this, 'woosb_item_visible' ), 10, 2 );
			}

			// Item class
			if ( get_option( '_woosb_hide_bundled', 'no' ) !== 'yes' ) {
				add_filter( 'woocommerce_cart_item_class', array( $this, 'woosb_item_class' ), 10, 2 );
				add_filter( 'woocommerce_mini_cart_item_class', array( $this, 'woosb_item_class' ), 10, 2 );
				add_filter( 'woocommerce_order_item_class', array( $this, 'woosb_item_class' ), 10, 2 );
			}

			// Get item data
			if ( get_option( '_woosb_hide_bundled', 'no' ) === 'yes_text' ) {
				add_filter( 'woocommerce_get_item_data', array( $this, 'woosb_get_item_data' ), 10, 2 );
				add_action( 'woocommerce_checkout_create_order_line_item', array(
					$this,
					'woosb_checkout_create_order_line_item'
				), 10, 4 );
			}

			// Order item
			add_action( 'woocommerce_checkout_create_order_line_item', array(
				$this,
				'woosb_add_order_item_meta'
			), 10, 3 );
			add_filter( 'woocommerce_order_item_name', array( $this, 'woosb_cart_item_name' ), 10, 2 );
			add_filter( 'woocommerce_order_formatted_line_subtotal', array(
				$this,
				'woosb_order_formatted_line_subtotal'
			), 10, 2 );

			// Admin order
			add_action( 'woocommerce_ajax_add_order_item_meta', array(
				$this,
				'woosb_ajax_add_order_item_meta'
			), 10, 3 );
			add_filter( 'woocommerce_hidden_order_itemmeta', array( $this, 'woosb_hidden_order_item_meta' ), 10, 1 );
			add_action( 'woocommerce_before_order_itemmeta', array( $this, 'woosb_before_order_item_meta' ), 10, 1 );

			// Undo remove
			add_action( 'woocommerce_restore_cart_item', array( $this, 'woosb_restore_cart_item' ), 10, 1 );

			// Add settings link
			add_filter( 'plugin_action_links_' . plugin_basename( WOOSB_DIR . '/' . basename( WOOSB_FILE ) ), array(
				$this,
				'woosb_action_links'
			), 10, 2 );
			add_filter( 'plugin_row_meta', array( $this, 'woosb_row_meta' ), 10, 2 );

			// Loop add-to-cart
			add_filter( 'woocommerce_loop_add_to_cart_link', array( $this, 'woosb_loop_add_to_cart_link' ), 99, 2 );

			// Use woocommerce_get_cart_contents instead of woocommerce_before_calculate_totals, prevent price error on mini-cart
			add_filter( 'woocommerce_get_cart_contents', array( $this, 'woosb_get_cart_contents' ), 10, 1 );
			//add_action( 'woocommerce_before_calculate_totals', array( $this, 'woosb_before_calculate_totals' ), 10, 1 );

			// Shipping
			add_filter( 'woocommerce_cart_shipping_packages', array( $this, 'woosb_cart_shipping_packages' ), 99, 1 );
			add_filter( 'woocommerce_cart_contents_weight', array( $this, 'woosb_cart_contents_weight' ), 99, 1 );

			// Price html
			add_filter( 'woocommerce_get_price_html', array( $this, 'woosb_get_price_html' ), 99, 2 );

			// Order again
			add_filter( 'woocommerce_order_again_cart_item_data', array(
				$this,
				'woosb_order_again_cart_item_data'
			), 10, 2 );
			add_action( 'woocommerce_cart_loaded_from_session', array( $this, 'woosb_cart_loaded_from_session' ) );

			// Coupons
			add_filter( 'woocommerce_coupon_is_valid_for_product', array(
				$this,
				'woosb_coupon_is_valid_for_product'
			), 10, 4 );

			// Admin
			add_filter( 'display_post_states', array( $this, 'woosb_display_post_states' ), 10, 2 );

			// Bulk action
			add_action( 'current_screen', array( $this, 'woosb_bulk_hooks' ) );

			// Emails
			add_action( 'woocommerce_no_stock_notification', array( $this, 'woosb_no_stock' ), 99 );
			add_action( 'woocommerce_low_stock_notification', array( $this, 'woosb_low_stock' ), 99 );

			// Search filters
			if ( get_option( '_woosb_search_sku', 'no' ) === 'yes' ) {
				add_filter( 'pre_get_posts', array( $this, 'woosb_search_sku' ), 99 );
			}

			if ( get_option( '_woosb_search_exact', 'no' ) === 'yes' ) {
				add_action( 'pre_get_posts', array( $this, 'woosb_search_exact' ), 99 );
			}

			if ( get_option( '_woosb_search_sentence', 'no' ) === 'yes' ) {
				add_action( 'pre_get_posts', array( $this, 'woosb_search_sentence' ), 99 );
			}
		}

		function woosb_admin_menu() {
			add_submenu_page( 'wpclever', esc_html__( 'WPC Product Bundles', 'woo-product-bundle' ), esc_html__( 'Product Bundles', 'woo-product-bundle' ), 'manage_options', 'wpclever-woosb', array(
				&$this,
				'woosb_admin_menu_content'
			) );
		}

		function woosb_admin_menu_content() {
			add_thickbox();
			$active_tab = isset( $_GET['tab'] ) ? sanitize_key( $_GET['tab'] ) : 'settings';
			?>
            <div class="wpclever_settings_page wrap">
                <h1 class="wpclever_settings_page_title"><?php echo esc_html__( 'WPC Product Bundles', 'woo-product-bundle' ) . ' ' . WOOSB_VERSION; ?></h1>
                <div class="wpclever_settings_page_desc about-text">
                    <p>
						<?php printf( esc_html__( 'Thank you for using our plugin! If you are satisfied, please reward it a full five-star %s rating.', 'woo-product-bundle' ), '<span style="color:#ffb900">&#9733;&#9733;&#9733;&#9733;&#9733;</span>' ); ?>
                        <br/>
                        <a href="<?php echo esc_url( WOOSB_REVIEWS ); ?>"
                           target="_blank"><?php esc_html_e( 'Reviews', 'woo-product-bundle' ); ?></a> | <a
                                href="<?php echo esc_url( WOOSB_CHANGELOG ); ?>"
                                target="_blank"><?php esc_html_e( 'Changelog', 'woo-product-bundle' ); ?></a>
                        | <a href="<?php echo esc_url( WOOSB_DISCUSSION ); ?>"
                             target="_blank"><?php esc_html_e( 'Discussion', 'woo-product-bundle' ); ?></a>
                    </p>
                </div>
                <div class="wpclever_settings_page_nav">
                    <h2 class="nav-tab-wrapper">
                        <a href="<?php echo admin_url( 'admin.php?page=wpclever-woosb&tab=how' ); ?>"
                           class="<?php echo $active_tab === 'how' ? 'nav-tab nav-tab-active' : 'nav-tab'; ?>">
							<?php esc_html_e( 'How to use?', 'woo-product-bundle' ); ?>
                        </a>
                        <a href="<?php echo admin_url( 'admin.php?page=wpclever-woosb&tab=settings' ); ?>"
                           class="<?php echo $active_tab === 'settings' ? 'nav-tab nav-tab-active' : 'nav-tab'; ?>">
							<?php esc_html_e( 'Settings', 'woo-product-bundle' ); ?>
                        </a>
                        <a href="<?php echo admin_url( 'admin.php?page=wpclever-woosb&tab=compatible' ); ?>"
                           class="<?php echo $active_tab === 'compatible' ? 'nav-tab nav-tab-active' : 'nav-tab'; ?>">
							<?php esc_html_e( 'Compatible', 'woo-product-bundle' ); ?>
                        </a>
                        <a href="<?php echo esc_url( WOOSB_DOCS ); ?>" class="nav-tab" target="_blank">
							<?php esc_html_e( 'Docs', 'woo-product-bundle' ); ?>
                        </a>
                        <a href="<?php echo admin_url( 'admin.php?page=wpclever-woosb&tab=premium' ); ?>"
                           class="<?php echo $active_tab === 'premium' ? 'nav-tab nav-tab-active' : 'nav-tab'; ?>"
                           style="color: #c9356e">
							<?php esc_html_e( 'Premium Version', 'woo-product-bundle' ); ?>
                        </a>
                        <a href="<?php echo esc_url( admin_url( 'admin.php?page=wpclever-kit' ) ); ?>"
                           class="nav-tab">
							<?php esc_html_e( 'Essential Kit', 'woo-product-bundle' ); ?>
                        </a>
                    </h2>
                </div>
                <div class="wpclever_settings_page_content">
					<?php if ( $active_tab === 'how' ) { ?>
                        <div class="wpclever_settings_page_content_text">
                            <p>
								<?php esc_html_e( 'When creating the product, please choose product data is "Smart Bundle" then you can see the search field to start search and add products to the bundle.', 'woo-product-bundle' ); ?>
                            </p>
                            <p>
                                <img src="<?php echo WOOSB_URI; ?>assets/images/how-01.jpg"/>
                            </p>
                        </div>
					<?php } elseif ( $active_tab === 'settings' ) { ?>
                        <form method="post" action="options.php">
							<?php wp_nonce_field( 'update-options' ) ?>
                            <table class="form-table">
                                <tr class="heading">
                                    <th colspan="2">
										<?php esc_html_e( 'General', 'woo-product-bundle' ); ?>
                                    </th>
                                </tr>
                                <tr>
                                    <th><?php esc_html_e( 'Price format', 'woo-product-bundle' ); ?></th>
                                    <td>
                                        <select name="_woosb_price_format">
                                            <option value="from_min" <?php echo( get_option( '_woosb_price_format', 'from_min' ) === 'from_min' ? 'selected' : '' ); ?>><?php esc_html_e( 'From min price', 'woo-product-bundle' ); ?></option>
                                            <option value="min_only" <?php echo( get_option( '_woosb_price_format', 'from_min' ) === 'min_only' ? 'selected' : '' ); ?>><?php esc_html_e( 'Min price only', 'woo-product-bundle' ); ?></option>
                                            <option value="min_max" <?php echo( get_option( '_woosb_price_format', 'from_min' ) === 'min_max' ? 'selected' : '' ); ?>><?php esc_html_e( 'Min - max', 'woo-product-bundle' ); ?></option>
                                            <option value="normal" <?php echo( get_option( '_woosb_price_format', 'from_min' ) === 'normal' ? 'selected' : '' ); ?>><?php esc_html_e( 'Regular and sale price', 'woo-product-bundle' ); ?></option>
                                        </select>
                                        <span class="description">
                                                    <?php esc_html_e( 'Choose the price format for bundle on the shop page.', 'woo-product-bundle' ); ?>
                                                </span>
                                    </td>
                                </tr>
                                <tr>
                                    <th><?php esc_html_e( 'Calculate bundled prices', 'woo-product-bundle' ); ?></th>
                                    <td>
                                        <select name="_woosb_bundled_price_from">
                                            <option
                                                    value="sale_price" <?php echo( get_option( '_woosb_bundled_price_from', 'sale_price' ) === 'sale_price' ? 'selected' : '' ); ?>>
												<?php esc_html_e( 'from Sale price', 'woo-product-bundle' ); ?>
                                            </option>
                                            <option
                                                    value="regular_price" <?php echo( get_option( '_woosb_bundled_price_from', 'sale_price' ) === 'regular_price' ? 'selected' : '' ); ?>>
												<?php esc_html_e( 'from Regular price', 'woo-product-bundle' ); ?>
                                            </option>
                                        </select>
                                        <span class="description">
											<?php esc_html_e( 'Bundled pricing methods: from Sale price (default) or Regular price.', 'woo-product-bundle' ); ?>
										</span>
                                    </td>
                                </tr>
                                <tr class="heading">
                                    <th colspan="2">
										<?php esc_html_e( 'Bundled products', 'woo-product-bundle' ); ?>
                                    </th>
                                </tr>
                                <tr>
                                    <th><?php esc_html_e( 'Position', 'woo-product-bundle' ); ?></th>
                                    <td>
                                        <select name="_woosb_bundled_position">
                                            <option
                                                    value="above" <?php echo( get_option( '_woosb_bundled_position', 'above' ) === 'above' ? 'selected' : '' ); ?>>
												<?php esc_html_e( 'Above the add to cart button', 'woo-product-bundle' ); ?>
                                            </option>
                                            <option
                                                    value="below" <?php echo( get_option( '_woosb_bundled_position', 'above' ) === 'below' ? 'selected' : '' ); ?>>
												<?php esc_html_e( 'Under the add to cart button', 'woo-product-bundle' ); ?>
                                            </option>
                                            <option
                                                    value="below_title" <?php echo( get_option( '_woosb_bundled_position', 'above' ) === 'below_title' ? 'selected' : '' ); ?>>
												<?php esc_html_e( 'Under the title', 'woo-product-bundle' ); ?>
                                            </option>
                                            <option
                                                    value="below_price" <?php echo( get_option( '_woosb_bundled_position', 'above' ) === 'below_price' ? 'selected' : '' ); ?>>
												<?php esc_html_e( 'Under the price', 'woo-product-bundle' ); ?>
                                            </option>
                                            <option
                                                    value="below_excerpt" <?php echo( get_option( '_woosb_bundled_position', 'above' ) === 'below_excerpt' ? 'selected' : '' ); ?>>
												<?php esc_html_e( 'Under the excerpt', 'woo-product-bundle' ); ?>
                                            </option>
                                            <option
                                                    value="tab" <?php echo( get_option( '_woosb_bundled_position', 'above' ) === 'tab' ? 'selected' : '' ); ?>>
												<?php esc_html_e( 'In a new tab', 'woo-product-bundle' ); ?>
                                            </option>
                                            <option
                                                    value="no" <?php echo( get_option( '_woosb_bundled_position', 'above' ) === 'no' ? 'selected' : '' ); ?>>
												<?php esc_html_e( 'No (hide it)', 'woo-product-bundle' ); ?>
                                            </option>
                                        </select> <span class="description">
                                                    <?php esc_html_e( 'Choose the position to show the bundled products list.', 'woo-product-bundle' ); ?>
                                                </span>
                                    </td>
                                </tr>
                                <tr>
                                    <th><?php esc_html_e( 'Variations selector', 'woo-product-bundle' ); ?></th>
                                    <td>
                                        <select name="_woosb_variations_selector">
                                            <option
                                                    value="default" <?php echo( get_option( '_woosb_variations_selector', 'default' ) === 'default' ? 'selected' : '' ); ?>>
												<?php esc_html_e( 'Default', 'woo-product-bundle' ); ?>
                                            </option>
                                            <option
                                                    value="wpc_radio" <?php echo( get_option( '_woosb_variations_selector', 'default' ) === 'wpc_radio' ? 'selected' : '' ); ?>>
												<?php esc_html_e( 'Use WPC Variations Radio Buttons', 'woo-product-bundle' ); ?>
                                            </option>
                                        </select> <span class="description">If you choose "Use WPC Variations Radio Buttons", please install <a
                                                    href="<?php echo esc_url( admin_url( 'plugin-install.php?tab=plugin-information&plugin=wpc-variations-radio-buttons&TB_iframe=true&width=800&height=550' ) ); ?>"
                                                    class="thickbox"
                                                    title="Install WPC Variations Radio Buttons">WPC Variations Radio Buttons</a> to make it work.</span>
                                    </td>
                                </tr>
                                <tr>
                                    <th><?php esc_html_e( 'Show thumbnail', 'woo-product-bundle' ); ?></th>
                                    <td>
                                        <select name="_woosb_bundled_thumb">
                                            <option
                                                    value="yes" <?php echo( get_option( '_woosb_bundled_thumb', 'yes' ) === 'yes' ? 'selected' : '' ); ?>>
												<?php esc_html_e( 'Yes', 'woo-product-bundle' ); ?>
                                            </option>
                                            <option
                                                    value="no" <?php echo( get_option( '_woosb_bundled_thumb', 'yes' ) === 'no' ? 'selected' : '' ); ?>>
												<?php esc_html_e( 'No', 'woo-product-bundle' ); ?>
                                            </option>
                                        </select>
                                    </td>
                                </tr>
                                <tr>
                                    <th><?php esc_html_e( 'Show quantity', 'woo-product-bundle' ); ?></th>
                                    <td>
                                        <select name="_woosb_bundled_qty">
                                            <option
                                                    value="yes" <?php echo( get_option( '_woosb_bundled_qty', 'yes' ) === 'yes' ? 'selected' : '' ); ?>>
												<?php esc_html_e( 'Yes', 'woo-product-bundle' ); ?>
                                            </option>
                                            <option
                                                    value="no" <?php echo( get_option( '_woosb_bundled_qty', 'yes' ) === 'no' ? 'selected' : '' ); ?>>
												<?php esc_html_e( 'No', 'woo-product-bundle' ); ?>
                                            </option>
                                        </select>
                                    </td>
                                </tr>
                                <tr>
                                    <th><?php esc_html_e( 'Show short description', 'woo-product-bundle' ); ?></th>
                                    <td>
                                        <select name="_woosb_bundled_description">
                                            <option
                                                    value="yes" <?php echo( get_option( '_woosb_bundled_description', 'no' ) === 'yes' ? 'selected' : '' ); ?>>
												<?php esc_html_e( 'Yes', 'woo-product-bundle' ); ?>
                                            </option>
                                            <option
                                                    value="no" <?php echo( get_option( '_woosb_bundled_description', 'no' ) === 'no' ? 'selected' : '' ); ?>>
												<?php esc_html_e( 'No', 'woo-product-bundle' ); ?>
                                            </option>
                                        </select>
                                    </td>
                                </tr>
                                <tr>
                                    <th><?php esc_html_e( 'Show price', 'woo-product-bundle' ); ?></th>
                                    <td>
                                        <select name="_woosb_bundled_price">
                                            <option
                                                    value="price" <?php echo( get_option( '_woosb_bundled_price', 'price' ) === 'price' ? 'selected' : '' ); ?>>
												<?php esc_html_e( 'Price', 'woo-product-bundle' ); ?>
                                            </option>
                                            <option
                                                    value="subtotal" <?php echo( get_option( '_woosb_bundled_price', 'price' ) === 'subtotal' ? 'selected' : '' ); ?>>
												<?php esc_html_e( 'Subtotal', 'woo-product-bundle' ); ?>
                                            </option>
                                            <option
                                                    value="no" <?php echo( get_option( '_woosb_bundled_price', 'price' ) === 'no' ? 'selected' : '' ); ?>>
												<?php esc_html_e( 'No', 'woo-product-bundle' ); ?>
                                            </option>
                                        </select>
                                    </td>
                                </tr>
                                <tr>
                                    <th><?php esc_html_e( 'Show plus/minus button', 'woo-product-bundle' ); ?></th>
                                    <td>
                                        <select name="_woosb_plus_minus">
                                            <option
                                                    value="yes" <?php echo( get_option( '_woosb_plus_minus', 'no' ) === 'yes' ? 'selected' : '' ); ?>>
												<?php esc_html_e( 'Yes', 'woo-product-bundle' ); ?>
                                            </option>
                                            <option
                                                    value="no" <?php echo( get_option( '_woosb_plus_minus', 'no' ) === 'no' ? 'selected' : '' ); ?>>
												<?php esc_html_e( 'No', 'woo-product-bundle' ); ?>
                                            </option>
                                        </select> <span
                                                class="description"><?php esc_html_e( 'Show the plus/minus button for the quantity input.', 'woo-product-bundle' ); ?></span>
                                    </td>
                                </tr>
                                <tr>
                                    <th><?php esc_html_e( 'Link to individual product', 'woo-product-bundle' ); ?></th>
                                    <td>
                                        <select name="_woosb_bundled_link">
                                            <option
                                                    value="yes" <?php echo( get_option( '_woosb_bundled_link', 'yes' ) === 'yes' ? 'selected' : '' ); ?>>
												<?php esc_html_e( 'Yes, open in the same tab', 'woo-product-bundle' ); ?>
                                            </option>
                                            <option
                                                    value="yes_blank" <?php echo( get_option( '_woosb_bundled_link', 'yes' ) === 'yes_blank' ? 'selected' : '' ); ?>>
												<?php esc_html_e( 'Yes, open in the new tab', 'woo-product-bundle' ); ?>
                                            </option>
                                            <option
                                                    value="yes_popup" <?php echo( get_option( '_woosb_bundled_link', 'yes' ) === 'yes_popup' ? 'selected' : '' ); ?>>
												<?php esc_html_e( 'Yes, open quick view popup', 'woo-product-bundle' ); ?>
                                            </option>
                                            <option
                                                    value="no" <?php echo( get_option( '_woosb_bundled_link', 'yes' ) === 'no' ? 'selected' : '' ); ?>>
												<?php esc_html_e( 'No', 'woo-product-bundle' ); ?>
                                            </option>
                                        </select> <span class="description">If you choose "Open quick view popup", please install <a
                                                    href="<?php echo esc_url( admin_url( 'plugin-install.php?tab=plugin-information&plugin=woo-smart-quick-view&TB_iframe=true&width=800&height=550' ) ); ?>"
                                                    class="thickbox" title="Install WPC Smart Quick View">WPC Smart Quick View</a> to make it work.</span>
                                    </td>
                                </tr>
                                <tr>
                                    <th><?php esc_html_e( 'Change image', 'woo-product-bundle' ); ?></th>
                                    <td>
                                        <select name="_woosb_change_image">
                                            <option
                                                    value="yes" <?php echo( get_option( '_woosb_change_image', 'yes' ) === 'yes' ? 'selected' : '' ); ?>>
												<?php esc_html_e( 'Yes', 'woo-product-bundle' ); ?>
                                            </option>
                                            <option
                                                    value="no" <?php echo( get_option( '_woosb_change_image', 'yes' ) === 'no' ? 'selected' : '' ); ?>>
												<?php esc_html_e( 'No', 'woo-product-bundle' ); ?>
                                            </option>
                                        </select>
                                        <span class="description">
											<?php esc_html_e( 'Change the main product image when choosing the variation of bundled products.', 'woo-product-bundle' ); ?>
										</span>
                                    </td>
                                </tr>
                                <tr>
                                    <th><?php esc_html_e( 'Change price', 'woo-product-bundle' ); ?></th>
                                    <td>
                                        <select name="_woosb_change_price">
                                            <option
                                                    value="yes" <?php echo( get_option( '_woosb_change_price', 'yes' ) === 'yes' ? 'selected' : '' ); ?>>
												<?php esc_html_e( 'Yes', 'woo-product-bundle' ); ?>
                                            </option>
                                            <option
                                                    value="yes_custom" <?php echo( get_option( '_woosb_change_price', 'yes' ) === 'yes_custom' ? 'selected' : '' ); ?>>
												<?php esc_html_e( 'Yes, custom selector', 'woo-product-bundle' ); ?>
                                            </option>
                                            <option
                                                    value="no" <?php echo( get_option( '_woosb_change_price', 'yes' ) === 'no' ? 'selected' : '' ); ?>>
												<?php esc_html_e( 'No', 'woo-product-bundle' ); ?>
                                            </option>
                                        </select>
                                        <input type="text" name="_woosb_change_price_custom"
                                               value="<?php echo get_option( '_woosb_change_price_custom', '.summary > .price' ); ?>"
                                               placeholder=".summary > .price"/>
                                        <span class="description">
											<?php esc_html_e( 'Change the main product price when choosing the variation of bundled products. It uses JavaScript to change product price so it is very dependent on theme’s HTML. If it cannot find and update the product price, please contact us and we can help you find the right selector or adjust the JS file.', 'woo-product-bundle' ); ?>
										</span>
                                    </td>
                                </tr>
                                <tr>
                                    <th><?php esc_html_e( 'Total text', 'woo-product-bundle' ); ?></th>
                                    <td>
                                        <input type="text" name="_woosb_bundle_price_text"
                                               value="<?php echo get_option( '_woosb_bundle_price_text', esc_html__( 'Bundle price:', 'woo-product-bundle' ) ); ?>"/>
                                    </td>
                                </tr>
                                <tr>
                                    <th><?php esc_html_e( 'Saved text', 'woo-product-bundle' ); ?></th>
                                    <td>
                                        <input type="text" name="_woosb_bundle_saved_text"
                                               value="<?php echo get_option( '_woosb_bundle_saved_text', esc_html__( '(saved [d])', 'woo-product-bundle' ) ); ?>"/>
                                        <span class="description">
											<?php esc_html_e( 'Use [d] to show the saved percentage or amount.', 'woo-product-bundle' ); ?>
										</span>
                                    </td>
                                </tr>
                                <tr class="heading">
                                    <th>
										<?php esc_html_e( 'Bundles', 'woo-product-bundle' ); ?>
                                    </th>
                                    <td>
										<?php esc_html_e( 'Settings for bundles on the bundled product page.', 'woo-product-bundle' ); ?>
                                    </td>
                                </tr>
                                <tr>
                                    <th><?php esc_html_e( 'Position', 'woo-product-bundle' ); ?></th>
                                    <td>
                                        <select name="_woosb_bundles_position">
                                            <option
                                                    value="above" <?php echo( get_option( '_woosb_bundles_position', 'no' ) === 'above' ? 'selected' : '' ); ?>>
												<?php esc_html_e( 'Above the add to cart button', 'woo-product-bundle' ); ?>
                                            </option>
                                            <option
                                                    value="below" <?php echo( get_option( '_woosb_bundles_position', 'no' ) === 'below' ? 'selected' : '' ); ?>>
												<?php esc_html_e( 'Under the add to cart button', 'woo-product-bundle' ); ?>
                                            </option>
                                            <option
                                                    value="tab" <?php echo( get_option( '_woosb_bundles_position', 'no' ) === 'tab' ? 'selected' : '' ); ?>>
												<?php esc_html_e( 'In a new tab', 'woo-product-bundle' ); ?>
                                            </option>
                                            <option
                                                    value="no" <?php echo( get_option( '_woosb_bundles_position', 'no' ) === 'no' ? 'selected' : '' ); ?>>
												<?php esc_html_e( 'No (hide it)', 'woo-product-bundle' ); ?>
                                            </option>
                                        </select> <span class="description">
                                                    <?php esc_html_e( 'Choose the position to show the bundles list.', 'woo-product-bundle' ); ?>
                                                </span>
                                    </td>
                                </tr>
                                <tr>
                                    <th>
										<?php esc_html_e( 'Above text', 'woo-product-bundle' ); ?>
                                    </th>
                                    <td>
                                                <textarea name="_woosb_bundles_before_text"
                                                          class="large-text"><?php echo stripslashes( get_option( '_woosb_bundles_before_text' ) ); ?></textarea>
                                    </td>
                                </tr>
                                <tr>
                                    <th>
										<?php esc_html_e( 'Under text', 'woo-product-bundle' ); ?>
                                    </th>
                                    <td>
                                                <textarea name="_woosb_bundles_after_text"
                                                          class="large-text"><?php echo stripslashes( get_option( '_woosb_bundles_after_text' ) ); ?></textarea>
                                    </td>
                                </tr>
                                <tr class="heading">
                                    <th>
										<?php esc_html_e( '"Add to Cart" button labels', 'woo-product-bundle' ); ?>
                                    </th>
                                    <td>
										<?php esc_html_e( 'Leave blank if you want to use the default text and can be translated.', 'woo-product-bundle' ); ?>
                                    </td>
                                </tr>
                                <tr>
                                    <th><?php esc_html_e( 'Archive/shop page', 'woo-product-bundle' ); ?></th>
                                    <td>
                                        <input type="text" name="_woosb_archive_button_add"
                                               value="<?php echo get_option( '_woosb_archive_button_add' ); ?>"
                                               placeholder="<?php esc_html_e( 'Add to cart', 'woo-product-bundle' ); ?>"/>
                                        <span class="description">
											<?php esc_html_e( 'For purchasable bundle.', 'woo-product-bundle' ); ?>
										</span><br/>
                                        <input type="text" name="_woosb_archive_button_select"
                                               value="<?php echo get_option( '_woosb_archive_button_select' ); ?>"
                                               placeholder="<?php esc_html_e( 'Select options', 'woo-product-bundle' ); ?>"/>
                                        <span class="description">
											<?php esc_html_e( 'For purchasable bundle and has variable product(s).', 'woo-product-bundle' ); ?>
										</span><br/>
                                        <input type="text" name="_woosb_archive_button_read"
                                               value="<?php echo get_option( '_woosb_archive_button_read' ); ?>"
                                               placeholder="<?php esc_html_e( 'Read more', 'woo-product-bundle' ); ?>"/>
                                        <span class="description">
											<?php esc_html_e( 'For un-purchasable bundle.', 'woo-product-bundle' ); ?>
										</span>
                                    </td>
                                </tr>
                                <tr>
                                    <th><?php esc_html_e( 'Single product page', 'woo-product-bundle' ); ?></th>
                                    <td>
                                        <input type="text" name="_woosb_single_button_add"
                                               value="<?php echo get_option( '_woosb_single_button_add' ); ?>"
                                               placeholder="<?php esc_html_e( 'Add to cart', 'woo-product-bundle' ); ?>"/>
                                    </td>
                                </tr>
                                <tr class="heading">
                                    <th colspan="2">
										<?php esc_html_e( 'Cart & Checkout', 'woo-product-bundle' ); ?>
                                    </th>
                                </tr>
                                <tr>
                                    <th><?php esc_html_e( 'Coupon restrictions', 'woo-product-bundle' ); ?></th>
                                    <td>
                                        <select name="_woosb_coupon_restrictions">
                                            <option
                                                    value="no" <?php echo( get_option( '_woosb_coupon_restrictions', 'no' ) === 'no' ? 'selected' : '' ); ?>>
												<?php esc_html_e( 'No', 'woo-product-bundle' ); ?>
                                            </option>
                                            <option
                                                    value="bundles" <?php echo( get_option( '_woosb_coupon_restrictions', 'no' ) === 'bundles' ? 'selected' : '' ); ?>>
												<?php esc_html_e( 'Exclude bundles', 'woo-product-bundle' ); ?>
                                            </option>
                                            <option
                                                    value="bundled" <?php echo( get_option( '_woosb_coupon_restrictions', 'no' ) === 'bundled' ? 'selected' : '' ); ?>>
												<?php esc_html_e( 'Exclude bundled products', 'woo-product-bundle' ); ?>
                                            </option>
                                            <option
                                                    value="both" <?php echo( get_option( '_woosb_coupon_restrictions', 'no' ) === 'both' ? 'selected' : '' ); ?>>
												<?php esc_html_e( 'Exclude both bundles and bundled products', 'woo-product-bundle' ); ?>
                                            </option>
                                        </select>
                                        <span class="description">
											<?php esc_html_e( 'Choose products you want to exclude from coupons.', 'woo-product-bundle' ); ?>
										</span>
                                    </td>
                                </tr>
                                <tr>
                                    <th><?php esc_html_e( 'Exclude un-purchasable products', 'woo-product-bundle' ); ?></th>
                                    <td>
                                        <select name="_woosb_exclude_unpurchasable">
                                            <option
                                                    value="yes" <?php echo( get_option( '_woosb_exclude_unpurchasable', 'no' ) === 'yes' ? 'selected' : '' ); ?>>
												<?php esc_html_e( 'Yes', 'woo-product-bundle' ); ?>
                                            </option>
                                            <option
                                                    value="no" <?php echo( get_option( '_woosb_exclude_unpurchasable', 'no' ) === 'no' ? 'selected' : '' ); ?>>
												<?php esc_html_e( 'No', 'woo-product-bundle' ); ?>
                                            </option>
                                        </select>
                                        <span class="description">
											<?php esc_html_e( 'Make the bundle still purchasable when one of the bundled products is un-purchasable. These bundled products are excluded from the orders.', 'woo-product-bundle' ); ?>
										</span>
                                    </td>
                                </tr>
                                <tr>
                                    <th><?php esc_html_e( 'Cart contents count', 'woo-product-bundle' ); ?></th>
                                    <td>
                                        <select name="_woosb_cart_contents_count">
                                            <option
                                                    value="bundle" <?php echo( get_option( '_woosb_cart_contents_count', 'bundle' ) === 'bundle' ? 'selected' : '' ); ?>>
												<?php esc_html_e( 'Bundles only', 'woo-product-bundle' ); ?>
                                            </option>
                                            <option
                                                    value="bundled_products" <?php echo( get_option( '_woosb_cart_contents_count', 'bundle' ) === 'bundled_products' ? 'selected' : '' ); ?>>
												<?php esc_html_e( 'Bundled products only', 'woo-product-bundle' ); ?>
                                            </option>
                                            <option
                                                    value="both" <?php echo( get_option( '_woosb_cart_contents_count', 'bundle' ) === 'both' ? 'selected' : '' ); ?>>
												<?php esc_html_e( 'Both bundles and bundled products', 'woo-product-bundle' ); ?>
                                            </option>
                                        </select>
                                    </td>
                                </tr>
                                <tr>
                                    <th><?php esc_html_e( 'Hide bundle name before bundled products', 'woo-product-bundle' ); ?></th>
                                    <td>
                                        <select name="_woosb_hide_bundle_name">
                                            <option
                                                    value="yes" <?php echo( get_option( '_woosb_hide_bundle_name', 'no' ) === 'yes' ? 'selected' : '' ); ?>>
												<?php esc_html_e( 'Yes', 'woo-product-bundle' ); ?>
                                            </option>
                                            <option
                                                    value="no" <?php echo( get_option( '_woosb_hide_bundle_name', 'no' ) === 'no' ? 'selected' : '' ); ?>>
												<?php esc_html_e( 'No', 'woo-product-bundle' ); ?>
                                            </option>
                                        </select>
                                    </td>
                                </tr>
                                <tr>
                                    <th><?php esc_html_e( 'Hide bundled products on cart & checkout page', 'woo-product-bundle' ); ?></th>
                                    <td>
                                        <select name="_woosb_hide_bundled">
                                            <option
                                                    value="yes" <?php echo( get_option( '_woosb_hide_bundled', 'no' ) === 'yes' ? 'selected' : '' ); ?>>
												<?php esc_html_e( 'Yes, just show the main product', 'woo-product-bundle' ); ?>
                                            </option>
                                            <option
                                                    value="yes_text" <?php echo( get_option( '_woosb_hide_bundled', 'no' ) === 'yes_text' ? 'selected' : '' ); ?>>
												<?php esc_html_e( 'Yes, but show bundled product names under the main product', 'woo-product-bundle' ); ?>
                                            </option>
                                            <option
                                                    value="no" <?php echo( get_option( '_woosb_hide_bundled', 'no' ) === 'no' ? 'selected' : '' ); ?>>
												<?php esc_html_e( 'No', 'woo-product-bundle' ); ?>
                                            </option>
                                        </select>
                                    </td>
                                </tr>
                                <tr>
                                    <th><?php esc_html_e( 'Hide bundled products on mini-cart', 'woo-product-bundle' ); ?></th>
                                    <td>
                                        <select name="_woosb_hide_bundled_mini_cart">
                                            <option
                                                    value="yes" <?php echo( get_option( '_woosb_hide_bundled_mini_cart', 'no' ) === 'yes' ? 'selected' : '' ); ?>>
												<?php esc_html_e( 'Yes', 'woo-product-bundle' ); ?>
                                            </option>
                                            <option
                                                    value="no" <?php echo( get_option( '_woosb_hide_bundled_mini_cart', 'no' ) === 'no' ? 'selected' : '' ); ?>>
												<?php esc_html_e( 'No', 'woo-product-bundle' ); ?>
                                            </option>
                                        </select>
                                        <span class="description">
											<?php esc_html_e( 'Hide bundled products, just show the main product on mini-cart.', 'woo-product-bundle' ); ?>
										</span>
                                    </td>
                                </tr>
                                <tr class="heading">
                                    <th colspan="2">
										<?php esc_html_e( 'Search', 'woo-product-bundle' ); ?>
                                    </th>
                                </tr>
                                <tr>
                                    <th><?php esc_html_e( 'Search limit', 'woo-product-bundle' ); ?></th>
                                    <td>
                                        <input name="_woosb_search_limit" type="number" min="1"
                                               max="500"
                                               value="<?php echo get_option( '_woosb_search_limit', '5' ); ?>"/>
                                    </td>
                                </tr>
                                <tr>
                                    <th><?php esc_html_e( 'Search by SKU', 'woo-product-bundle' ); ?></th>
                                    <td>
                                        <select name="_woosb_search_sku">
                                            <option
                                                    value="yes" <?php echo( get_option( '_woosb_search_sku', 'no' ) === 'yes' ? 'selected' : '' ); ?>>
												<?php esc_html_e( 'Yes', 'woo-product-bundle' ); ?>
                                            </option>
                                            <option
                                                    value="no" <?php echo( get_option( '_woosb_search_sku', 'no' ) === 'no' ? 'selected' : '' ); ?>>
												<?php esc_html_e( 'No', 'woo-product-bundle' ); ?>
                                            </option>
                                        </select>
                                    </td>
                                </tr>
                                <tr>
                                    <th><?php esc_html_e( 'Search by ID', 'woo-product-bundle' ); ?></th>
                                    <td>
                                        <select name="_woosb_search_id">
                                            <option
                                                    value="yes" <?php echo( get_option( '_woosb_search_id', 'no' ) === 'yes' ? 'selected' : '' ); ?>>
												<?php esc_html_e( 'Yes', 'woo-product-bundle' ); ?>
                                            </option>
                                            <option
                                                    value="no" <?php echo( get_option( '_woosb_search_id', 'no' ) === 'no' ? 'selected' : '' ); ?>>
												<?php esc_html_e( 'No', 'woo-product-bundle' ); ?>
                                            </option>
                                        </select> <span
                                                class="description"><?php esc_html_e( 'Search by ID when only entered the numeric.', 'woo-product-bundle' ); ?></span>
                                    </td>
                                </tr>
                                <tr>
                                    <th><?php esc_html_e( 'Search exact', 'woo-product-bundle' ); ?></th>
                                    <td>
                                        <select name="_woosb_search_exact">
                                            <option
                                                    value="yes" <?php echo( get_option( '_woosb_search_exact', 'no' ) === 'yes' ? 'selected' : '' ); ?>>
												<?php esc_html_e( 'Yes', 'woo-product-bundle' ); ?>
                                            </option>
                                            <option
                                                    value="no" <?php echo( get_option( '_woosb_search_exact', 'no' ) === 'no' ? 'selected' : '' ); ?>>
												<?php esc_html_e( 'No', 'woo-product-bundle' ); ?>
                                            </option>
                                        </select> <span
                                                class="description"><?php esc_html_e( 'Match whole product title or content?', 'woo-product-bundle' ); ?></span>
                                    </td>
                                </tr>
                                <tr>
                                    <th><?php esc_html_e( 'Search sentence', 'woo-product-bundle' ); ?></th>
                                    <td>
                                        <select name="_woosb_search_sentence">
                                            <option
                                                    value="yes" <?php echo( get_option( '_woosb_search_sentence', 'no' ) === 'yes' ? 'selected' : '' ); ?>>
												<?php esc_html_e( 'Yes', 'woo-product-bundle' ); ?>
                                            </option>
                                            <option
                                                    value="no" <?php echo( get_option( '_woosb_search_sentence', 'no' ) === 'no' ? 'selected' : '' ); ?>>
												<?php esc_html_e( 'No', 'woo-product-bundle' ); ?>
                                            </option>
                                        </select> <span
                                                class="description"><?php esc_html_e( 'Do a phrase search?', 'woo-product-bundle' ); ?></span>
                                    </td>
                                </tr>
                                <tr>
                                    <th><?php esc_html_e( 'Accept same products', 'woo-product-bundle' ); ?></th>
                                    <td>
                                        <select name="_woosb_search_same">
                                            <option
                                                    value="yes" <?php echo( get_option( '_woosb_search_same', 'no' ) === 'yes' ? 'selected' : '' ); ?>>
												<?php esc_html_e( 'Yes', 'woo-product-bundle' ); ?>
                                            </option>
                                            <option
                                                    value="no" <?php echo( get_option( '_woosb_search_same', 'no' ) === 'no' ? 'selected' : '' ); ?>>
												<?php esc_html_e( 'No', 'woo-product-bundle' ); ?>
                                            </option>
                                        </select> <span
                                                class="description"><?php esc_html_e( 'If yes, a product can be added many times.', 'woo-product-bundle' ); ?></span>
                                    </td>
                                </tr>
                                <tr class="submit">
                                    <th colspan="2">
                                        <input type="submit" name="submit" class="button button-primary"
                                               value="<?php esc_html_e( 'Update Options', 'woo-product-bundle' ); ?>"/>
                                        <input type="hidden" name="action" value="update"/>
                                        <input type="hidden" name="page_options"
                                               value="_woosb_price_format,_woosb_bundled_price_from,_woosb_bundled_position,_woosb_variations_selector,_woosb_bundled_thumb,_woosb_bundled_qty,_woosb_bundled_description,_woosb_bundled_price,_woosb_plus_minus,_woosb_bundled_link,_woosb_change_image,_woosb_change_price,_woosb_change_price_custom,_woosb_coupon_restrictions,_woosb_exclude_unpurchasable,_woosb_cart_contents_count,_woosb_hide_bundle_name,_woosb_hide_bundled,_woosb_hide_bundled_mini_cart,_woosb_bundle_price_text,_woosb_bundle_saved_text,_woosb_bundles_position,_woosb_bundles_before_text,_woosb_bundles_after_text,_woosb_archive_button_add,_woosb_archive_button_select,_woosb_archive_button_read,_woosb_single_button_add,_woosb_search_limit,_woosb_search_sku,_woosb_search_id,_woosb_search_exact,_woosb_search_sentence,_woosb_search_same"/>
                                    </th>
                                </tr>
                            </table>
                        </form>
					<?php } elseif ( $active_tab === 'compatible' ) { ?>
                        <form method="post" action="options.php">
							<?php wp_nonce_field( 'update-options' ) ?>
                            <table class="form-table">
                                <tr class="heading">
                                    <th colspan="2">
										<?php esc_html_e( 'WooCommerce PDF Invoices & Packing Slips', 'woo-product-bundle' ); ?>
                                        <a href="https://wordpress.org/plugins/woocommerce-pdf-invoices-packing-slips/"
                                           target="_blank"><span
                                                    class="dashicons dashicons-external"></span></a>
                                    </th>
                                </tr>
                                <tr>
                                    <th><?php esc_html_e( 'Hide bundles', 'woo-product-bundle' ); ?></th>
                                    <td>
                                        <select name="_woosb_compatible_wcpdf_hide_bundles">
                                            <option value="yes" <?php echo( get_option( '_woosb_compatible_wcpdf_hide_bundles', 'no' ) === 'yes' ? 'selected' : '' ); ?>><?php esc_html_e( 'Yes', 'woo-product-bundle' ); ?></option>
                                            <option value="no" <?php echo( get_option( '_woosb_compatible_wcpdf_hide_bundles', 'no' ) === 'no' ? 'selected' : '' ); ?>><?php esc_html_e( 'No', 'woo-product-bundle' ); ?></option>
                                        </select>
                                    </td>
                                </tr>
                                <tr>
                                    <th><?php esc_html_e( 'Hide bundled products', 'woo-product-bundle' ); ?></th>
                                    <td>
                                        <select name="_woosb_compatible_wcpdf_hide_bundled">
                                            <option value="yes" <?php echo( get_option( '_woosb_compatible_wcpdf_hide_bundled', 'no' ) === 'yes' ? 'selected' : '' ); ?>><?php esc_html_e( 'Yes', 'woo-product-bundle' ); ?></option>
                                            <option value="no" <?php echo( get_option( '_woosb_compatible_wcpdf_hide_bundled', 'no' ) === 'no' ? 'selected' : '' ); ?>><?php esc_html_e( 'No', 'woo-product-bundle' ); ?></option>
                                        </select>
                                    </td>
                                </tr>
                                <tr class="heading">
                                    <th colspan="2">
										<?php esc_html_e( 'WooCommerce PDF Invoices, Packing Slips, Delivery Notes & Shipping Labels', 'woo-product-bundle' ); ?>
                                        <a href="https://wordpress.org/plugins/print-invoices-packing-slip-labels-for-woocommerce/"
                                           target="_blank"><span
                                                    class="dashicons dashicons-external"></span></a>
                                    </th>
                                </tr>
                                <tr>
                                    <th><?php esc_html_e( 'Hide bundles', 'woo-product-bundle' ); ?></th>
                                    <td>
                                        <select name="_woosb_compatible_pklist_hide_bundles">
                                            <option value="yes" <?php echo( get_option( '_woosb_compatible_pklist_hide_bundles', 'no' ) === 'yes' ? 'selected' : '' ); ?>><?php esc_html_e( 'Yes', 'woo-product-bundle' ); ?></option>
                                            <option value="no" <?php echo( get_option( '_woosb_compatible_pklist_hide_bundles', 'no' ) === 'no' ? 'selected' : '' ); ?>><?php esc_html_e( 'No', 'woo-product-bundle' ); ?></option>
                                        </select>
                                    </td>
                                </tr>
                                <tr>
                                    <th><?php esc_html_e( 'Hide bundled products', 'woo-product-bundle' ); ?></th>
                                    <td>
                                        <select name="_woosb_compatible_pklist_hide_bundled">
                                            <option value="yes" <?php echo( get_option( '_woosb_compatible_pklist_hide_bundled', 'no' ) === 'yes' ? 'selected' : '' ); ?>><?php esc_html_e( 'Yes', 'woo-product-bundle' ); ?></option>
                                            <option value="no" <?php echo( get_option( '_woosb_compatible_pklist_hide_bundled', 'no' ) === 'no' ? 'selected' : '' ); ?>><?php esc_html_e( 'No', 'woo-product-bundle' ); ?></option>
                                        </select>
                                    </td>
                                </tr>
                                <tr class="submit">
                                    <th colspan="2">
                                        <input type="submit" name="submit" class="button button-primary"
                                               value="<?php esc_html_e( 'Update Options', 'woo-product-bundle' ); ?>"/>
                                        <input type="hidden" name="action" value="update"/>
                                        <input type="hidden" name="page_options"
                                               value="_woosb_compatible_wcpdf_hide_bundles,_woosb_compatible_wcpdf_hide_bundled,_woosb_compatible_pklist_hide_bundles,_woosb_compatible_pklist_hide_bundled"/>
                                    </th>
                                </tr>
                            </table>
                        </form>
					<?php } elseif ( $active_tab === 'premium' ) { ?>
                        <div class="wpclever_settings_page_content_text">
                            <p>
                                Get the Premium Version just $29! <a
                                        href="https://wpclever.net/downloads/woocommerce-product-bundle?utm_source=pro&utm_medium=woosb&utm_campaign=wporg"
                                        target="_blank">https://wpclever.net/downloads/woocommerce-product-bundle</a>
                            </p>
                            <p><strong>Extra features for Premium Version:</strong></p>
                            <ul style="margin-bottom: 0">
                                <li>- Add a variable product or a specific variation to a bundle.
                                </li>
                                <li>- Get the lifetime update & premium support.</li>
                            </ul>
                        </div>
					<?php } ?>
                </div>
            </div>
			<?php
		}

		function woosb_wp_enqueue_scripts() {
			wp_enqueue_style( 'woosb-frontend', WOOSB_URI . 'assets/css/frontend.css' );
			wp_enqueue_script( 'woosb-frontend', WOOSB_URI . 'assets/js/frontend.js', array( 'jquery' ), WOOSB_VERSION, true );

			$saved_text = trim( get_option( '_woosb_bundle_saved_text', esc_html__( '(saved [d])', 'woo-product-bundle' ) ) );

			if ( empty( $saved_text ) ) {
				$saved_text = esc_html__( '(saved [d])', 'woo-product-bundle' );
			}

			wp_localize_script( 'woosb-frontend', 'woosb_vars', array(
					'version'                  => WOOSB_VERSION,
					'alert_selection'          => esc_html__( 'Please select some product options for [name] before adding this bundle to the cart.', 'woo-product-bundle' ),
					'alert_empty'              => esc_html__( 'Please choose at least one product before adding this bundle to the cart.', 'woo-product-bundle' ),
					'alert_min'                => esc_html__( 'Please choose at least [min] in the whole products before adding this bundle to the cart.', 'woo-product-bundle' ),
					'alert_max'                => esc_html__( 'Please choose maximum [max] in the whole products before adding this bundle to the cart.', 'woo-product-bundle' ),
					'price_text'               => get_option( '_woosb_bundle_price_text', '' ),
					'saved_text'               => $saved_text,
					'container_selector'       => apply_filters( 'woosb_container_selector', '' ),
					'change_image'             => get_option( '_woosb_change_image', 'yes' ),
					'bundled_price'            => get_option( '_woosb_bundled_price', 'price' ),
					'bundled_price_from'       => get_option( '_woosb_bundled_price_from', 'sale_price' ),
					'change_price'             => get_option( '_woosb_change_price', 'yes' ),
					'price_selector'           => get_option( '_woosb_change_price_custom', '' ),
					'price_format'             => get_woocommerce_price_format(),
					'price_decimals'           => wc_get_price_decimals(),
					'price_thousand_separator' => wc_get_price_thousand_separator(),
					'price_decimal_separator'  => wc_get_price_decimal_separator(),
					'currency_symbol'          => get_woocommerce_currency_symbol()
				)
			);
		}

		function woosb_admin_enqueue_scripts() {
			wp_enqueue_style( 'hint', WOOSB_URI . 'assets/css/hint.css' );
			wp_enqueue_style( 'woosb-backend', WOOSB_URI . 'assets/css/backend.css' );
			wp_enqueue_script( 'dragarrange', WOOSB_URI . 'assets/js/drag-arrange.js', array( 'jquery' ), WOOSB_VERSION, true );
			wp_enqueue_script( 'woosb-backend', WOOSB_URI . 'assets/js/backend.js', array( 'jquery' ), WOOSB_VERSION, true );
			wp_localize_script( 'woosb-backend', 'woosb_vars', array(
					'price_decimals'           => wc_get_price_decimals(),
					'price_thousand_separator' => wc_get_price_thousand_separator(),
					'price_decimal_separator'  => wc_get_price_decimal_separator()
				)
			);
		}

		function woosb_action_links( $links, $file ) {
			$settings_link    = '<a href="' . admin_url( 'admin.php?page=wpclever-woosb&tab=settings' ) . '">' . esc_html__( 'Settings', 'woo-product-bundle' ) . '</a>';
			$links['premium'] = '<a href="' . admin_url( 'admin.php?page=wpclever-woosb&tab=premium' ) . '">' . esc_html__( 'Premium Version', 'woo-product-bundle' ) . '</a>';
			array_unshift( $links, $settings_link );

			return (array) $links;
		}

		function woosb_row_meta( $links, $file ) {
			if ( plugin_basename( WOOSB_DIR . '/' . basename( WOOSB_FILE ) ) === $file ) {
				$row_meta = array(
					'docs'    => '<a href="' . esc_url( WOOSB_DOCS ) . '" target="_blank">' . esc_html__( 'Docs', 'woo-product-bundle' ) . '</a>',
					'support' => '<a href="' . esc_url( WOOSB_SUPPORT ) . '" target="_blank">' . esc_html__( 'Support', 'woo-product-bundle' ) . '</a>',
				);

				return array_merge( $links, $row_meta );
			}

			return (array) $links;
		}

		function woosb_cart_contents_count( $count ) {
			// count for cart contents
			$cart_count = get_option( '_woosb_cart_contents_count', 'bundle' );

			if ( $cart_count !== 'both' ) {
				$cart_contents = WC()->cart->cart_contents;

				foreach ( $cart_contents as $cart_item_key => $cart_item ) {
					if ( ( $cart_count === 'bundled_products' ) && ! empty( $cart_item['woosb_ids'] ) ) {
						$count -= $cart_item['quantity'];
					}

					if ( ( $cart_count === 'bundle' ) && ! empty( $cart_item['woosb_parent_id'] ) ) {
						$count -= $cart_item['quantity'];
					}
				}
			}

			return $count;
		}

		function woosb_get_item_count( $count, $type, $order ) {
			// count for order items
			$cart_count    = get_option( '_woosb_cart_contents_count', 'bundle' );
			$order_bundles = $order_bundled = 0;

			if ( $cart_count !== 'both' ) {
				$order_items = $order->get_items( 'line_item' );

				foreach ( $order_items as $order_item ) {
					if ( $order_item->get_meta( '_woosb_parent_id' ) ) {
						$order_bundled += $order_item->get_quantity();
					}

					if ( $order_item->get_meta( '_woosb_ids' ) ) {
						$order_bundles += $order_item->get_quantity();
					}
				}

				if ( ( $cart_count === 'bundled_products' ) && ( $order_bundled > 0 ) ) {
					return $count - $order_bundles;
				}

				if ( ( $cart_count === 'bundle' ) && ( $order_bundles > 0 ) ) {
					return $count - $order_bundled;
				}
			}

			return $count;
		}

		function woosb_cart_item_name( $name, $cart_item ) {
			if ( isset( $cart_item['woosb_parent_id'] ) && ! empty( $cart_item['woosb_parent_id'] ) && ( get_option( '_woosb_hide_bundle_name', 'no' ) === 'no' ) ) {
				if ( ( strpos( $name, '</a>' ) !== false ) && ( get_option( '_woosb_bundled_link', 'yes' ) !== 'no' ) ) {
					return '<a href="' . get_permalink( $cart_item['woosb_parent_id'] ) . '">' . get_the_title( $cart_item['woosb_parent_id'] ) . '</a> &rarr; ' . $name;
				}

				return get_the_title( $cart_item['woosb_parent_id'] ) . ' &rarr; ' . strip_tags( $name );
			}

			return $name;
		}

		function woosb_cart_item_removed( $cart_item_key, $cart ) {
			if ( isset( $cart->removed_cart_contents[ $cart_item_key ]['woosb_keys'] ) ) {
				$keys = $cart->removed_cart_contents[ $cart_item_key ]['woosb_keys'];

				foreach ( $keys as $key ) {
					$cart->remove_cart_item( $key );

					if ( ( $new_key = array_search( $key, array_column( $cart->cart_contents, 'woosb_key', 'key' ) ) ) !== false ) {
						$cart->remove_cart_item( $new_key );
					}
				}
			}
		}

		function woosb_check_in_cart( $product_id ) {
			foreach ( WC()->cart->get_cart() as $cart_item ) {
				if ( $cart_item['product_id'] === $product_id ) {
					return true;
				}
			}

			return false;
		}

		function woosb_add_to_cart_validation( $passed, $product_id ) {
			$_product = wc_get_product( $product_id );

			if ( $_product && $_product->is_type( 'woosb' ) && ( $items = $_product->get_items() ) ) {
				if ( isset( $_POST['woosb_ids'] ) && ( $_product->is_optional() || $_product->has_variables() ) ) {
					$ids   = WPCleverWoosb_Helper::woosb_clean_ids( $_POST['woosb_ids'] );
					$items = $this->woosb_get_bundled( 0, $ids );
				}

				$qty = isset( $_POST['quantity'] ) ? (int) $_POST['quantity'] : 1;

				if ( ! empty( $items ) ) {
					foreach ( $items as $item ) {
						$_id      = $item['id'];
						$_qty     = $item['qty'];
						$_product = wc_get_product( $_id );

						if ( ! $_product ) {
							wc_add_notice( esc_html__( 'One of the bundled products is unavailable.', 'woo-product-bundle' ), 'error' );
							wc_add_notice( esc_html__( 'You cannot add this bundle to the cart.', 'woo-product-bundle' ), 'error' );

							return false;
						}

						if ( $_product->is_type( 'variable' ) || $_product->is_type( 'woosb' ) ) {
							wc_add_notice( sprintf( esc_html__( '"%s" is un-purchasable.', 'woo-product-bundle' ), esc_html( $_product->get_name() ) ), 'error' );
							wc_add_notice( esc_html__( 'You cannot add this bundle to the cart.', 'woo-product-bundle' ), 'error' );

							return false;
						}

						if ( ! $_product->is_in_stock() || ! $_product->is_purchasable() ) {
							wc_add_notice( sprintf( esc_html__( '"%s" is un-purchasable.', 'woo-product-bundle' ), esc_html( $_product->get_name() ) ), 'error' );
							wc_add_notice( esc_html__( 'You cannot add this bundle to the cart.', 'woo-product-bundle' ), 'error' );

							return false;
						}

						if ( ! $_product->has_enough_stock( $_qty * $qty ) ) {
							wc_add_notice( sprintf( esc_html__( '"%s" has not enough stock.', 'woo-product-bundle' ), esc_html( $_product->get_name() ) ), 'error' );
							wc_add_notice( esc_html__( 'You cannot add this bundle to the cart.', 'woo-product-bundle' ), 'error' );

							return false;
						}

						if ( $_product->is_sold_individually() && $this->woosb_check_in_cart( $_id ) ) {
							wc_add_notice( sprintf( esc_html__( 'You cannot add another "%s" to the cart.', 'woo-product-bundle' ), esc_html( $_product->get_name() ) ), 'error' );
							wc_add_notice( esc_html__( 'You cannot add this bundle to the cart.', 'woo-product-bundle' ), 'error' );

							return false;
						}

						if ( $_product->managing_stock() ) {
							$products_qty_in_cart = WC()->cart->get_cart_item_quantities();

							if ( isset( $products_qty_in_cart[ $_product->get_stock_managed_by_id() ] ) && ! $_product->has_enough_stock( $products_qty_in_cart[ $_product->get_stock_managed_by_id() ] + $_qty * $qty ) ) {
								wc_add_notice( sprintf( esc_html__( '"%s" has not enough stock.', 'woo-product-bundle' ), esc_html( $_product->get_name() ) ), 'error' );
								wc_add_notice( esc_html__( 'You cannot add this bundle to the cart.', 'woo-product-bundle' ), 'error' );

								return false;
							}
						}

						if ( post_password_required( $_id ) ) {
							wc_add_notice( sprintf( esc_html__( '"%s" is protected and cannot be purchased.', 'woo-product-bundle' ), esc_html( $_product->get_name() ) ), 'error' );
							wc_add_notice( esc_html__( 'You cannot add this bundle to the cart.', 'woo-product-bundle' ), 'error' );

							return false;
						}
					}
				}
			}

			return $passed;
		}

		function woosb_add_cart_item_data( $cart_item_data, $product_id ) {
			$_product = wc_get_product( $product_id );

			if ( $_product && $_product->is_type( 'woosb' ) && ( $ids = $_product->get_ids() ) ) {
				// make sure that is bundle
				if ( isset( $_POST['woosb_ids'] ) && ( $_product->is_optional() || $_product->has_variables() ) ) {
					$ids = WPCleverWoosb_Helper::woosb_clean_ids( $_POST['woosb_ids'] );
					unset( $_POST['woosb_ids'] );
				}

				if ( ! empty( $ids ) ) {
					$cart_item_data['woosb_ids'] = $ids;
				}
			}

			return $cart_item_data;
		}

		function woosb_add_to_cart( $cart_item_key, $product_id, $quantity, $variation_id, $variation, $cart_item_data ) {
			if ( ! empty( $cart_item_data['woosb_ids'] ) ) {
				$items = $this->woosb_get_bundled( 0, $cart_item_data['woosb_ids'] );
				$this->woosb_add_to_cart_items( $items, $cart_item_key, $product_id, $quantity );
			}
		}

		function woosb_restore_cart_item( $cart_item_key ) {
			if ( isset( WC()->cart->cart_contents[ $cart_item_key ]['woosb_ids'] ) ) {
				unset( WC()->cart->cart_contents[ $cart_item_key ]['woosb_keys'] );

				$product_id = WC()->cart->cart_contents[ $cart_item_key ]['product_id'];
				$quantity   = WC()->cart->cart_contents[ $cart_item_key ]['quantity'];
				$items      = $this->woosb_get_bundled( 0, WC()->cart->cart_contents[ $cart_item_key ]['woosb_ids'] );

				$this->woosb_add_to_cart_items( $items, $cart_item_key, $product_id, $quantity );
			}
		}

		function woosb_add_to_cart_items( $items, $cart_item_key, $product_id, $quantity ) {
			$fixed_price           = WC()->cart->cart_contents[ $cart_item_key ]['data']->is_fixed_price();
			$discount_amount       = WC()->cart->cart_contents[ $cart_item_key ]['data']->get_discount_amount();
			$discount_percentage   = WC()->cart->cart_contents[ $cart_item_key ]['data']->get_discount();
			$exclude_unpurchasable = get_option( '_woosb_exclude_unpurchasable', 'no' );

			// save current key associated with woosb_parent_key
			WC()->cart->cart_contents[ $cart_item_key ]['woosb_key']             = $cart_item_key;
			WC()->cart->cart_contents[ $cart_item_key ]['woosb_fixed_price']     = $fixed_price;
			WC()->cart->cart_contents[ $cart_item_key ]['woosb_discount_amount'] = $discount_amount;
			WC()->cart->cart_contents[ $cart_item_key ]['woosb_discount']        = $discount_percentage;

			if ( is_array( $items ) && ( count( $items ) > 0 ) ) {
				foreach ( $items as $item ) {
					$_id      = $item['id'];
					$_qty     = $item['qty'];
					$_product = wc_get_product( $_id );

					if ( ! $_product || ( $_qty <= 0 ) || in_array( $_product->get_type(), self::$_types, true ) ) {
						continue;
					}

					$_variation_id = 0;
					$_variation    = array();
					$_price        = WPCleverWoosb_Helper::woosb_get_price( $_product, 'min' );

					if ( $_product instanceof WC_Product_Variation ) {
						// ensure we don't add a variation to the cart directly by variation ID
						$_variation_id = $_id;
						$_id           = $_product->get_parent_id();
						$_variation    = $_product->get_variation_attributes();
					}

					if ( ! $fixed_price && $discount_percentage ) {
						$_price *= (float) ( 100 - $discount_percentage ) / 100;
						$_price = round( $_price, wc_get_price_decimals() );
					}

					// add to cart
					$_data = array(
						'woosb_qty'             => $_qty,
						'woosb_price'           => $_price,
						'woosb_parent_id'       => $product_id,
						'woosb_parent_key'      => $cart_item_key,
						'woosb_fixed_price'     => $fixed_price,
						'woosb_discount_amount' => $discount_amount,
						'woosb_discount'        => $discount_percentage
					);

					$cart_id = WC()->cart->generate_cart_id( $_id, $_variation_id, $_variation, $_data );
					$_key    = WC()->cart->find_product_in_cart( $cart_id );

					if ( empty( $_key ) ) {
						$_key = WC()->cart->add_to_cart( $_id, $_qty * $quantity, $_variation_id, $_variation, $_data );
					}

					if ( empty( $_key ) ) {
						if ( $exclude_unpurchasable !== 'yes' ) {
							// can't add the bundled product
							if ( isset( WC()->cart->cart_contents[ $cart_item_key ]['woosb_keys'] ) ) {
								$keys = WC()->cart->cart_contents[ $cart_item_key ]['woosb_keys'];

								foreach ( $keys as $key ) {
									// remove all bundled products
									WC()->cart->remove_cart_item( $key );
								}

								// remove the bundle
								WC()->cart->remove_cart_item( $cart_item_key );

								// break out of the loop
								break;
							}
						}
					} elseif ( ! isset( WC()->cart->cart_contents[ $cart_item_key ]['woosb_keys'] ) || ! in_array( $_key, WC()->cart->cart_contents[ $cart_item_key ]['woosb_keys'], true ) ) {
						// save current key
						WC()->cart->cart_contents[ $_key ]['woosb_key'] = $_key;

						// add keys for parent
						WC()->cart->cart_contents[ $cart_item_key ]['woosb_keys'][] = $_key;
					}
				} // end foreach
			}
		}

		function woosb_get_cart_item_from_session( $cart_item, $session_values ) {
			if ( isset( $session_values['woosb_ids'] ) && ! empty( $session_values['woosb_ids'] ) ) {
				$cart_item['woosb_ids'] = $session_values['woosb_ids'];
			}

			if ( isset( $session_values['woosb_parent_id'] ) ) {
				$cart_item['woosb_parent_id']  = $session_values['woosb_parent_id'];
				$cart_item['woosb_parent_key'] = $session_values['woosb_parent_key'];
				$cart_item['woosb_qty']        = $session_values['woosb_qty'];
			}

			return $cart_item;
		}

		function woosb_get_cart_contents( $cart_contents ) {
			foreach ( $cart_contents as $cart_item_key => $cart_item ) {
				// bundled products
				if ( ! empty( $cart_item['woosb_parent_id'] ) ) {
					// set price
					if ( isset( $cart_item['woosb_fixed_price'] ) && $cart_item['woosb_fixed_price'] ) {
						$cart_item['data']->set_price( 0 );
					} elseif ( isset( $cart_item['woosb_price'], $cart_item['woosb_discount_amount'], $cart_item['woosb_discount'] ) && ( $cart_item['woosb_discount_amount'] || $cart_item['woosb_discount'] ) ) {
						$cart_item['data']->set_price( $cart_item['woosb_price'] );
					}

					// sync quantity
					if ( ! empty( $cart_item['woosb_parent_key'] ) && ! empty( $cart_item['woosb_qty'] ) ) {
						$parent_key = $cart_item['woosb_parent_key'];

						if ( isset( $cart_contents[ $parent_key ] ) ) {
							$cart_contents[ $cart_item_key ]['quantity'] = $cart_item['woosb_qty'] * $cart_contents[ $parent_key ]['quantity'];
						} elseif ( ( $parent_new_key = array_search( $parent_key, array_column( $cart_contents, 'woosb_key', 'key' ) ) ) !== false ) {
							$cart_contents[ $cart_item_key ]['quantity'] = $cart_item['woosb_qty'] * $cart_contents[ $parent_new_key ]['quantity'];
						}
					}
				}

				// bundles
				if ( ! empty( $cart_item['woosb_ids'] ) && isset( $cart_item['woosb_fixed_price'] ) && ! $cart_item['woosb_fixed_price'] ) {
					// set price zero, calculate later
					if ( isset( $cart_item['woosb_discount_amount'] ) && $cart_item['woosb_discount_amount'] ) {
						$cart_item['data']->set_price( - (float) $cart_item['woosb_discount_amount'] );
					} else {
						$cart_item['data']->set_price( 0 );
					}

					if ( ! empty( $cart_item['woosb_keys'] ) ) {
						$bundle_price = 0;

						foreach ( $cart_item['woosb_keys'] as $key ) {
							if ( isset( $cart_contents[ $key ] ) ) {
								$bundle_price += wc_get_price_to_display( $cart_contents[ $key ]['data'], array(
									'qty'   => $cart_contents[ $key ]['woosb_qty'],
									'price' => $cart_contents[ $key ]['woosb_price']
								) );
							}
						}

						if ( ! empty( $cart_item['woosb_discount_amount'] ) ) {
							$bundle_price -= (float) $cart_item['woosb_discount_amount'];
						}

						if ( $cart_item['quantity'] > 0 ) {
							$cart_contents[ $cart_item_key ]['woosb_price'] = round( $bundle_price, wc_get_price_decimals() );
						}
					}
				}
			}

			return $cart_contents;
		}

		function woosb_before_calculate_totals( $cart_object ) {
			if ( ! defined( 'DOING_AJAX' ) && is_admin() ) {
				//  This is necessary for WC 3.0+
				return;
			}

			$cart_contents = WC()->cart->get_cart();

			foreach ( $cart_contents as $cart_item_key => $cart_item ) {
				// bundled products
				if ( ! empty( $cart_item['woosb_parent_id'] ) ) {
					// set price
					if ( isset( $cart_item['woosb_fixed_price'] ) && $cart_item['woosb_fixed_price'] ) {
						$cart_item['data']->set_price( 0 );
					} elseif ( isset( $cart_item['woosb_price'], $cart_item['woosb_discount_amount'], $cart_item['woosb_discount'] ) && ( $cart_item['woosb_discount_amount'] || $cart_item['woosb_discount'] ) ) {
						$cart_item['data']->set_price( $cart_item['woosb_price'] );
					}

					// sync quantity
					if ( ! empty( $cart_item['woosb_parent_key'] ) && ! empty( $cart_item['woosb_qty'] ) ) {
						$parent_key = $cart_item['woosb_parent_key'];

						if ( isset( $cart_contents[ $parent_key ] ) ) {
							WC()->cart->cart_contents[ $cart_item_key ]['quantity'] = $cart_item['woosb_qty'] * $cart_contents[ $parent_key ]['quantity'];
						} elseif ( ( $parent_new_key = array_search( $parent_key, array_column( $cart_contents, 'woosb_key', 'key' ) ) ) !== false ) {
							WC()->cart->cart_contents[ $cart_item_key ]['quantity'] = $cart_item['woosb_qty'] * $cart_contents[ $parent_new_key ]['quantity'];
						}
					}
				}

				// bundles
				if ( ! empty( $cart_item['woosb_ids'] ) && isset( $cart_item['woosb_fixed_price'] ) && ! $cart_item['woosb_fixed_price'] ) {
					// set price zero, calculate later
					if ( isset( $cart_item['woosb_discount_amount'] ) && $cart_item['woosb_discount_amount'] ) {
						$cart_item['data']->set_price( - (float) $cart_item['woosb_discount_amount'] );
					} else {
						$cart_item['data']->set_price( 0 );
					}

					if ( ! empty( $cart_item['woosb_keys'] ) ) {
						$bundle_price = 0;

						foreach ( $cart_item['woosb_keys'] as $key ) {
							if ( isset( $cart_contents[ $key ] ) ) {
								$bundle_price += wc_get_price_to_display( $cart_contents[ $key ]['data'], array(
									'qty'   => $cart_contents[ $key ]['woosb_qty'],
									'price' => $cart_contents[ $key ]['woosb_price']
								) );
							}
						}

						if ( ! empty( $cart_item['woosb_discount_amount'] ) ) {
							$bundle_price -= (float) $cart_item['woosb_discount_amount'];
						}

						if ( $cart_item['quantity'] > 0 ) {
							WC()->cart->cart_contents[ $cart_item_key ]['woosb_price'] = round( $bundle_price, wc_get_price_decimals() );
						}
					}
				}
			}
		}

		function woosb_cart_item_price( $price, $cart_item ) {
			if ( isset( $cart_item['woosb_ids'], $cart_item['woosb_price'], $cart_item['woosb_fixed_price'] ) && ! $cart_item['woosb_fixed_price'] ) {
				return wc_price( $cart_item['woosb_price'] );
			}

			if ( isset( $cart_item['woosb_parent_id'], $cart_item['woosb_price'], $cart_item['woosb_fixed_price'] ) && $cart_item['woosb_fixed_price'] ) {
				return wc_price( $cart_item['woosb_price'] );
			}

			return $price;
		}

		function woosb_cart_item_subtotal( $subtotal, $cart_item = null ) {
			if ( isset( $cart_item['woosb_ids'], $cart_item['woosb_price'], $cart_item['woosb_fixed_price'] ) && ! $cart_item['woosb_fixed_price'] ) {
				return wc_price( $cart_item['woosb_price'] * $cart_item['quantity'] );
			}

			if ( isset( $cart_item['woosb_parent_id'], $cart_item['woosb_price'], $cart_item['woosb_fixed_price'] ) && $cart_item['woosb_fixed_price'] ) {
				return wc_price( $cart_item['woosb_price'] * $cart_item['quantity'] );
			}

			return $subtotal;
		}

		function woosb_item_visible( $visible, $cart_item ) {
			if ( isset( $cart_item['woosb_parent_id'] ) ) {
				return false;
			}

			return $visible;
		}

		function woosb_item_class( $class, $cart_item ) {
			if ( isset( $cart_item['woosb_parent_id'] ) ) {
				$class .= ' woosb-cart-item woosb-cart-child woosb-item-child';
			} elseif ( isset( $cart_item['woosb_ids'] ) ) {
				$class .= ' woosb-cart-item woosb-cart-parent woosb-item-parent';
			}

			return $class;
		}

		function woosb_get_item_data( $data, $cart_item ) {
			if ( empty( $cart_item['woosb_ids'] ) ) {
				return $data;
			}

			$items     = $this->woosb_get_bundled( 0, $cart_item['woosb_ids'] );
			$items_str = '';

			if ( is_array( $items ) && count( $items ) > 0 ) {
				foreach ( $items as $item ) {
					$items_str .= $item['qty'] . ' × ' . get_the_title( $item['id'] ) . '; ';
				}
			}

			$items_str          = trim( $items_str, '; ' );
			$data['woosb_data'] = array(
				'key'     => esc_html__( 'Bundled products', 'woo-product-bundle' ),
				'value'   => $items_str,
				'display' => '',
			);

			return $data;
		}

		function woosb_checkout_create_order_line_item( $order_item, $cart_item_key, $values, $order ) {
			if ( empty( $values['woosb_ids'] ) ) {
				return;
			}

			$items     = $this->woosb_get_bundled( 0, $values['woosb_ids'] );
			$items_str = '';

			if ( is_array( $items ) && count( $items ) > 0 ) {
				foreach ( $items as $item ) {
					$items_str .= $item['qty'] . ' × ' . get_the_title( $item['id'] ) . '; ';
				}
			}

			$items_str = trim( $items_str, '; ' );
			$order_item->add_meta_data( esc_html__( 'Bundled products', 'woo-product-bundle' ), $items_str );
		}

		function woosb_add_order_item_meta( $order_item, $cart_item_key, $values ) {
			if ( isset( $values['woosb_parent_id'] ) ) {
				// use _ to hide the data
				$order_item->update_meta_data( '_woosb_parent_id', $values['woosb_parent_id'] );
			}

			if ( isset( $values['woosb_ids'] ) ) {
				// use _ to hide the data
				$order_item->update_meta_data( '_woosb_ids', $values['woosb_ids'] );
			}

			if ( isset( $values['woosb_price'] ) ) {
				// use _ to hide the data
				$order_item->update_meta_data( '_woosb_price', $values['woosb_price'] );
			}
		}

		function woosb_ajax_add_order_item_meta( $order_item_id, $order_item, $order ) {
			$quantity = $order_item->get_quantity();

			if ( 'line_item' === $order_item->get_type() ) {
				$product    = $order_item->get_product();
				$product_id = $product->get_id();

				if ( $product && $product->is_type( 'woosb' ) && ( $items = $product->get_items() ) ) {
					// get bundle info
					$fixed_price         = $product->is_fixed_price();
					$discount_amount     = $product->get_discount_amount();
					$discount_percentage = $product->get_discount();

					// add the bundle
					if ( ! $fixed_price ) {
						if ( $discount_amount ) {
							$product->set_price( - (float) $discount_amount );
						} else {
							$product->set_price( 0 );
						}
					}

					$order_id = $order->add_product( $product, $quantity );

					foreach ( $items as $item ) {
						$_product = wc_get_product( $item['id'] );

						if ( ! $_product || in_array( $_product->get_type(), self::$_types, true ) ) {
							continue;
						}

						if ( $fixed_price ) {
							$_product->set_price( 0 );
						} elseif ( $discount_percentage ) {
							$_price = (float) ( 100 - $discount_percentage ) * WPCleverWoosb_Helper::woosb_get_price( $_product, 'min' ) / 100;
							$_product->set_price( $_price );
						}

						// add bundled products
						$_order_item_id = $order->add_product( $_product, $item['qty'] * $quantity );

						if ( ! $_order_item_id ) {
							continue;
						}

						$_order_items = $order->get_items( 'line_item' );
						$_order_item  = $_order_items[ $_order_item_id ];
						$_order_item->add_meta_data( '_woosb_parent_id', $product_id, true );
						$_order_item->save();
					}

					// remove the old bundle
					if ( $order_id ) {
						$order->remove_item( $order_item_id );
					}
				}

				$order->save();
			}
		}

		function woosb_hidden_order_item_meta( $hidden ) {
			return array_merge( $hidden, array(
				'_woosb_parent_id',
				'_woosb_ids',
				'_woosb_price',
				'woosb_parent_id',
				'woosb_ids',
				'woosb_price'
			) );
		}

		function woosb_before_order_item_meta( $order_item_id ) {
			if ( $parent_id = wc_get_order_item_meta( $order_item_id, '_woosb_parent_id', true ) ) {
				echo sprintf( esc_html__( '(bundled in %s)', 'woo-product-bundle' ), get_the_title( $parent_id ) );
			}
		}

		function woosb_order_formatted_line_subtotal( $subtotal, $order_item ) {
			if ( isset( $order_item['_woosb_ids'], $order_item['_woosb_price'] ) ) {
				return wc_price( $order_item['_woosb_price'] * $order_item['quantity'] );
			}

			return $subtotal;
		}

		function woosb_cart_item_remove_link( $link, $cart_item_key ) {
			if ( isset( WC()->cart->cart_contents[ $cart_item_key ]['woosb_parent_key'] ) ) {
				$parent_key = WC()->cart->cart_contents[ $cart_item_key ]['woosb_parent_key'];

				if ( isset( WC()->cart->cart_contents[ $parent_key ] ) || array_search( $parent_key, array_column( WC()->cart->cart_contents, 'woosb_key' ) ) !== false ) {
					return '';
				}
			}

			return $link;
		}

		function woosb_cart_item_quantity( $quantity, $cart_item_key, $cart_item ) {
			// add qty as text - not input
			if ( isset( $cart_item['woosb_parent_id'] ) ) {
				return $cart_item['quantity'];
			}

			return $quantity;
		}

		function woosb_get_search_results() {
			$keyword   = sanitize_text_field( $_POST['keyword'] );
			$added_ids = explode( ',', WPCleverWoosb_Helper::woosb_clean_ids( $_POST['ids'] ) );

			if ( ( get_option( '_woosb_search_id', 'no' ) === 'yes' ) && is_numeric( $keyword ) ) {
				// search by id
				$query_args = array(
					'p'         => absint( $keyword ),
					'post_type' => 'product'
				);
			} else {
				$query_args = array(
					'is_woosb'       => true,
					'post_type'      => 'product',
					'post_status'    => array( 'publish', 'private' ),
					's'              => $keyword,
					'posts_per_page' => get_option( '_woosb_search_limit', '5' ),
					'tax_query'      => array(
						array(
							'taxonomy' => 'product_type',
							'field'    => 'slug',
							'terms'    => self::$_types,
							'operator' => 'NOT IN',
						)
					)
				);

				if ( get_option( '_woosb_search_same', 'no' ) !== 'yes' ) {
					$exclude_ids = array();

					if ( is_array( $added_ids ) && count( $added_ids ) > 0 ) {
						foreach ( $added_ids as $added_id ) {
							$added_id_new  = explode( '/', $added_id );
							$exclude_ids[] = absint( isset( $added_id_new[0] ) ? $added_id_new[0] : 0 );
						}
					}

					$query_args['post__not_in'] = $exclude_ids;
				}
			}

			$query = new WP_Query( $query_args );

			if ( $query->have_posts() ) {
				echo '<ul>';

				while ( $query->have_posts() ) {
					$query->the_post();
					$_product = wc_get_product( get_the_ID() );

					if ( ! $_product ) {
						continue;
					}

					$this->woosb_product_data_li( $_product, 1, true );
				}

				echo '</ul>';
				wp_reset_postdata();
			} else {
				echo '<ul><span>' . sprintf( esc_html__( 'No results found for "%s"', 'woo-product-bundle' ), $keyword ) . '</span></ul>';
			}

			die();
		}

		function woosb_search_sku( $query ) {
			if ( $query->is_search && isset( $query->query['is_woosb'] ) ) {
				global $wpdb;

				$sku = $query->query['s'];
				$ids = $wpdb->get_col( $wpdb->prepare( "SELECT post_id FROM $wpdb->postmeta WHERE meta_key='_sku' AND meta_value = %s;", $sku ) );

				if ( ! $ids ) {
					return;
				}

				unset( $query->query['s'], $query->query_vars['s'] );
				$query->query['post__in'] = array();

				foreach ( $ids as $id ) {
					$post = get_post( $id );

					if ( $post->post_type === 'product_variation' ) {
						$query->query['post__in'][]      = $post->post_parent;
						$query->query_vars['post__in'][] = $post->post_parent;
					} else {
						$query->query_vars['post__in'][] = $post->ID;
					}
				}
			}
		}

		function woosb_search_exact( $query ) {
			if ( $query->is_search && isset( $query->query['is_woosb'] ) ) {
				$query->set( 'exact', true );
			}
		}

		function woosb_search_sentence( $query ) {
			if ( $query->is_search && isset( $query->query['is_woosb'] ) ) {
				$query->set( 'sentence', true );
			}
		}

		function woosb_product_type_selector( $types ) {
			$types['woosb'] = esc_html__( 'Smart bundle', 'woo-product-bundle' );

			return $types;
		}

		function woosb_product_data_tabs( $tabs ) {
			$tabs['woosb'] = array(
				'label'  => esc_html__( 'Bundled Products', 'woo-product-bundle' ),
				'target' => 'woosb_settings',
				'class'  => array( 'show_if_woosb' ),
			);

			return $tabs;
		}

		function woosb_single_product_summary_bundles() {
			$this->woosb_show_bundles();
		}

		function woosb_single_product_summary_bundled() {
			$this->woosb_show_bundled();
		}

		function woosb_product_tabs( $tabs ) {
			global $product;
			$product_id = $product->get_id();

			if ( ( get_option( '_woosb_bundled_position', 'above' ) === 'tab' ) && $product->is_type( 'woosb' ) ) {
				$tabs['woosb'] = array(
					'title'    => esc_html__( 'Bundled products', 'woo-product-bundle' ),
					'priority' => 50,
					'callback' => array( $this, 'woosb_product_tab_bundled' )
				);
			}

			if ( ( get_option( '_woosb_bundles_position', 'no' ) === 'tab' ) && ! $product->is_type( 'woosb' ) && $this->woosb_get_bundles( $product_id ) ) {
				$tabs['woosb'] = array(
					'title'    => esc_html__( 'Bundles', 'woo-product-bundle' ),
					'priority' => 50,
					'callback' => array( $this, 'woosb_product_tab_bundles' )
				);
			}

			return $tabs;
		}

		function woosb_product_tab_bundled() {
			$this->woosb_show_bundled();
		}

		function woosb_product_tab_bundles() {
			$this->woosb_show_bundles();
		}

		function woosb_product_filters( $filters ) {
			$filters = str_replace( 'Woosb', esc_html__( 'Smart bundle', 'woo-product-bundle' ), $filters );

			return $filters;
		}

		function woosb_product_data_panels() {
			global $post;
			$post_id = $post->ID;
			$ids     = '';

			if ( get_post_meta( $post_id, 'woosb_ids', true ) ) {
				$ids = get_post_meta( $post_id, 'woosb_ids', true );
			} elseif ( isset( $_GET['woosb_ids'] ) ) {
				$ids = implode( ',', explode( '.', $_GET['woosb_ids'] ) );
			}

			$ids = WPCleverWoosb_Helper::woosb_clean_ids( $ids );

			if ( isset( $_GET['woosb_ids'] ) ) {
				?>
                <script type="text/javascript">
                  jQuery(document).ready(function($) {
                    $('#product-type').val('woosb').trigger('change');
                  });
                </script>
				<?php
			}
			?>
            <div id='woosb_settings' class='panel woocommerce_options_panel woosb_table'>
                <table>
                    <tr>
                        <th><?php esc_html_e( 'Search', 'woo-product-bundle' ); ?> (<a
                                    href="<?php echo admin_url( 'admin.php?page=wpclever-woosb&tab=settings#search' ); ?>"
                                    target="_blank"><?php esc_html_e( 'settings', 'woo-product-bundle' ); ?></a>)
                        </th>
                        <td>
                            <div class="w100">
								<span class="loading"
                                      id="woosb_loading"
                                      style="display: none;"><?php esc_html_e( 'searching...', 'woo-product-bundle' ); ?></span>
                                <input type="search" id="woosb_keyword"
                                       placeholder="<?php esc_html_e( 'Type any keyword to search', 'woo-product-bundle' ); ?>"/>
                                <div id="woosb_results" class="woosb_results" style="display: none;"></div>
                            </div>
                        </td>
                    </tr>
                    <tr class="woosb_tr_space">
                        <th><?php esc_html_e( 'Selected', 'woo-product-bundle' ); ?></th>
                        <td>
                            <div class="w100">
                                <input type="hidden" id="woosb_ids" class="woosb_ids" name="woosb_ids"
                                       value="<?php echo esc_attr( $ids ); ?>"
                                       readonly/>
                                <div id="woosb_selected" class="woosb_selected">
                                    <ul>
										<?php
										if ( ! empty( $ids ) ) {
											$items = $this->woosb_get_bundled( 0, $ids, false );

											if ( is_array( $items ) && count( $items ) > 0 ) {
												foreach ( $items as $item ) {
													$_product = wc_get_product( $item['id'] );

													if ( ! $_product || in_array( $_product->get_type(), self::$_types, true ) ) {
														continue;
													}

													$this->woosb_product_data_li( $_product, $item['qty'] );
												}
											}
										}
										?>
                                    </ul>
                                </div>
                            </div>
                        </td>
                    </tr>
                    <tr class="woosb_tr_space">
                        <th><?php echo esc_html__( 'Regular price', 'woo-product-bundle' ) . ' (' . get_woocommerce_currency_symbol() . ')'; ?></th>
                        <td>
                            <span id="woosb_regular_price"></span>
                        </td>
                    </tr>
                    <tr class="woosb_tr_space">
                        <th><?php esc_html_e( 'Fixed price', 'woo-product-bundle' ); ?></th>
                        <td>
                            <input id="woosb_disable_auto_price" name="woosb_disable_auto_price"
                                   type="checkbox" <?php echo( get_post_meta( $post_id, 'woosb_disable_auto_price', true ) === 'on' ? 'checked' : '' ); ?>/>
                            <label for="woosb_disable_auto_price"><?php esc_html_e( 'Disable auto calculate price.', 'woo-product-bundle' ); ?></label> <?php echo sprintf( esc_html__( 'If checked, %s click here to set price %s by manually.', 'woo-product-bundle' ), '<a id="woosb_set_regular_price">', '</a>' ); ?>
                        </td>
                    </tr>
                    <tr class="woosb_tr_space woosb_tr_show_if_auto_price">
                        <th><?php esc_html_e( 'Discount', 'woo-product-bundle' ); ?></th>
                        <td style="vertical-align: middle; line-height: 30px;">
							<?php $discount_percentage = (float) get_post_meta( $post_id, 'woosb_discount', true ) ?: 0; ?>
                            <input id="woosb_discount" name="woosb_discount" type="number"
                                   min="0" step="0.0001"
                                   max="99.9999"
                                   value="<?php echo esc_attr( $discount_percentage ); ?>"
                                   style="width: 80px"/> <?php esc_html_e( '% or amount', 'woo-product-bundle' ); ?>
                            <input id="woosb_discount_amount"
                                   name="woosb_discount_amount" type="number"
                                   min="0" step="0.0001"
                                   value="<?php echo get_post_meta( $post_id, 'woosb_discount_amount', true ); ?>"
                                   style="width: 80px"/> <?php echo get_woocommerce_currency_symbol(); ?>
                            . <?php esc_html_e( 'If you fill both, the amount will be used.', 'woo-product-bundle' ); ?>
                        </td>
                    </tr>
                    <tr class="woosb_tr_space">
                        <th><?php esc_html_e( 'Custom quantity', 'woo-product-bundle' ); ?></th>
                        <td>
                            <input id="woosb_optional_products" name="woosb_optional_products"
                                   type="checkbox" <?php echo( get_post_meta( $post_id, 'woosb_optional_products', true ) === 'on' ? 'checked' : '' ); ?>/>
                            <label for="woosb_optional_products"><?php esc_html_e( 'Buyer can change the quantity of bundled products.', 'woo-product-bundle' ); ?></label>
                        </td>
                    </tr>
                    <tr class="woosb_tr_space woosb_tr_show_if_optional_products">
                        <th><?php esc_html_e( 'Each item\'s quantity limit', 'woo-product-bundle' ); ?></th>
                        <td>
                            <input id="woosb_limit_each_min_default" name="woosb_limit_each_min_default"
                                   type="checkbox" <?php echo( get_post_meta( $post_id, 'woosb_limit_each_min_default', true ) === 'on' ? 'checked' : '' ); ?>/>
                            <label for="woosb_limit_each_min_default"><?php esc_html_e( 'Use default quantity as min?', 'woo-product-bundle' ); ?></label>
                            <u>or</u> Min <input name="woosb_limit_each_min" type="number"
                                                 min="0"
                                                 value="<?php echo( get_post_meta( $post_id, 'woosb_limit_each_min', true ) ?: '' ); ?>"
                                                 style="width: 60px; float: none"/> Max <input
                                    name="woosb_limit_each_max"
                                    type="number" min="1"
                                    value="<?php echo( get_post_meta( $post_id, 'woosb_limit_each_max', true ) ?: '' ); ?>"
                                    style="width: 60px; float: none"/>
                        </td>
                    </tr>
                    <tr class="woosb_tr_space woosb_tr_show_if_optional_products">
                        <th><?php esc_html_e( 'All items\' quantity limit', 'woo-product-bundle' ); ?></th>
                        <td>
                            Min <input name="woosb_limit_whole_min" type="number"
                                       min="1"
                                       value="<?php echo( get_post_meta( $post_id, 'woosb_limit_whole_min', true ) ?: '' ); ?>"
                                       style="width: 60px; float: none"/> Max <input
                                    name="woosb_limit_whole_max"
                                    type="number" min="1"
                                    value="<?php echo( get_post_meta( $post_id, 'woosb_limit_whole_max', true ) ?: '' ); ?>"
                                    style="width: 60px; float: none"/>
                        </td>
                    </tr>
                    <tr class="woosb_tr_space">
                        <th><?php esc_html_e( 'Shipping fee', 'woo-product-bundle' ); ?></th>
                        <td style="font-style: italic">
                            <select id="woosb_shipping_fee" name="woosb_shipping_fee">
                                <option value="whole" <?php echo( get_post_meta( $post_id, 'woosb_shipping_fee', true ) === 'whole' ? 'selected' : '' ); ?>><?php esc_html_e( 'Apply to the whole bundle', 'woo-product-bundle' ); ?></option>
                                <option value="each" <?php echo( get_post_meta( $post_id, 'woosb_shipping_fee', true ) === 'each' ? 'selected' : '' ); ?>><?php esc_html_e( 'Apply to each bundled product', 'woo-product-bundle' ); ?></option>
                            </select>
                        </td>
                    </tr>
                    <tr class="woosb_tr_space">
                        <th><?php esc_html_e( 'Manage stock', 'woo-product-bundle' ); ?></th>
                        <td>
                            <input id="woosb_manage_stock" name="woosb_manage_stock"
                                   type="checkbox" <?php echo( get_post_meta( $post_id, 'woosb_manage_stock', true ) === 'on' ? 'checked' : '' ); ?>/>
                            <label for="woosb_manage_stock"><?php esc_html_e( 'Enable stock management at bundle level.', 'woo-product-bundle' ); ?></label>
                            <span class="woocommerce-help-tip"
                                  data-tip="<?php esc_attr_e( 'By default, the bundle\' stock was calculated automatically from bundled products. After enabling, please press "Update" then you can change the stock settings on the "Inventory" tab.', 'woo-product-bundle' ); ?>"></span>
                        </td>
                    </tr>
                    <tr class="woosb_tr_space">
                        <th><?php esc_html_e( 'Custom display price', 'woo-product-bundle' ); ?></th>
                        <td>
                            <input type="text" name="woosb_custom_price"
                                   value="<?php echo stripslashes( get_post_meta( $post_id, 'woosb_custom_price', true ) ); ?>"/>
                            E.g: <code>From $10 to $100</code>
                        </td>
                    </tr>
                    <tr class="woosb_tr_space">
                        <th><?php esc_html_e( 'Above text', 'woo-product-bundle' ); ?></th>
                        <td>
                            <div class="w100">
                                        <textarea
                                                name="woosb_before_text"><?php echo stripslashes( get_post_meta( $post_id, 'woosb_before_text', true ) ); ?></textarea>
                            </div>
                        </td>
                    </tr>
                    <tr class="woosb_tr_space">
                        <th><?php esc_html_e( 'Under text', 'woo-product-bundle' ); ?></th>
                        <td>
                            <div class="w100">
                                        <textarea
                                                name="woosb_after_text"><?php echo stripslashes( get_post_meta( $post_id, 'woosb_after_text', true ) ); ?></textarea>
                            </div>
                        </td>
                    </tr>
                </table>
            </div>
			<?php
		}

		function woosb_product_data_li( $product, $qty = 1, $search = false ) {
			$product_id = $product->get_id();

			if ( class_exists( 'WPCleverWoopq' ) && ( get_option( '_woopq_decimal', 'no' ) === 'yes' ) ) {
				$step = '0.000001';
			} else {
				$step = 1;
			}

			if ( $product->is_sold_individually() ) {
				$qty_input = '<input type="number" value="' . esc_attr( $qty ) . '" min="0" step="' . esc_attr( $step ) . '" max="1"/>';
			} else {
				$qty_input = '<input type="number" value="' . esc_attr( $qty ) . '" min="0" step="' . esc_attr( $step ) . '"/>';
			}

			$price     = WPCleverWoosb_Helper::woosb_get_price( $product, 'min' );
			$price_max = WPCleverWoosb_Helper::woosb_get_price( $product, 'max' );

			if ( $search ) {
				$remove_btn = '<span class="remove hint--left" aria-label="' . esc_html__( 'Add', 'woo-product-bundle' ) . '">+</span>';
			} else {
				$remove_btn = '<span class="remove hint--left" aria-label="' . esc_html__( 'Remove', 'woo-product-bundle' ) . '">×</span>';
			}

			$product_name = apply_filters( 'woosb_li_name', $product->get_name(), $product );

			echo '<li ' . ( ! $product->is_in_stock() ? 'class="out-of-stock"' : '' ) . ' data-id="' . esc_attr( $product_id ) . '" data-price="' . esc_attr( $price ) . '" data-price-max="' . esc_attr( $price_max ) . '"><span class="move"></span><span class="qty hint--right" aria-label="' . esc_html__( 'Default quantity', 'woo-product-bundle' ) . '">' . $qty_input . '</span> <span class="data"><span class="name">' . strip_tags( $product_name ) . '</span> <span class="info">' . $product->get_price_html() . '</span> ' . ( $product->is_sold_individually() ? '<span class="info">' . esc_html__( 'sold individually', 'woo-product-bundle' ) . '</span> ' : '' ) . '</span> <span class="type"><a href="' . get_edit_post_link( $product_id ) . '" target="_blank">' . $product->get_type() . ' #' . $product_id . '</a></span> ' . $remove_btn . '</li>';
		}

		function woosb_delete_option_fields( $post_id ) {
			if ( isset( $_POST['product-type'] ) && ( $_POST['product-type'] !== 'woosb' ) ) {
				delete_post_meta( $post_id, 'woosb_ids' );
			}
		}

		function woosb_save_option_fields( $post_id ) {
			if ( isset( $_POST['woosb_ids'] ) && ! empty( $_POST['woosb_ids'] ) ) {
				update_post_meta( $post_id, 'woosb_ids', WPCleverWoosb_Helper::woosb_clean_ids( $_POST['woosb_ids'] ) );
			}

			if ( isset( $_POST['woosb_disable_auto_price'] ) ) {
				update_post_meta( $post_id, 'woosb_disable_auto_price', 'on' );
			} else {
				update_post_meta( $post_id, 'woosb_disable_auto_price', 'off' );
			}

			if ( isset( $_POST['woosb_discount'] ) ) {
				update_post_meta( $post_id, 'woosb_discount', sanitize_text_field( $_POST['woosb_discount'] ) );
			} else {
				update_post_meta( $post_id, 'woosb_discount', 0 );
			}

			if ( isset( $_POST['woosb_discount_amount'] ) ) {
				update_post_meta( $post_id, 'woosb_discount_amount', sanitize_text_field( $_POST['woosb_discount_amount'] ) );
			} else {
				update_post_meta( $post_id, 'woosb_discount_amount', 0 );
			}

			if ( isset( $_POST['woosb_shipping_fee'] ) ) {
				update_post_meta( $post_id, 'woosb_shipping_fee', sanitize_text_field( $_POST['woosb_shipping_fee'] ) );
			}

			if ( isset( $_POST['woosb_optional_products'] ) ) {
				update_post_meta( $post_id, 'woosb_optional_products', 'on' );
			} else {
				update_post_meta( $post_id, 'woosb_optional_products', 'off' );
			}

			if ( isset( $_POST['woosb_manage_stock'] ) ) {
				update_post_meta( $post_id, 'woosb_manage_stock', 'on' );
			} else {
				update_post_meta( $post_id, 'woosb_manage_stock', 'off' );
			}

			if ( isset( $_POST['woosb_custom_price'] ) && ( $_POST['woosb_custom_price'] !== '' ) ) {
				update_post_meta( $post_id, 'woosb_custom_price', addslashes( $_POST['woosb_custom_price'] ) );
			} else {
				delete_post_meta( $post_id, 'woosb_custom_price' );
			}

			if ( isset( $_POST['woosb_limit_each_min'] ) ) {
				update_post_meta( $post_id, 'woosb_limit_each_min', sanitize_text_field( $_POST['woosb_limit_each_min'] ) );
			}

			if ( isset( $_POST['woosb_limit_each_max'] ) ) {
				update_post_meta( $post_id, 'woosb_limit_each_max', sanitize_text_field( $_POST['woosb_limit_each_max'] ) );
			}

			if ( isset( $_POST['woosb_limit_each_min_default'] ) ) {
				update_post_meta( $post_id, 'woosb_limit_each_min_default', 'on' );
			} else {
				update_post_meta( $post_id, 'woosb_limit_each_min_default', 'off' );
			}

			if ( isset( $_POST['woosb_limit_whole_min'] ) ) {
				update_post_meta( $post_id, 'woosb_limit_whole_min', sanitize_text_field( $_POST['woosb_limit_whole_min'] ) );
			}

			if ( isset( $_POST['woosb_limit_whole_max'] ) ) {
				update_post_meta( $post_id, 'woosb_limit_whole_max', sanitize_text_field( $_POST['woosb_limit_whole_max'] ) );
			}

			if ( isset( $_POST['woosb_before_text'] ) && ( $_POST['woosb_before_text'] !== '' ) ) {
				update_post_meta( $post_id, 'woosb_before_text', addslashes( $_POST['woosb_before_text'] ) );
			} else {
				delete_post_meta( $post_id, 'woosb_before_text' );
			}

			if ( isset( $_POST['woosb_after_text'] ) && ( $_POST['woosb_after_text'] !== '' ) ) {
				update_post_meta( $post_id, 'woosb_after_text', addslashes( $_POST['woosb_after_text'] ) );
			} else {
				delete_post_meta( $post_id, 'woosb_after_text' );
			}
		}

		function woosb_add_to_cart_form() {
			global $product;

			if ( ! $product || ! $product->is_type( 'woosb' ) ) {
				return;
			}

			if ( $product->has_variables() ) {
				wp_enqueue_script( 'wc-add-to-cart-variation' );
			}

			if ( ( get_option( '_woosb_bundled_position', 'above' ) === 'above' ) && apply_filters( 'woosb_show_bundled', true, $product->get_id() ) ) {
				$this->woosb_show_bundled();
			}

			wc_get_template( 'single-product/add-to-cart/simple.php' );

			if ( ( get_option( '_woosb_bundled_position', 'above' ) === 'below' ) && apply_filters( 'woosb_show_bundled', true, $product->get_id() ) ) {
				$this->woosb_show_bundled();
			}
		}

		function woosb_add_to_cart_button() {
			global $product;

			if ( $product->is_type( 'woosb' ) && ( $ids = $product->get_ids() ) ) {
				echo '<input name="woosb_ids" class="woosb_ids woosb-ids" type="hidden" value="' . esc_attr( $ids ) . '"/>';
			}
		}

		function woosb_loop_add_to_cart_link( $link, $product ) {
			if ( $product->is_type( 'woosb' ) && ( $product->has_variables() || $product->is_optional() ) ) {
				$link = str_replace( 'ajax_add_to_cart', '', $link );
			}

			return $link;
		}

		function woosb_cart_shipping_packages( $packages ) {
			if ( ! empty( $packages ) ) {
				foreach ( $packages as $package_key => $package ) {
					if ( ! empty( $package['contents'] ) ) {
						foreach ( $package['contents'] as $cart_item_key => $cart_item ) {
							if ( ! empty( $cart_item['woosb_parent_id'] ) && ( get_post_meta( $cart_item['woosb_parent_id'], 'woosb_shipping_fee', true ) !== 'each' ) ) {
								unset( $packages[ $package_key ]['contents'][ $cart_item_key ] );
							}

							if ( ! empty( $cart_item['woosb_ids'] ) && ( get_post_meta( $cart_item['data']->get_id(), 'woosb_shipping_fee', true ) === 'each' ) ) {
								unset( $packages[ $package_key ]['contents'][ $cart_item_key ] );
							}
						}
					}
				}
			}

			return $packages;
		}

		function woosb_cart_contents_weight( $weight ) {
			$weight = 0;

			foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ) {
				if ( $cart_item['data']->has_weight() ) {
					if ( ( ! empty( $cart_item['woosb_parent_id'] ) && ( get_post_meta( $cart_item['woosb_parent_id'], 'woosb_shipping_fee', true ) !== 'each' ) ) || ( ! empty( $cart_item['woosb_ids'] ) && ( get_post_meta( $cart_item['data']->get_id(), 'woosb_shipping_fee', true ) === 'each' ) ) ) {
						$weight += 0;
					} else {
						$weight += (float) $cart_item['data']->get_weight() * $cart_item['quantity'];
					}
				}
			}

			return $weight;
		}

		function woosb_get_price_html( $price, $product ) {
			$product_id = $product->get_id();

			if ( $product->is_type( 'woosb' ) && ( $items = $product->get_items() ) ) {
				$custom_price = stripslashes( get_post_meta( $product_id, 'woosb_custom_price', true ) );

				if ( ! empty( $custom_price ) ) {
					return $custom_price;
				}

				if ( ! $product->is_fixed_price() ) {
					$discount_amount     = $product->get_discount_amount();
					$discount_percentage = $product->get_discount();

					if ( $product->is_optional() ) {
						// min price
						$prices = array();

						foreach ( $items as $item ) {
							$_product = wc_get_product( $item['id'] );

							if ( $_product ) {
								$prices[] = WPCleverWoosb_Helper::woosb_get_price_to_display( $_product, 1, 'min' );
							}
						}

						if ( count( $prices ) > 0 ) {
							$min_price = min( $prices );
						} else {
							$min_price = 0;
						}

						// min whole
						$min_whole = (float) ( get_post_meta( $product_id, 'woosb_limit_whole_min', true ) ?: 1 );

						if ( $min_whole > 0 ) {
							$min_price *= $min_whole;
						}

						// min each
						$min_each = (float) ( get_post_meta( $product_id, 'woosb_limit_each_min', true ) ?: 0 );

						if ( $min_each > 0 ) {
							$min_price = 0;

							foreach ( $prices as $pr ) {
								$min_price += (float) $pr;
							}

							$min_price *= $min_each;
						}

						if ( $discount_amount ) {
							$min_price -= (float) $discount_amount;
						} elseif ( $discount_percentage ) {
							$min_price *= (float) ( 100 - $discount_percentage ) / 100;
						}

						switch ( get_option( '_woosb_price_format', 'from_min' ) ) {
							case 'min_only':
								return wc_price( $min_price );
								break;
							case 'from_min':
								return esc_html__( 'From', 'woo-product-bundle' ) . ' ' . wc_price( $min_price );
								break;
						}
					} elseif ( $product->has_variables() ) {
						$min_price = $max_price = 0;

						foreach ( $items as $item ) {
							$_product = wc_get_product( $item['id'] );

							if ( $_product ) {
								$min_price += WPCleverWoosb_Helper::woosb_get_price_to_display( $_product, $item['qty'], 'min' );
								$max_price += WPCleverWoosb_Helper::woosb_get_price_to_display( $_product, $item['qty'], 'max' );
							}
						}

						if ( $discount_amount ) {
							$min_price -= (float) $discount_amount;
							$max_price -= (float) $discount_amount;
						} elseif ( $discount_percentage ) {
							$min_price *= (float) ( 100 - $discount_percentage ) / 100;
							$max_price *= (float) ( 100 - $discount_percentage ) / 100;
						}

						switch ( get_option( '_woosb_price_format', 'from_min' ) ) {
							case 'min_only':
								return wc_price( $min_price );
								break;
							case 'min_max':
								return wc_price( $min_price ) . ' - ' . wc_price( $max_price );
								break;
							case 'from_min':
								return esc_html__( 'From', 'woo-product-bundle' ) . ' ' . wc_price( $min_price );
								break;
						}
					} else {
						$price = $price_sale = 0;

						foreach ( $items as $item ) {
							$_product = wc_get_product( $item['id'] );

							if ( $_product ) {
								$_price = WPCleverWoosb_Helper::woosb_get_price_to_display( $_product, $item['qty'], 'min' );

								$price += $_price;

								if ( $discount_percentage ) {
									// if haven't discount_amount, apply discount percentage
									$price_sale += round( $_price * ( 100 - $discount_percentage ) / 100, wc_get_price_decimals() );
								}
							}
						}

						if ( $discount_amount ) {
							$price_sale = $price - $discount_amount;
						}

						if ( $price_sale ) {
							return wc_format_sale_price( wc_price( $price ), wc_price( $price_sale ) );
						}

						return wc_price( $price );
					}
				}
			}

			return $price;
		}

		function woosb_order_again_cart_item_data( $data, $cart_item ) {
			if ( isset( $cart_item['woosb_ids'] ) ) {
				$data['woosb_order_again'] = 'yes';
				$data['woosb_ids']         = $cart_item['woosb_ids'];
			}

			if ( isset( $cart_item['woosb_parent_id'] ) ) {
				$data['woosb_order_again'] = 'yes';
				$data['woosb_parent_id']   = $cart_item['woosb_parent_id'];
			}

			return $data;
		}

		function woosb_cart_loaded_from_session() {
			foreach ( WC()->cart->cart_contents as $cart_item_key => $cart_item ) {
				if ( isset( $cart_item['woosb_order_again'], $cart_item['woosb_parent_id'] ) ) {
					WC()->cart->remove_cart_item( $cart_item_key );
				}

				if ( isset( $cart_item['woosb_order_again'], $cart_item['woosb_ids'] ) ) {
					$items = $this->woosb_get_bundled( 0, $cart_item['woosb_ids'] );
					$this->woosb_add_to_cart_items( $items, $cart_item_key, $cart_item['product_id'], $cart_item['quantity'] );
				}
			}
		}

		function woosb_coupon_is_valid_for_product( $valid, $product, $coupon, $cart_item ) {
			if ( ( get_option( '_woosb_coupon_restrictions', 'no' ) === 'both' ) && ( isset( $cart_item['woosb_parent_id'] ) || isset( $cart_item['woosb_ids'] ) ) ) {
				// exclude both bundles and bundled products
				return false;
			}

			if ( ( get_option( '_woosb_coupon_restrictions', 'no' ) === 'bundles' ) && isset( $cart_item['woosb_ids'] ) ) {
				// exclude bundles
				return false;
			}

			if ( ( get_option( '_woosb_coupon_restrictions', 'no' ) === 'bundled' ) && isset( $cart_item['woosb_parent_id'] ) ) {
				// exclude bundled products
				return false;
			}

			return $valid;
		}

		function woosb_show_bundled( $product = null ) {
			if ( ! $product ) {
				global $product;
			}

			if ( ! $product || ! $product->is_type( 'woosb' ) ) {
				return;
			}

			$product_id          = $product->get_id();
			$fixed_price         = $product->is_fixed_price();
			$discount_amount     = $product->get_discount_amount();
			$discount_percentage = $product->get_discount();
			$count               = 1;

			if ( $items = $product->get_items() ) {
				do_action( 'woosb_before_wrap', $product );

				echo '<div class="woosb-wrap woosb-wrap-' . esc_attr( $product_id ) . ' woosb-bundled" data-id="' . esc_attr( $product_id ) . '">';

				if ( $before_text = apply_filters( 'woosb_before_text', get_post_meta( $product_id, 'woosb_before_text', true ), $product_id ) ) {
					echo '<div class="woosb-before-text woosb-text">' . do_shortcode( stripslashes( $before_text ) ) . '</div>';
				}

				do_action( 'woosb_before_table', $product );
				?>
                <div class="woosb-products"
                     data-discount-amount="<?php echo esc_attr( $discount_amount ); ?>"
                     data-discount="<?php echo esc_attr( $discount_percentage ); ?>"
                     data-fixed-price="<?php echo esc_attr( $fixed_price ? 'yes' : 'no' ); ?>"
                     data-price="<?php echo esc_attr( wc_get_price_to_display( $product ) ); ?>"
                     data-variables="<?php echo esc_attr( $product->has_variables() ? 'yes' : 'no' ); ?>"
                     data-optional="<?php echo esc_attr( $product->is_optional() ? 'yes' : 'no' ); ?>"
                     data-min="<?php echo esc_attr( get_post_meta( $product_id, 'woosb_limit_whole_min', true ) ?: 1 ); ?>"
                     data-max="<?php echo esc_attr( get_post_meta( $product_id, 'woosb_limit_whole_max', true ) ?: '' ); ?>">

					<?php
					foreach ( $items as $item ) {
						$_product = wc_get_product( $item['id'] );

						if ( ! $_product || in_array( $_product->get_type(), self::$_types, true ) ) {
							continue;
						}

						if ( ! apply_filters( 'woosb_item_visible', true, $_product, $product ) ) {
							continue;
						}

						$_qty = $item['qty'];
						$_min = 0;
						$_max = 1000;

						if ( $product->is_optional() ) {
							if ( get_post_meta( $product_id, 'woosb_limit_each_min_default', true ) === 'on' ) {
								$_min = $_qty;
							} else {
								$_min = absint( get_post_meta( $product_id, 'woosb_limit_each_min', true ) ?: 0 );
							}

							$_max = absint( get_post_meta( $product_id, 'woosb_limit_each_max', true ) ?: 1000 );

							if ( $_qty < $_min ) {
								$_qty = $_min;
							}

							if ( ( $_max > $_min ) && ( $_qty > $_max ) ) {
								$_qty = $_max;
							}
						}

						if ( ( ! $_product->is_in_stock() || ! $_product->has_enough_stock( $_qty ) ) && ( get_option( '_woosb_exclude_unpurchasable', 'no' ) === 'yes' ) ) {
							$_qty = 0;
						}
						?>
                        <div class="woosb-product"
                             data-name="<?php echo esc_attr( $_product->get_name() ); ?>"
                             data-id="<?php echo esc_attr( $_product->is_type( 'variable' ) ? 0 : $item['id'] ); ?>"
                             data-price="<?php echo esc_attr( WPCleverWoosb_Helper::woosb_get_price_to_display( $_product, 1, 'min' ) ); ?>"
                             data-qty="<?php echo esc_attr( $_qty ); ?>" data-order="<?php echo esc_attr( $count ); ?>">
							<?php
							do_action( 'woosb_before_item', $_product, $product, $count );

							if ( get_option( '_woosb_bundled_thumb', 'yes' ) !== 'no' ) { ?>
                                <div class="woosb-thumb">
									<?php if ( $_product->is_visible() && ( get_option( '_woosb_bundled_link', 'yes' ) !== 'no' ) ) {
										echo '<a ' . ( get_option( '_woosb_bundled_link', 'yes' ) === 'yes_popup' ? 'class="woosq-btn no-ajaxy" data-id="' . $item['id'] . '"' : '' ) . ' href="' . esc_url( $_product->get_permalink() ) . '" ' . ( get_option( '_woosb_bundled_link', 'yes' ) === 'yes_blank' ? 'target="_blank"' : '' ) . '>';
									} ?>
                                    <div class="woosb-thumb-ori">
										<?php echo apply_filters( 'woosb_item_thumbnail', $_product->get_image(), $_product ); ?>
                                    </div>
                                    <div class="woosb-thumb-new"></div>
									<?php if ( $_product->is_visible() && ( get_option( '_woosb_bundled_link', 'yes' ) !== 'no' ) ) {
										echo '</a>';
									} ?>
                                </div><!-- /woosb-thumb -->
							<?php } ?>

                            <div class="woosb-title">
								<?php
								do_action( 'woosb_before_item_name', $_product );

								echo '<div class="woosb-title-inner">';

								if ( ( get_option( '_woosb_bundled_qty', 'yes' ) === 'yes' ) && ( get_post_meta( $product_id, 'woosb_optional_products', true ) !== 'on' ) ) {
									echo apply_filters( 'woosb_item_qty', $item['qty'] . ' × ', $item['qty'], $_product );
								}

								$_name = '';

								if ( $_product->is_visible() && ( get_option( '_woosb_bundled_link', 'yes' ) !== 'no' ) ) {
									$_name .= '<a ' . ( get_option( '_woosb_bundled_link', 'yes' ) === 'yes_popup' ? 'class="woosq-btn no-ajaxy" data-id="' . $item['id'] . '"' : '' ) . ' href="' . esc_url( $_product->get_permalink() ) . '" ' . ( get_option( '_woosb_bundled_link', 'yes' ) === 'yes_blank' ? 'target="_blank"' : '' ) . '>';
								}

								if ( $_product->is_in_stock() && $_product->has_enough_stock( $_qty ) ) {
									$_name .= $_product->get_name();
								} else {
									$_name .= '<s>' . $_product->get_name() . '</s>';
								}

								if ( $_product->is_visible() && ( get_option( '_woosb_bundled_link', 'yes' ) !== 'no' ) ) {
									$_name .= '</a>';
								}

								echo apply_filters( 'woosb_item_name', $_name, $_product, $product, $count );
								echo '</div>';

								do_action( 'woosb_after_item_name', $_product );

								if ( get_option( '_woosb_bundled_description', 'no' ) === 'yes' ) {
									echo '<div class="woosb-description">' . apply_filters( 'woosb_item_description', $_product->get_short_description(), $_product ) . '</div>';
								}

								echo '<div class="woosb-availability">' . wc_get_stock_html( $_product ) . '</div>';
								?>
                            </div>

							<?php if ( get_post_meta( $product_id, 'woosb_optional_products', true ) === 'on' ) {
								if ( ( $_product->get_backorders() === 'no' ) && ( $_product->get_stock_status() !== 'onbackorder' ) && is_int( $_product->get_stock_quantity() ) && ( $_product->get_stock_quantity() < $_max ) ) {
									$_max = $_product->get_stock_quantity();
								}

								if ( $_product->is_sold_individually() ) {
									$_max = 1;
								}

								if ( $_product->is_in_stock() ) {
									echo '<div class="woosb-qty' . ( get_option( '_woosb_plus_minus', 'no' ) === 'yes' ? ' woosb-qty-plus-minus' : '' ) . '">';

									if ( get_option( '_woosb_plus_minus', 'no' ) === 'yes' ) {
										echo '<div class="woosb-qty-input">';
										echo '<div class="woosb-qty-input-minus">-</div>';
									}

									woocommerce_quantity_input( array(
										'input_value' => $_qty,
										'min_value'   => $_min,
										'max_value'   => $_max
									), $_product );

									if ( get_option( '_woosb_plus_minus', 'no' ) === 'yes' ) {
										echo '<div class="woosb-qty-input-plus">+</div>';
										echo '</div><!-- /woosb-qty-input -->';
									}

									echo '</div>';
								} else { ?>
                                    <div class="woosb-qty">
                                        <input type="number" class="input-text qty text" value="0"
                                               disabled/>
                                    </div>
								<?php }
							}

							if ( get_option( '_woosb_bundled_price', 'price' ) !== 'no' ) { ?>
                                <div class="woosb-price">
                                    <div class="woosb-price-ori">
										<?php
										$_ori_price = $_product->get_price();
										$_get_price = WPCleverWoosb_Helper::woosb_get_price( $_product );

										if ( ! $product->is_fixed_price() && ( $discount_percentage = $product->get_discount() ) ) {
											$_new_price     = true;
											$_product_price = $_get_price * ( 100 - $discount_percentage ) / 100;
										} else {
											$_new_price     = false;
											$_product_price = $_get_price;
										}

										switch ( get_option( '_woosb_bundled_price', 'price' ) ) {
											case 'price':
												if ( $_new_price ) {
													$_price = wc_format_sale_price( wc_get_price_to_display( $_product, array( 'price' => $_get_price ) ), wc_get_price_to_display( $_product, array( 'price' => $_product_price ) ) );
												} else {
													if ( $_get_price > $_ori_price ) {
														$_price = wc_price( WPCleverWoosb_Helper::woosb_get_price_to_display( $_product ) );
													} else {
														$_price = $_product->get_price_html();
													}
												}

												break;
											case 'subtotal':
												if ( $_new_price ) {
													$_price = wc_format_sale_price( wc_get_price_to_display( $_product, array(
														'price' => $_get_price,
														'qty'   => $item['qty']
													) ), wc_get_price_to_display( $_product, array(
														'price' => $_product_price,
														'qty'   => $item['qty']
													) ) );
												} else {
													$_price = wc_price( WPCleverWoosb_Helper::woosb_get_price_to_display( $_product, $item['qty'] ) );
												}

												break;
											default:
												$_price = $_product->get_price_html();
										}

										echo apply_filters( 'woosb_item_price', $_price, $_product );
										?>
                                    </div>
                                    <div class="woosb-price-new"></div>
									<?php do_action( 'woosb_after_item_price', $_product ); ?>
                                </div>
							<?php }

							do_action( 'woosb_after_item', $_product, $product, $count );
							?>
                        </div>
						<?php
						$count ++;
					}
					?>

                </div>
				<?php
				if ( ! $product->is_fixed_price() && ( $product->has_variables() || $product->is_optional() ) ) {
					echo '<div class="woosb-total woosb-text"></div>';
				}

				echo '<div class="woosb-alert woosb-text" style="display: none"></div>';

				do_action( 'woosb_after_table', $product );

				if ( $after_text = apply_filters( 'woosb_after_text', get_post_meta( $product_id, 'woosb_after_text', true ), $product_id ) ) {
					echo '<div class="woosb-after-text woosb-text">' . do_shortcode( stripslashes( $after_text ) ) . '</div>';
				}

				echo '</div>';

				do_action( 'woosb_after_wrap', $product );
			}
		}

		function woosb_show_bundles( $product = null ) {
			if ( ! $product ) {
				global $product;
			}

			if ( ! $product || $product->is_type( 'woosb' ) ) {
				return;
			}

			$product_id = $product->get_id();

			if ( $bundles = $this->woosb_get_bundles( $product_id ) ) {
				echo '<div class="woosb-bundles">';

				if ( $before_text = apply_filters( 'woosb_bundles_before_text', get_option( '_woosb_bundles_before_text' ) ) ) {
					echo '<div class="woosb_before_text woosb-before-text woosb-text">' . do_shortcode( stripslashes( $before_text ) ) . '</div>';
				}

				do_action( 'woosb_before_bundles', $product );
				echo '<div class="woosb-products">';

				foreach ( $bundles as $bundle ) {
					echo '<div class="woosb-product">';
					echo '<div class="woosb-thumb">' . $bundle->get_image() . '</div>';
					echo '<div class="woosb-title"><a ' . ( get_option( '_woosb_bundled_link', 'yes' ) === 'yes_popup' ? 'class="woosq-btn no-ajaxy" data-id="' . $bundle->get_id() . '"' : '' ) . ' href="' . $bundle->get_permalink() . '" ' . ( get_option( '_woosb_bundled_link', 'yes' ) === 'yes_blank' ? 'target="_blank"' : '' ) . '>' . $bundle->get_name() . '</a></div>';
					echo '<div class="woosb-price">';

					switch ( get_option( '_woosb_bundled_price', 'price' ) ) {
						case 'price':
							echo wc_price( wc_get_price_to_display( $bundle ) );

							break;
						default:
							echo $bundle->get_price_html();

							break;
					}

					echo '</div><!-- /woosb-price -->';
					echo '</div><!-- /woosb-product -->';
				}

				echo '</div><!-- /woosb-products -->';
				wp_reset_postdata();

				if ( $after_text = apply_filters( 'woosb_bundles_after_text', get_option( '_woosb_bundles_after_text' ) ) ) {
					echo '<div class="woosb_after_text woosb-after-text woosb-text">' . do_shortcode( stripslashes( $after_text ) ) . '</div>';
				}

				do_action( 'woosb_after_bundles', $product );
				echo '</div><!-- /woosb-bundles -->';
			}
		}

		function woosb_get_bundled( $product_id, $ids = null, $compact = true ) {
			$bundled = array();
			$ids     = ! is_null( $ids ) ? $ids : get_post_meta( $product_id, 'woosb_ids', true );

			if ( ! empty( $ids ) ) {
				$items = explode( ',', $ids );

				if ( is_array( $items ) && count( $items ) > 0 ) {
					foreach ( $items as $item ) {
						$_arr   = explode( '/', $item );
						$_id    = absint( isset( $_arr[0] ) ? $_arr[0] : 0 );
						$_qty   = (float) ( isset( $_arr[1] ) ? $_arr[1] : 1 );
						$has_id = false;

						if ( $compact && ( count( $bundled ) > 0 ) ) {
							foreach ( $bundled as $key => $bundled_item ) {
								if ( $bundled_item['id'] === $_id ) {
									$bundled[ $key ]['qty'] += $_qty;
									$has_id                 = true;
									break;
								}
							}
						}

						if ( $has_id ) {
							continue;
						}

						$bundled[] = array(
							'id'  => $_id,
							'qty' => $_qty
						);
					}
				}
			}

			if ( count( $bundled ) > 0 ) {
				return $bundled;
			}

			return false;
		}

		function woosb_get_bundles( $product_id, $per_page = 500, $offset = 0 ) {
			$bundles        = array();
			$product_id_str = $product_id . '/';
			$query_args     = array(
				'post_type'      => 'product',
				'post_status'    => 'publish',
				'posts_per_page' => $per_page,
				'offset'         => $offset,
				'tax_query'      => array(
					array(
						'taxonomy' => 'product_type',
						'field'    => 'slug',
						'terms'    => array( 'woosb' ),
						'operator' => 'IN',
					)
				),
				'meta_query'     => array(
					array(
						'key'     => 'woosb_ids',
						'value'   => $product_id_str,
						'compare' => 'LIKE',
					)
				)
			);
			$query          = new WP_Query( $query_args );

			if ( $query->have_posts() ) {
				while ( $query->have_posts() ) {
					$query->the_post();
					$_product = wc_get_product( get_the_ID() );

					if ( ! $_product ) {
						continue;
					}

					$bundles[] = $_product;
				}

				wp_reset_query();
			}

			return ! empty( $bundles ) ? $bundles : false;
		}

		function woosb_shortcode_form() {
			ob_start();
			$this->woosb_add_to_cart_form();
			$form = ob_get_contents();
			ob_end_clean();

			return $form;
		}

		function woosb_shortcode_bundled() {
			ob_start();
			$this->woosb_show_bundled();
			$bundled = ob_get_contents();
			ob_end_clean();

			return $bundled;
		}

		function woosb_shortcode_bundles() {
			ob_start();
			$this->woosb_show_bundles();
			$bundles = ob_get_contents();
			ob_end_clean();

			return $bundles;
		}

		function woosb_low_stock( $product ) {
			if ( 'no' === get_option( 'woocommerce_notify_low_stock', 'yes' ) ) {
				return;
			}

			$message = '';
			$subject = sprintf( '[%s] %s', WC_Email::get_blogname(), esc_html__( 'Bundle(s) low in stock', 'woo-product-bundle' ) );

			$product_id = $product->get_id();
			if ( $bundles = $this->woosb_get_bundles( $product_id ) ) {
				foreach ( $bundles as $bundle ) {
					$message .= sprintf( esc_html__( '%s is low in stock.', 'woo-product-bundle' ), html_entity_decode( strip_tags( $bundle->get_formatted_name() ), ENT_QUOTES, get_bloginfo( 'charset' ) ) ) . ' <a href="' . get_edit_post_link( $bundle->get_id() ) . '" target="_blank">#' . $bundle->get_id() . '</a><br/>';
				}

				$message .= sprintf( esc_html__( '%1$s is low in stock. There are %2$d left.', 'woo-product-bundle' ), html_entity_decode( strip_tags( $product->get_formatted_name() ), ENT_QUOTES, get_bloginfo( 'charset' ) ), html_entity_decode( strip_tags( $product->get_stock_quantity() ) ) ) . ' <a href="' . get_edit_post_link( $product_id ) . '" target="_blank">#' . $product_id . '</a>';

				wp_mail(
					apply_filters( 'woocommerce_email_recipient_low_stock', get_option( 'woocommerce_stock_email_recipient' ), $bundle ),
					apply_filters( 'woocommerce_email_subject_low_stock', $subject, $bundle ),
					apply_filters( 'woocommerce_email_content_low_stock', $message, $bundle ),
					apply_filters( 'woocommerce_email_headers', 'Content-Type: text/html; charset=UTF-8', 'low_stock', $bundle ),
					apply_filters( 'woocommerce_email_attachments', array(), 'low_stock', $bundle )
				);
			}
		}

		function woosb_display_post_states( $states, $post ) {
			if ( 'product' == get_post_type( $post->ID ) ) {
				if ( ( $_product = wc_get_product( $post->ID ) ) && $_product->is_type( 'woosb' ) ) {
					$count = 0;

					if ( $items = $_product->get_items() ) {
						$count = count( $items );
					}

					$states[] = apply_filters( 'woosb_post_states', '<span class="woosb-state">' . sprintf( esc_html__( 'Bundle (%s)', 'woo-product-bundle' ), $count ) . '</span>', $count, $_product );
				}
			}

			return $states;
		}

		function woosb_bulk_hooks() {
			if ( current_user_can( 'edit_products' ) ) {
				add_filter( 'bulk_actions-edit-product', array( $this, 'woosb_register_bulk_actions' ) );
				add_filter( 'handle_bulk_actions-edit-product', array(
					$this,
					'woosb_bulk_action_handler'
				), 10, 3 );
				add_action( 'admin_notices', array( $this, 'woosb_bulk_action_admin_notice' ) );
			}
		}

		function woosb_register_bulk_actions( $bulk_actions ) {
			$bulk_actions['woosb_create_bundle'] = esc_html__( 'Create a Smart bundle', 'woo-product-bundle' );

			return $bulk_actions;
		}

		function woosb_bulk_action_handler( $redirect_to, $doaction, $post_ids ) {
			if ( $doaction !== 'woosb_create_bundle' ) {
				return $redirect_to;
			}

			$ids         = implode( '.', $post_ids );
			$redirect_to = add_query_arg( 'woosb_ids', $ids, admin_url( 'post-new.php?post_type=product' ) );

			return $redirect_to;
		}

		function woosb_bulk_action_admin_notice() {
			if ( ! empty( $_REQUEST['woosb_ids'] ) ) {
				$ids = explode( '.', $_REQUEST['woosb_ids'] );
				echo '<div id="message" class="updated fade">' . sprintf( esc_html__( 'Added %s product(s) to this bundle.', 'woo-product-bundle' ), count( $ids ) ) . '</div>';
			}
		}

		function woosb_no_stock( $product ) {
			if ( 'no' === get_option( 'woocommerce_notify_no_stock', 'yes' ) ) {
				return;
			}

			$message    = '';
			$subject    = sprintf( '[%s] %s', wp_specialchars_decode( get_option( 'blogname' ), ENT_QUOTES ), esc_html__( 'Bundle(s) out of stock', 'woo-product-bundle' ) );
			$product_id = $product->get_id();

			if ( $bundles = $this->woosb_get_bundles( $product_id ) ) {
				foreach ( $bundles as $bundle ) {
					$message .= sprintf( esc_html__( '%s is out of stock.', 'woo-product-bundle' ), html_entity_decode( strip_tags( $bundle->get_formatted_name() ), ENT_QUOTES, get_bloginfo( 'charset' ) ) ) . ' <a href="' . get_edit_post_link( $bundle->get_id() ) . '" target="_blank">#' . $bundle->get_id() . '</a><br/>';
				}

				$message .= sprintf( esc_html__( '%s is out of stock.', 'woo-product-bundle' ), html_entity_decode( strip_tags( $product->get_formatted_name() ), ENT_QUOTES, get_bloginfo( 'charset' ) ) ) . ' <a href="' . get_edit_post_link( $product_id ) . '" target="_blank">#' . $product_id . '</a>';

				wp_mail(
					apply_filters( 'woocommerce_email_recipient_no_stock', get_option( 'woocommerce_stock_email_recipient' ), $bundle ),
					apply_filters( 'woocommerce_email_subject_no_stock', $subject, $bundle ),
					apply_filters( 'woocommerce_email_content_no_stock', $message, $bundle ),
					apply_filters( 'woocommerce_email_headers', 'Content-Type: text/html; charset=UTF-8', 'no_stock', $bundle ),
					apply_filters( 'woocommerce_email_attachments', array(), 'no_stock', $bundle )
				);
			}
		}
	}
}

function WPCleverWoosb() {
	return WPCleverWoosb::instance();
}