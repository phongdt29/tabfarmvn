<?php
/**
 * Customizer Control: repeater.
 *
 * @author PenciDesign
 * @since 1.0.0
 * @package soledad
 */

namespace SoledadFW\Customizer\Control;

/**
 * Repeater control
 */
class Repeater extends Control_Abstract {

	/**
	 * The control type.
	 *
	 * @access public
	 * @var string
	 */
	public $type = 'soledad-fw-repeater';

	/**
	 * The fields that each container row will contain.
	 *
	 * @access public
	 * @var array
	 */
	public $fields = array();

	/**
	 * Will store a filtered version of value for advenced fields (like images).
	 *
	 * @access protected
	 * @var array
	 */
	protected $filtered_value = array();

	/**
	 * The row label
	 *
	 * @access public
	 * @var array
	 */
	public $row_label = array();

	/**
	 * Constructor.
	 * Supplied `$args` override class property defaults.
	 * If `$args['settings']` is not defined, use the $id as the setting ID.
	 *
	 * @param WP_Customize_Manager $manager Customizer bootstrap instance.
	 * @param string               $id      Control ID.
	 * @param array                $args    {@see WP_Customize_Control::__construct}.
	 */
	public function __construct( $manager, $id, $args = array() ) {

		parent::__construct( $manager, $id, $args );

		// Set up defaults for row labels.
		$this->row_label = array(
			'type' => 'text',
			'value' => esc_attr__( 'row', 'soledad' ),
			'field' => false,
		);

		// Validating args for row labels.
		if ( isset( $args['row_label'] ) && is_array( $args['row_label'] ) && ! empty( $args['row_label'] ) ) {

			// Validating row label type.
			if ( isset( $args['row_label']['type'] ) && ( 'text' === $args['row_label']['type'] || 'field' === $args['row_label']['type'] ) ) {
				$this->row_label['type'] = $args['row_label']['type'];
			}

			// Validating row label type.
			if ( isset( $args['row_label']['value'] ) && ! empty( $args['row_label']['value'] ) ) {
				$this->row_label['value'] = esc_attr( $args['row_label']['value'] );
			}

			// Validating row label field.
			if ( isset( $args['row_label']['field'] ) && ! empty( $args['row_label']['field'] ) && isset( $args['fields'][ esc_attr( $args['row_label']['field'] ) ] ) ) {
				$this->row_label['field'] = esc_attr( $args['row_label']['field'] );
			} else {

				// If from field is not set correctly, making sure standard is set as the type.
				$this->row_label['type'] = 'text';
			}
		}

		if ( empty( $this->button_label ) ) {
			$this->button_label = esc_attr__( 'Add new', 'soledad' ) . ' ' . $this->row_label['value'];
		}

		if ( empty( $args['fields'] ) || ! is_array( $args['fields'] ) ) {
			$args['fields'] = array();
		}

		// An array to store keys of fields that need to be filtered.
		$media_fields_to_filter = array();

		foreach ( $args['fields'] as $key => $value ) {
			if ( ! isset( $value['default'] ) ) {
				$args['fields'][ $key ]['default'] = '';
			}

			if ( ! isset( $value['label'] ) ) {
				$args['fields'][ $key ]['label'] = '';
			}
			$args['fields'][ $key ]['id'] = $key;

			// We check if the filed is an uploaded media ( image , file, video, etc.. ).
			if ( isset( $value['type'] ) && ( 'image' === $value['type'] || 'cropped_image' === $value['type'] || 'upload' === $value['type'] ) ) {

				// We add it to the list of fields that need some extra filtering/processing.
				$media_fields_to_filter[ $key ] = true;
			}

			// If the field is a dropdown-pages field then add it to args.
			if ( isset( $value['type'] ) && ( 'dropdown-pages' === $value['type'] ) ) {

				$dropdown = wp_dropdown_pages(
					array(
						'name'              => '',
						'echo'              => 0,
						'show_option_none'  => esc_attr__( 'Select a Page', 'soledad' ),
						'option_none_value' => '0',
						'selected'          => '',
					)
				);

				// Hackily add in the data link parameter.
				$dropdown = str_replace( '<select', '<select data-field="'.esc_attr( $args['fields'][ $key ]['id'] ).'"' . $this->get_link(), $dropdown );

				$args['fields'][ $key ]['dropdown'] = $dropdown;
			}
		}

		$this->fields = $args['fields'];

		// Now we are going to filter the fields.
		// First we create a copy of the value that would be used otherwise.
		$this->filtered_value = $this->value();

		if ( is_array( $this->filtered_value ) && ! empty( $this->filtered_value ) ) {

			// We iterate over the list of fields.
			foreach ( $this->filtered_value as &$filtered_value_field ) {

				if ( is_array( $filtered_value_field ) && ! empty( $filtered_value_field ) ) {

					// We iterate over the list of properties for this field.
					foreach ( $filtered_value_field as $key => &$value ) {

						// We check if this field was marked as requiring extra filtering (in this case image, cropped_images, upload).
						if ( array_key_exists( $key, $media_fields_to_filter ) ) {

							// What follows was made this way to preserve backward compatibility.
							// The repeater control use to store the URL for images instead of the attachment ID.
							// We check if the value look like an ID (otherwise it's probably a URL so don't filter it).
							if ( is_numeric( $value ) ) {

								// "sanitize" the value.
								$attachment_id = (int) $value;

								// Try to get the attachment_url.
								$url = wp_get_attachment_url( $attachment_id );

								$filename = basename( get_attached_file( $attachment_id ) );

								// If we got a URL.
								if ( $url ) {

									// 'id' is needed for form hidden value, URL is needed to display the image.
									$value = array(
										'id'  => $attachment_id,
										'url' => $url,
										'filename' => $filename,
									);
								}
							}
						}
					}
				}
			}
		}
	}

	/**
	 * Refresh the parameters passed to the JavaScript via JSON.
	 *
	 * @access public
	 */
	public function to_json() {
		parent::to_json();

		$fields = $this->fields;

		$this->json['fields'] = $fields;
		$this->json['row_label'] = $this->row_label;

		// If filtered_value has been set and is not empty we use it instead of the actual value.
		if ( is_array( $this->filtered_value ) && ! empty( $this->filtered_value ) ) {
			$this->json['value'] = $this->filtered_value;
		}
	}

	protected function content_template() {
		?>
		<label>
			<# if ( data.label ) { #>
				<span class="customize-control-title">{{{ data.label }}}</span>
			<# } #>
			<# if ( data.description ) { #>
				<span class="description customize-control-description">{{{ data.description }}}</span>
			<# } #>
			<input type="hidden" <# _.each( data.inputAttrs, function( value, key ) { #> {{ key }}="{{ value }}" <# }); #> value="{{ data.value }}" {{{ data.link }}} />
		</label>

		<ul class="repeater-fields"></ul>

		<# if ( data.choices && data.choices.limit ) { #>
			<p class="limit"><?php printf( esc_attr__( 'Limit: %s rows', 'soledad' ), '{{ data.choices.limit }}' ); ?></p>
		<# } #>
		<div class="repeater-add-wrapper">
			<button class="button button-large button-primary repeater-add"><i class="fa fa-plus"></i></button>	
		</div>
		<?php
	}
}
