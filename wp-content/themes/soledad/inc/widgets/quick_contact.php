<?php
add_action( 'widgets_init', 'penci_quickcontact_widget' );

function penci_quickcontact_widget() {
	register_widget( 'Penci_Quick_Contact_Widget' );
}

if ( ! class_exists( 'Penci_Quick_Contact_Widget' ) ) {
	class Penci_Quick_Contact_Widget extends WP_Widget {

		/**
		 * Widget setup.
		 */
		function __construct() {
			$widget_ops  = array(
				'classname'   => 'penci_quickcontact_widget',
				'description' => esc_html__( 'Quick Contact Block', 'soledad' )
			);
			$control_ops = array( 'id_base' => 'penci_quickcontact_widget' );
			parent::__construct( 'penci_quickcontact_widget', penci_get_theme_name( '.Soledad', true ) . esc_html__( 'Penci Quick Contacts', 'soledad' ), $widget_ops, $control_ops );
			
		}

		public function widget( $args, $instance ) {
			if ( ! isset( $args['widget_id'] ) ) {
				$args['widget_id'] = $this->id;
			}

			$title = isset( $instance['title'] ) ? $instance['title'] : '';
			$title = apply_filters( 'widget_title', $title );
			$defaults = $this->soledad_widget_defaults();
			$settings = wp_parse_args( (array) $instance, $defaults );
			$contact_list = isset( $settings['contacts_list'] ) ? $settings['contacts_list'] : array();
			?>
			<?php echo $args['before_widget']; ?>
			<?php
			if ( $title ) {
				echo $args['before_title'] . $title . $args['after_title'];
			}
			$css_class = 'penci-block-vc penci-quick-contact-widget ';
			?>
            <div class="<?php echo esc_attr( $css_class ); ?>">
                <div class="penci-block_content">
					<?php if ( ! empty( $contact_list ) && is_array( $contact_list ) ) : ?>
						<div class="penci-quick-contact-items-wg">
							<?php foreach ( $contact_list as $contact ) :
								$icon = isset( $contact['icon'] ) ? $contact['icon'] : '';
								$line1 = isset( $contact['line1'] ) ? $contact['line1'] : '';
								$line2 = isset( $contact['line2'] ) ? $contact['line2'] : '';
								?>
								<div class="penci-quick-contact-item-wg">
									<?php if ( $icon ) : ?>
										<div class="penci-quick-contact-icon">
											<?php
											if ( filter_var( $icon, FILTER_VALIDATE_URL ) ) {
												echo '<img src="' . esc_url( $icon ) . '" alt="">';
											} else {
												echo '<i class="' . esc_attr( $icon ) . '"></i>';
											}
											?>
										</div>
									<?php endif; ?>
									<div class="penci-quick-contact-info">
										<?php if ( $line1 ) : ?>
											<div class="penci-quick-contact-line1"><?php echo $this->show_data( $line1 ); ?></div>
										<?php endif; ?>
										<?php if ( $line2 ) : ?>
											<div class="penci-quick-contact-line2"><?php echo $this->show_data( $line2 ); ?></div>
										<?php endif; ?>
									</div>
								</div>
							<?php endforeach; ?>
						</div>
					<?php else : ?>
						<p><?php esc_html_e( 'No contact information available.', 'soledad' ); ?></p>
					<?php endif; ?>
                </div>
            </div>
			<style>
				<?php
				$widget_id = esc_attr( $this->id );
				$border_width = esc_attr( $settings['border_width'] );
				$border_style = esc_attr( $settings['border_style'] );
				$border_color = esc_attr( $settings['border_color'] );
				$border_radius = esc_attr( $settings['border_radius'] );
				?>
				#<?php echo $widget_id; ?> .penci-quick-contact-widget {
					<?php if ( $border_width ) : ?>--pcquick-bdw: <?php echo $border_width; ?>px;
					<?php endif; ?>
					<?php if ( $border_style && $border_style !== 'none' ) : ?>--pcquick-bdstyle: <?php echo $border_style; ?>;
					<?php endif; ?>
					<?php if ( $border_color ) : ?>--pcquick-bdcolor: <?php echo $border_color; ?>;
					<?php endif; ?>
					<?php if ( $border_radius ) : ?>--pcquick-bdradius: <?php echo $border_radius; ?>px;
					<?php endif; ?>
				}
			</style>
			<?php
			echo $args['after_widget'];
		}

		/**
		 * Update the widget settings.
		 */
		function update( $new_instance, $old_instance ) {
			$instance = $old_instance;

			$data_instance = $this->soledad_widget_defaults();

			foreach ( $data_instance as $data => $value ) {
				$instance[ $data ] = ! empty( $new_instance[ $data ] ) ? $new_instance[ $data ] : '';
			}

			return $instance;
		}

		public function soledad_widget_defaults() {
			$defaults = array(
				'title'              => esc_html__( 'Quick Contact', 'soledad' ),
				'contacts_list'      => [],
				'border_style'       => 'none',
				'icon_color'         => '',
				'text_color'         => '',
				'block_bg_color'     => '',
				'border_color'       => '',
				'border_width'       => 2,
				'border_radius'      => 5,
				'block_id'           => rand( 1000, 100000 )
			);

			return $defaults;
		}

		function form( $instance ) {
			$defaults = $this->soledad_widget_defaults();
			$instance = wp_parse_args( (array) $instance, $defaults );
			$instance_title     = $instance['title'] ? str_replace( '"', '&quot;', $instance['title'] ) : '';
			?>

            <p class="penci-field-item" style="margin-top: 10px">
                <label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>">Block title:</label>
                <input id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" class="widefat" type="text"
                       name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>"
                       value="<?php echo $instance_title; ?>">
                <span class="penci-widget-desc">A title for this block, if you leave it blank the block will not have a title</span>
            </p>

			<style>
				.penci-contacts-list-container {
					background: #f9f9f9;
					padding: 15px;
					border: 1px solid #ddd;
					border-radius: 4px;
					margin-top: 10px;
				}
				.penci-contacts-list-container h4 {
					margin: 0 0 15px 0;
					font-size: 14px;
					font-weight: 600;
					text-transform: uppercase;
					color: #23282d;
					border-bottom: 2px solid #0073aa;
					padding-bottom: 10px;
				}
				.penci-contact-item {
					background: linear-gradient(135deg, #fff 0%, #f5f5f5 100%);
					padding: 16px;
					border: 1px solid #e8e8e8;
					border-radius: 5px;
					margin-bottom: 16px;
					position: relative;
					box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
					transition: all 0.2s ease;
				}
				.penci-contact-item:hover {
					border-color: #0073aa;
					box-shadow: 0 2px 6px rgba(0, 115, 170, 0.15);
				}
				.penci-contact-item-header {
					display: flex;
					justify-content: space-between;
					align-items: center;
					margin-bottom: 15px;
					padding-bottom: 12px;
					border-bottom: 2px solid #e0e0e0;
				}
				.penci-contact-item-header span {
					font-size: 12px;
					font-weight: 700;
					color: #fff;
					text-transform: uppercase;
					background: linear-gradient(135deg, #0073aa 0%, #005a87 100%);
					padding: 6px 12px;
					border-radius: 4px;
					letter-spacing: 0.5px;
				}
				.penci-contact-item-rows {
					display: grid;
					grid-template-columns: 1fr;
					gap: 12px;
				}
				.penci-contact-item-rows .penci-field-item {
					margin: 0;
				}
				.penci-field-item {
					margin: 0 0 12px 0;
					display: flex;
					flex-direction: column;
				}
				.penci-field-item label {
					display: block;
					font-weight: 700;
					margin-bottom: 7px;
					font-size: 12px;
					color: #0073aa;
					text-transform: uppercase;
					letter-spacing: 0.3px;
				}
				.penci-field-item input.widefat {
					width: 100%;
					padding: 10px 12px;
					border: 1px solid #ddd;
					border-radius: 4px;
					font-size: 13px;
					background: #fff;
					transition: all 0.2s ease;
					font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
				}
				.penci-field-item input.widefat:focus {
					outline: none;
					border-color: #0073aa;
					box-shadow: 0 0 0 3px rgba(0, 115, 170, 0.1);
					background: #fafbfc;
				}
				.penci-field-item input.widefat::placeholder {
					color: #bbb;
				}
				.penci-color-picker {
					padding: 10px 12px !important;
					height: 42px !important;
				}
				.penci-widget-desc {
					display: block;
					font-size: 11px;
					color: #999;
					margin-top: 5px;
					font-style: italic;
				}
				.penci-remove-contact {
					background: linear-gradient(135deg, #dc3545 0%, #c82333 100%) !important;
					color: #fff !important;
					border: none !important;
					padding: 5px 15px !important;
					font-size: 12px !important;
					font-weight: 600 !important;
					cursor: pointer;
					width: 100%;
					border-radius: 4px;
					margin-top: 12px !important;
					transition: all 0.2s;
					box-shadow: 0 2px 4px rgba(220, 53, 69, 0.2);
				}
				.penci-remove-contact:hover {
					background: linear-gradient(135deg, #c82333 0%, #bd2130 100%) !important;
					box-shadow: 0 3px 8px rgba(220, 53, 69, 0.3);
					transform: translateY(-1px);
				}
				.penci-add-contact {
					width: 100%;
					padding: 5px 15px !important;
					font-size: 13px !important;
					font-weight: 700 !important;
					background: linear-gradient(135deg, #0073aa 0%, #005a87 100%) !important;
					border: none !important;
					color: #fff !important;
					border-radius: 4px;
					cursor: pointer;
					transition: all 0.2s;
					box-shadow: 0 2px 4px rgba(0, 115, 170, 0.2);
					text-transform: uppercase;
					letter-spacing: 0.5px;
				}
				.penci-add-contact:hover {
					background: linear-gradient(135deg, #005a87 0%, #004167 100%) !important;
					box-shadow: 0 3px 8px rgba(0, 115, 170, 0.3);
					transform: translateY(-1px);
				}
			</style>

			<div class="penci-contacts-list-container">
				<h4><?php esc_html_e( '📞 Contacts List', 'soledad' ); ?></h4>
				<?php
				$contacts_list = isset( $instance['contacts_list'] ) && is_array( $instance['contacts_list'] ) ? $instance['contacts_list'] : array();
				
				if ( ! empty( $contacts_list ) ) {
					foreach ( $contacts_list as $index => $contact ) {
						$contact_icon = isset( $contact['icon'] ) ? $contact['icon'] : '';
						$contact_line1 = isset( $contact['line1'] ) ? $contact['line1'] : '';
						$contact_line2 = isset( $contact['line2'] ) ? $contact['line2'] : '';
						?>
						<div class="penci-contact-item" data-index="<?php echo esc_attr( $index ); ?>">
							<div class="penci-contact-item-header">
								<span><?php printf( esc_html__( 'Contact #%d', 'soledad' ), intval( $index ) + 1 ); ?></span>
							</div>

							<div class="penci-contact-item-rows">
								<p class="penci-field-item">
									<label for="<?php echo esc_attr( $this->get_field_id( 'contacts_list' ) . '_icon_' . $index ); ?>">
										<?php esc_html_e( 'Icon URL/Name', 'soledad' ); ?>
									</label>
									<input id="<?php echo esc_attr( $this->get_field_id( 'contacts_list' ) . '_icon_' . $index ); ?>" 
										   class="widefat" 
										   type="text"
										   name="<?php echo esc_attr( $this->get_field_name( 'contacts_list' ) . '[' . $index . '][icon]' ); ?>"
										   value="<?php echo esc_attr( $contact_icon ); ?>"
										   placeholder="e.g., fas fa-phone or https://...">
									<span class="penci-widget-desc">Enter image URL or Font Awesome class. You can get the icon class name <a target="_blank" href="https://soledad.pencidesign.net/soledad-document/#add-icons">here</a>.</span>
								</p>

								<p class="penci-field-item">
									<label for="<?php echo esc_attr( $this->get_field_id( 'contacts_list' ) . '_line1_' . $index ); ?>">
										<?php esc_html_e( 'Contact Line 1', 'soledad' ); ?>
									</label>
									<input id="<?php echo esc_attr( $this->get_field_id( 'contacts_list' ) . '_line1_' . $index ); ?>" 
										   class="widefat" 
										   type="text"
										   name="<?php echo esc_attr( $this->get_field_name( 'contacts_list' ) . '[' . $index . '][line1]' ); ?>"
										   value="<?php echo esc_attr( $contact_line1 ); ?>"
										   placeholder="e.g., Phone">
									<span class="penci-widget-desc">Primary contact information</span>
								</p>

								<p class="penci-field-item" style="grid-column: 1 / -1;">
									<label for="<?php echo esc_attr( $this->get_field_id( 'contacts_list' ) . '_line2_' . $index ); ?>">
										<?php esc_html_e( 'Contact Line 2', 'soledad' ); ?>
									</label>
									<input id="<?php echo esc_attr( $this->get_field_id( 'contacts_list' ) . '_line2_' . $index ); ?>" 
										   class="widefat" 
										   type="text"
										   name="<?php echo esc_attr( $this->get_field_name( 'contacts_list' ) . '[' . $index . '][line2]' ); ?>"
										   value="<?php echo esc_attr( $contact_line2 ); ?>"
										   placeholder="e.g., +1 (555) 123-4567">
									<span class="penci-widget-desc">Secondary contact information</span>
								</p>
							</div>

							<button type="button" class="penci-remove-contact button">
								<?php esc_html_e( '🗑 Remove Contact', 'soledad' ); ?>
							</button>
						</div>
						<?php
					}
				}
				?>

			<button type="button" class="penci-add-contact button">
				<?php esc_html_e( '+ Add Contact', 'soledad' ); ?>
			</button>
			</div>

			<hr style="margin: 20px 0; border: none; border-top: 1px solid #ddd;">

			<p class="penci-field-item">
				<label for="<?php echo esc_attr( $this->get_field_id( 'border_width' ) ); ?>">
					<?php esc_html_e( 'Border Width (px)', 'soledad' ); ?>
				</label>
				<input id="<?php echo esc_attr( $this->get_field_id( 'border_width' ) ); ?>" 
					   class="widefat" 
					   type="number"
					   name="<?php echo esc_attr( $this->get_field_name( 'border_width' ) ); ?>"
					   value="<?php echo esc_attr( isset( $instance['border_width'] ) ? $instance['border_width'] : 2 ); ?>"
					   min="0"
					   max="10"
					   step="1">
				<span class="penci-widget-desc">Set the border thickness for contact items</span>
			</p>

			<p class="penci-field-item">
				<label for="<?php echo esc_attr( $this->get_field_id( 'border_radius' ) ); ?>">
					<?php esc_html_e( 'Border Radius (px)', 'soledad' ); ?>
				</label>
				<input id="<?php echo esc_attr( $this->get_field_id( 'border_radius' ) ); ?>" 
					   class="widefat" 
					   type="number"
					   name="<?php echo esc_attr( $this->get_field_name( 'border_radius' ) ); ?>"
					   value="<?php echo esc_attr( isset( $instance['border_radius'] ) ? $instance['border_radius'] : 5 ); ?>"
					   min="0"
					   max="50"
					   step="1">
				<span class="penci-widget-desc">Set the corner roundness for contact items</span>
			</p>

			<p class="penci-field-item">
				<label for="<?php echo esc_attr( $this->get_field_id( 'border_color' ) ); ?>">
					<?php esc_html_e( 'Border Color', 'soledad' ); ?>
				</label>
				<input id="<?php echo esc_attr( $this->get_field_id( 'border_color' ) ); ?>" 
					   type="text"
					   name="<?php echo esc_attr( $this->get_field_name( 'border_color' ) ); ?>"
					   value="<?php echo esc_attr( isset( $instance['border_color'] ) ? $instance['border_color'] : '' ); ?>"
					   class="widefat pcwoo-color-picker color-picker"
					   data-default-color="">
				<span class="penci-widget-desc">Choose the border color for contact items</span>
			</p>

			<p class="penci-field-item">
				<label for="<?php echo esc_attr( $this->get_field_id( 'border_style' ) ); ?>">
					<?php esc_html_e( 'Border Style', 'soledad' ); ?>
				</label>
				<select id="<?php echo esc_attr( $this->get_field_id( 'border_style' ) ); ?>" 
						class="widefat"
						name="<?php echo esc_attr( $this->get_field_name( 'border_style' ) ); ?>">
					<option value="none" <?php selected( isset( $instance['border_style'] ) ? $instance['border_style'] : 'none', 'none' ); ?>>
						<?php esc_html_e( 'None', 'soledad' ); ?>
					</option>
					<option value="solid" <?php selected( isset( $instance['border_style'] ) ? $instance['border_style'] : 'none', 'solid' ); ?>>
						<?php esc_html_e( 'Solid', 'soledad' ); ?>
					</option>
					<option value="dashed" <?php selected( isset( $instance['border_style'] ) ? $instance['border_style'] : 'none', 'dashed' ); ?>>
						<?php esc_html_e( 'Dashed', 'soledad' ); ?>
					</option>
					<option value="dotted" <?php selected( isset( $instance['border_style'] ) ? $instance['border_style'] : 'none', 'dotted' ); ?>>
						<?php esc_html_e( 'Dotted', 'soledad' ); ?>
					</option>
					<option value="double" <?php selected( isset( $instance['border_style'] ) ? $instance['border_style'] : 'none', 'double' ); ?>>
						<?php esc_html_e( 'Double', 'soledad' ); ?>
					</option>
				</select>
				<span class="penci-widget-desc">Choose the border style for contact items</span>
			</p>

			<script>
				(function($) {
					function createContactItem(baseIndex) {
						return '<div class="penci-contact-item" data-index="' + baseIndex + '">' +
							'<div class="penci-contact-item-header">' +
								'<span>Contact #' + (baseIndex + 1) + '</span>' +
							'</div>' +
							'<div class="penci-contact-item-rows">' +
								'<p class="penci-field-item">' +
									'<label>Icon URL/Name</label>' +
									'<input class="widefat" type="text" name="widget-penci_quickcontact_widget[<?php echo esc_js( $this->number ); ?>][contacts_list][' + baseIndex + '][icon]" value="" placeholder="e.g., fas fa-phone or https://...">' +
									'<span class="penci-widget-desc">Enter image URL or Font Awesome class. You can get the icon class name <a target="_blank" href="https://soledad.pencidesign.net/soledad-document/#add-icons">here</a>.</span>' +
								'</p>' +
								'<p class="penci-field-item">' +
									'<label>Contact Line 1</label>' +
									'<input class="widefat" type="text" name="widget-penci_quickcontact_widget[<?php echo esc_js( $this->number ); ?>][contacts_list][' + baseIndex + '][line1]" value="" placeholder="e.g., Phone">' +
									'<span class="penci-widget-desc">Primary contact information</span>' +
								'</p>' +
								'<p class="penci-field-item" style="grid-column: 1 / -1;">' +
									'<label>Contact Line 2</label>' +
									'<input class="widefat" type="text" name="widget-penci_quickcontact_widget[<?php echo esc_js( $this->number ); ?>][contacts_list][' + baseIndex + '][line2]" value="" placeholder="e.g., +1 (555) 123-4567">' +
									'<span class="penci-widget-desc">Secondary contact information</span>' +
								'</p>' +
							'</div>' +
							'<button type="button" class="penci-remove-contact button">🗑 Remove Contact</button>' +
							'</div>';
					}
					
					$('.penci-contacts-list-container').each(function() {
						var container = $(this);
						var addBtn = container.find('.penci-add-contact');
						
						addBtn.off('click').on('click', function(e) {
							e.preventDefault();
							e.stopPropagation();
							var items = container.find('.penci-contact-item');
							var newIndex = items.length;
							var newItem = $(createContactItem(newIndex));
							addBtn.before(newItem);
							return false;
						});
					});
					
					$(document).off('click', '.penci-remove-contact').on('click', '.penci-remove-contact', function(e) {
						e.preventDefault();
						e.stopPropagation();
						$(this).closest('.penci-contact-item').remove();
						return false;
					});
				})(jQuery);
			</script>
			
			<?php
		}

		public function check_data( $data ) {
			if ( empty( $data ) ) {
				return 'unknown';
			}

			$data = strtolower( trim( $data ) );

			// Check for email
			if ( filter_var( $data, FILTER_VALIDATE_EMAIL ) ) {
				return 'email';
			}

			// Check for URL/website
			if ( filter_var( $data, FILTER_VALIDATE_URL ) ) {
				return 'website';
			}

			// Check for phone number (basic pattern)
			if ( preg_match( '/^[\d\s\-\+\(\)\.]+$/', $data ) && strlen( preg_replace( '/\D/', '', $data ) ) >= 7 ) {
				return 'phone';
			}

			// Check for social media handles
			if ( preg_match( '/^@[\w\.]+$/', $data ) ) {
				return 'social';
			}

			return 'unknown';
		}

		public function show_data( $data ) {
			$data_type = $this->check_data( $data );
			switch ( $data_type ) {
				case 'email':
					return '<a href="mailto:' . esc_attr( $data ) . '">' . esc_html( $data ) . '</a>';
				case 'website':
					return '<a href="' . esc_url( $data ) . '" target="_blank" rel="noopener noreferrer">' . esc_html( $data ) . '</a>';
				case 'phone':
					$tel_link = preg_replace( '/\D+/', '', $data );
					return '<a href="tel:' . esc_attr( $tel_link ) . '">' . esc_html( $data ) . '</a>';
				case 'social':
					return esc_html( $data );
				default:
					return esc_html( $data );
			}
		}
	}
}
?>