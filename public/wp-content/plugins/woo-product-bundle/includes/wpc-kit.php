<?php
defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'WPCleverKit' ) ) {
	class WPCleverKit {
		protected static $_plugins = array(
			'woo-product-bundle'             => array(
				'name' => 'WPC Product Bundles for WooCommerce',
				'slug' => 'woo-product-bundle',
				'file' => 'wpc-product-bundles.php'
			),
			'wpc-composite-products'         => array(
				'name' => 'WPC Composite Products for WooCommerce',
				'slug' => 'wpc-composite-products',
				'file' => 'wpc-composite-products.php'
			),
			'wpc-grouped-product'            => array(
				'name' => 'WPC Grouped Product for WooCommerce',
				'slug' => 'wpc-grouped-product',
				'file' => 'wpc-grouped-product.php'
			),
			'woo-bought-together'            => array(
				'name' => 'WPC Frequently Bought Together for WooCommerce',
				'slug' => 'woo-bought-together',
				'file' => 'wpc-frequently-bought-together.php'
			),
			'woo-smart-compare'              => array(
				'name' => 'WPC Smart Compare for WooCommerce',
				'slug' => 'woo-smart-compare',
				'file' => 'wpc-smart-compare.php'
			),
			'woo-smart-quick-view'           => array(
				'name' => 'WPC Smart Quick View for WooCommerce',
				'slug' => 'woo-smart-quick-view',
				'file' => 'wpc-smart-quick-view.php'
			),
			'woo-smart-wishlist'             => array(
				'name' => 'WPC Smart Wishlist for WooCommerce',
				'slug' => 'woo-smart-wishlist',
				'file' => 'wpc-smart-wishlist.php'
			),
			'woo-fly-cart'                   => array(
				'name' => 'WPC Fly Cart for WooCommerce',
				'slug' => 'woo-fly-cart',
				'file' => 'wpc-fly-cart.php'
			),
			'wpc-force-sells'                => array(
				'name' => 'WPC Force Sells for WooCommerce',
				'slug' => 'wpc-force-sells',
				'file' => 'wpc-force-sells.php'
			),
			'woo-added-to-cart-notification' => array(
				'name' => 'WPC Added To Cart Notification for WooCommerce',
				'slug' => 'woo-added-to-cart-notification',
				'file' => 'wpc-added-to-cart-notification.php'
			),
			'wpc-ajax-add-to-cart'           => array(
				'name' => 'WPC Ajax Add to Cart for WooCommerce',
				'slug' => 'wpc-ajax-add-to-cart',
				'file' => 'wpc-ajax-add-to-cart.php'
			),
			'wpc-product-quantity'           => array(
				'name' => 'WPC Product Quantity for WooCommerce',
				'slug' => 'wpc-product-quantity',
				'file' => 'wpc-product-quantity.php'
			),
			'wpc-variations-radio-buttons'   => array(
				'name' => 'WPC Variations Radio Buttons for WooCommerce',
				'slug' => 'wpc-variations-radio-buttons',
				'file' => 'wpc-variations-radio-buttons.php'
			),
			'wpc-product-tabs'               => array(
				'name' => 'WPC Product Tabs for WooCommerce',
				'slug' => 'wpc-product-tabs',
				'file' => 'wpc-product-tabs.php'
			),
			'woo-product-timer'              => array(
				'name' => 'WPC Product Timer for WooCommerce',
				'slug' => 'woo-product-timer',
				'file' => 'wpc-product-timer.php'
			),
			'wpc-countdown-timer'            => array(
				'name' => 'WPC Countdown Timer for WooCommerce',
				'slug' => 'wpc-countdown-timer',
				'file' => 'wpc-countdown-timer.php'
			),
			'wpc-product-table'              => array(
				'name' => 'WPC Product Table for WooCommerce',
				'slug' => 'wpc-product-table',
				'file' => 'wpc-product-table.php'
			),
			'wpc-name-your-price'            => array(
				'name' => 'WPC Name Your Price for WooCommerce',
				'slug' => 'wpc-name-your-price',
				'file' => 'wpc-name-your-price.php'
			)
		);

		function __construct() {
			// admin scripts
			add_action( 'admin_enqueue_scripts', array( $this, 'admin_scripts' ) );

			// settings page
			add_action( 'admin_menu', array( $this, 'admin_menu' ) );
		}

		function admin_scripts() {
			wp_enqueue_style( 'wpckit-backend', WPC_URI . 'assets/kit/css/backend.css' );
			wp_enqueue_script( 'wpckit-backend', WPC_URI . 'assets/kit/js/backend.js', array(
				'jquery'
			) );
		}

		function admin_menu() {
			add_submenu_page( 'wpclever', esc_html__( 'WPC Essential Kit', 'wpckit' ), esc_html__( 'Essential Kit', 'wpckit' ), 'manage_options', 'wpclever-kit', array(
				$this,
				'settings_page'
			) );
		}

		function settings_page() {
			add_thickbox();
			?>
            <div class="wpclever_settings_page wrap">
                <br/>
                <div class="wp-list-table widefat plugin-install-network">
					<?php
					if ( ! function_exists( 'plugins_api' ) ) {
						include_once ABSPATH . 'wp-admin/includes/plugin-install.php';
					}

					if ( isset( $_GET['action'], $_GET['plugin'] ) && ( $_GET['action'] === 'activate' ) && wp_verify_nonce( $_GET['_wpnonce'], 'activate-plugin_' . $_GET['plugin'] ) ) {
						activate_plugin( $_GET['plugin'], '', false, true );
					}

					if ( isset( $_GET['action'], $_GET['plugin'] ) && ( $_GET['action'] === 'deactivate' ) && wp_verify_nonce( $_GET['_wpnonce'], 'deactivate-plugin_' . $_GET['plugin'] ) ) {
						deactivate_plugins( $_GET['plugin'], '', false, true );
					}

					$updated      = false;
					$plugins_info = get_site_transient( 'wpclever_plugins_info' );

					foreach ( self::$_plugins as $_plugin ) {
						if ( ! isset( $plugins_info[ $_plugin['slug'] ] ) ) {
							$_plugin_info = plugins_api(
								'plugin_information',
								array(
									'slug'   => $_plugin['slug'],
									'fields' => array(
										'short_description',
										'version',
										'active_installs',
										'downloaded',
									),
								)
							);

							if ( ! is_wp_error( $_plugin_info ) ) {
								$plugin_info                      = array(
									'name'              => $_plugin_info->name,
									'slug'              => $_plugin_info->slug,
									'version'           => $_plugin_info->version,
									'rating'            => $_plugin_info->rating,
									'num_ratings'       => $_plugin_info->num_ratings,
									'downloads'         => $_plugin_info->downloaded,
									'last_updated'      => $_plugin_info->last_updated,
									'homepage'          => $_plugin_info->homepage,
									'short_description' => $_plugin_info->short_description,
									'active_installs'   => $_plugin_info->active_installs,
								);
								$plugins_info[ $_plugin['slug'] ] = $plugin_info;
								$updated                          = true;
							} else {
								$plugin_info = array(
									'name' => $_plugin['name'],
									'slug' => $_plugin['slug']
								);
							}
						} else {
							$plugin_info = $plugins_info[ $_plugin['slug'] ];
						}

						$details_link = network_admin_url(
							'plugin-install.php?tab=plugin-information&amp;plugin=' . $plugin_info['slug'] . '&amp;TB_iframe=true&amp;width=600&amp;height=550'
						);
						?>
                        <div class="plugin-card <?php echo esc_attr( $_plugin['slug'] ); ?>"
                             id="<?php echo esc_attr( $_plugin['slug'] ); ?>">
                            <div class="plugin-card-top">
                                <a href="<?php echo esc_url( $details_link ); ?>" class="thickbox">
                                    <img src="<?php echo esc_url( WPC_URI . 'assets/kit/images/' . $_plugin['slug'] . '.png' ); ?>"
                                         class="plugin-icon" alt=""/>
                                </a>
                                <div class="name column-name">
                                    <h3>
                                        <a class="thickbox" href="<?php echo esc_url( $details_link ); ?>">
											<?php echo $plugin_info['name']; ?>
                                        </a>
                                    </h3>
                                </div>
                                <div class="action-links">
                                    <ul class="plugin-action-buttons">
                                        <li>
											<?php if ( $this->is_plugin_installed( $_plugin ) ) {
												if ( $this->is_plugin_active( $_plugin ) ) {
													?>
                                                    <a href="<?php echo esc_url( $this->deactivate_plugin_link( $_plugin ) ); ?>"
                                                       class="button deactivate-now">
														<?php esc_html_e( 'Deactivate', 'wpckit' ); ?>
                                                    </a>
													<?php
												} else {
													?>
                                                    <a href="<?php echo esc_url( $this->activate_plugin_link( $_plugin ) ); ?>"
                                                       class="button activate-now">
														<?php esc_html_e( 'Activate', 'wpckit' ); ?>
                                                    </a>
													<?php
												}
											} else { ?>
                                                <a href="<?php echo esc_url( $this->install_plugin_link( $_plugin ) ); ?>"
                                                   class="install-now button wpckit-install-now">
													<?php esc_html_e( 'Install Now', 'wpckit' ); ?>
                                                </a>
											<?php } ?>
                                        </li>
                                        <li>
                                            <a href="<?php echo esc_url( $details_link ); ?>"
                                               class="thickbox open-plugin-details-modal"
                                               aria-label="<?php echo esc_attr( sprintf( esc_html__( 'More information about %s', 'wpckit' ), $plugin_info['name'] ) ); ?>"
                                               data-title="<?php echo esc_attr( $plugin_info['name'] ); ?>">
												<?php esc_html_e( 'More Details', 'wpckit' ); ?>
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                                <div class="desc column-description">
                                    <p><?php echo esc_html( $plugin_info['short_description'] ); ?></p>
                                    <!--
									<p class="authors">
										<cite>
											By <a href="https://wpclever.net" target="_blank"
												  title="plugin author WPClever.net">WPClever.net</a>
										</cite>
									</p>
									-->
                                </div>
                            </div>
							<?php if ( $this->is_plugin_installed( $_plugin, true ) ) {
								?>
                                <div class="plugin-card-bottom premium">
                                    <div class="text">
                                        <strong>✓ Premium version was installed.</strong><br/>
                                        Please deactivate the free version when using the premium version.
                                    </div>
                                    <div class="btn">
										<?php
										if ( $this->is_plugin_active( $_plugin, true ) ) {
											?>
                                            <a href="<?php echo esc_url( $this->deactivate_plugin_link( $_plugin, true ) ); ?>"
                                               class="button deactivate-now">
												<?php esc_html_e( 'Deactivate', 'wpckit' ); ?>
                                            </a>
											<?php
										} else {
											?>
                                            <a href="<?php echo esc_url( $this->activate_plugin_link( $_plugin, true ) ); ?>"
                                               class="button activate-now">
												<?php esc_html_e( 'Activate', 'wpckit' ); ?>
                                            </a>
											<?php
										}
										?>
                                    </div>
                                </div>
								<?php
							} else {
								echo '<div class="plugin-card-bottom">';

								if ( isset( $plugin_info['rating'], $plugin_info['num_ratings'] ) ) { ?>
                                    <div class="vers column-rating">
										<?php
										wp_star_rating(
											array(
												'rating' => $plugin_info['rating'],
												'type'   => 'percent',
												'number' => $plugin_info['num_ratings'],
											)
										);
										?>
                                        <span class="num-ratings">(<?php echo esc_html( number_format_i18n( $plugin_info['num_ratings'] ) ); ?>)</span>
                                    </div>
								<?php }

								if ( isset( $plugin_info['version'] ) ) { ?>
                                    <div class="column-updated">
										<?php echo esc_html__( 'Version', 'wpckit' ) . ' ' . $plugin_info['version']; ?>
                                    </div>
								<?php }

								if ( isset( $plugin_info['active_installs'] ) ) { ?>
                                    <div class="column-downloaded">
										<?php echo number_format_i18n( $plugin_info['active_installs'] ) . esc_html__( '+ Active Installations', 'wpckit' ); ?>
                                    </div>
								<?php }

								if ( isset( $plugin_info['last_updated'] ) ) { ?>
                                    <div class="column-compatibility">
                                        <strong><?php esc_html_e( 'Last Updated:', 'wpckit' ); ?></strong>
                                        <span><?php printf( esc_html__( '%s ago', 'wpckit' ), esc_html( human_time_diff( strtotime( $plugin_info['last_updated'] ) ) ) ); ?></span>
                                    </div>
								<?php }

								echo '</div>';
							} ?>
                        </div>
						<?php
					}

					if ( $updated ) {
						set_site_transient( 'wpclever_plugins_info', $plugins_info, 24 * HOUR_IN_SECONDS );
					}
					?>
                </div>
            </div>
			<?php
		}

		public function is_plugin_installed( $plugin, $premium = false ) {
			if ( $premium ) {
				return file_exists( WP_PLUGIN_DIR . '/' . $plugin['slug'] . '-premium/' . $plugin['file'] );
			} else {
				return file_exists( WP_PLUGIN_DIR . '/' . $plugin['slug'] . '/' . $plugin['file'] );
			}
		}

		public function is_plugin_active( $plugin, $premium = false ) {
			if ( $premium ) {
				return is_plugin_active( $plugin['slug'] . '-premium/' . $plugin['file'] );
			} else {
				return is_plugin_active( $plugin['slug'] . '/' . $plugin['file'] );
			}
		}

		public function install_plugin_link( $plugin ) {
			return wp_nonce_url( self_admin_url( 'update.php?action=install-plugin&plugin=' . $plugin['slug'] ), 'install-plugin_' . $plugin['slug'] );
		}

		public function activate_plugin_link( $plugin, $premium = false ) {
			if ( $premium ) {
				return wp_nonce_url( admin_url( 'admin.php?page=wpclever-kit&action=activate&plugin=' . $plugin['slug'] . '-premium/' . $plugin['file'] . '#' . $plugin['slug'] ), 'activate-plugin_' . $plugin['slug'] . '-premium/' . $plugin['file'] );
			} else {
				return wp_nonce_url( admin_url( 'admin.php?page=wpclever-kit&action=activate&plugin=' . $plugin['slug'] . '/' . $plugin['file'] . '#' . $plugin['slug'] ), 'activate-plugin_' . $plugin['slug'] . '/' . $plugin['file'] );
			}
		}

		public function deactivate_plugin_link( $plugin, $premium = false ) {
			if ( $premium ) {
				return wp_nonce_url( admin_url( 'admin.php?page=wpclever-kit&action=deactivate&plugin=' . $plugin['slug'] . '-premium/' . $plugin['file'] . '#' . $plugin['slug'] ), 'deactivate-plugin_' . $plugin['slug'] . '-premium/' . $plugin['file'] );
			} else {
				return wp_nonce_url( admin_url( 'admin.php?page=wpclever-kit&action=deactivate&plugin=' . $plugin['slug'] . '/' . $plugin['file'] . '#' . $plugin['slug'] ), 'deactivate-plugin_' . $plugin['slug'] . '/' . $plugin['file'] );
			}
		}
	}

	new WPCleverKit();
}
