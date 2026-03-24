<?php
$options   = [];
$options[] = array(
	'label'    => __( 'Display Footer Quick Contact?', 'soledad' ),
	'id'       => 'penci_footer_quickcontact_enable',
	'type'     => 'soledad-fw-toggle',
	'default'  => false,
	'sanitize' => 'penci_sanitize_checkbox_field'
);
$options[] = array(
	'label'    => __( 'Quick Contact Position', 'soledad' ),
	'id'       => 'penci_footer_quickcontact_position',
	'type'     => 'soledad-fw-select',
	'choices'  => [
		'left'  => esc_html__( 'Left', 'soledad' ),
		'right' => esc_html__( 'Right', 'soledad' ),
	],
	'default'  => 'right',
	'sanitize' => 'penci_sanitize_choices_field'
);
$options[] = array(
	'label'       => '',
	'description' => '',
	'id'          => 'penci_footer_quickcontact_size_mobile',
	'type'        => 'soledad-fw-hidden',
	'sanitize'    => 'absint',
);
$options[] = array(
	'default'     => '',
	'sanitize'    => 'absint',
	'label'       => __( 'Icon Size', 'soledad' ),
	'id'          => 'penci_footer_quickcontact_size',
	'type'        => 'soledad-fw-size',
	'ids'         => array(
		'desktop' => 'penci_footer_quickcontact_size',
		'mobile'  => 'penci_footer_quickcontact_size_mobile',
	),
	'choices'     => array(
		'desktop' => array(
			'min'  => 1,
			'max'  => 2000,
			'step' => 1,
			'edit' => true,
			'unit' => 'px',
		),
		'mobile'  => array(
			'min'  => 1,
			'max'  => 2000,
			'step' => 1,
			'edit' => true,
			'unit' => 'px',
		),
	),
);
$options[] = array(
	'label'        => __( 'Footer Quick Contact Items', 'soledad' ),
	'id'           => 'penci_footer_quickcontact_content',
	'type'         => 'soledad-fw-repeater',
	'description'  => __( 'You can add multiple contact items here.', 'soledad' ),
	'button_label' => esc_html__( '"Add new" button label (optional) ', 'soledad' ),
	'row_label'    => [
		'type'  => 'field',
		'value' => esc_html__( 'Name', 'soledad' ),
		'field' => 'contact_name',
	],
	'fields'       => [
		'contact_name' => [
			'type'        => 'text',
			'label'       => esc_html__( 'Contact Name', 'soledad' ),
			'default'     => '',
		],
		'contact_link' => [
			'type'        => 'url',
			'label'       => esc_html__( 'Contact Link', 'soledad' ),
			'default'     => '',
		],
		'contact_logo'  => [
			'type'        => 'image',
			'label'       => esc_html__( 'Contact Logo', 'soledad' ),
			'default'     => '',
		],
		'contact_eff'  	=> [
			'type'        => 'select',
			'label'       => esc_html__( 'Animation', 'soledad' ),
			'choices'     => [
				'pczoom'    => esc_html__( 'Zoom In', 'soledad' ),
				'pcswing'   => esc_html__( 'Swing', 'soledad' ),
				'pctada' 	=> esc_html__( 'Tada', 'soledad' ),
			],
		],
		'contact_color'  => [
			'type'        => 'color',
			'label'       => esc_html__( 'Text Color', 'soledad' ),
			'default'     => '',
		],
		'contact_bgcolor'  => [
			'type'        => 'color',
			'label'       => esc_html__( 'Background Color', 'soledad' ),
			'default'     => '',
		],
	],
);

return $options;