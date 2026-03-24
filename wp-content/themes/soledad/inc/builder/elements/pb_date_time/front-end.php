<?php 
$header_data       = $args['header_data'];
$date_time_format = penci_builder_validate_mod( $header_data, 'penci_header_pb_data_time_format' );
$class            = penci_builder_validate_mod( $header_data, 'penci_header_pb_data_time_css_class' );

if ( empty( $date_time_format ) ) {
	return false;
}

$local_time = penci_builder_validate_mod( $header_data, 'penci_header_pb_data_time_local' );

// Fallback: site time (server-rendered)
if ( function_exists( 'wp_date' ) ) {
	$return = wp_date( $date_time_format );
} else {
	$return = current_time( $date_time_format );
}

$time_class = get_theme_mod( 'penci_time_sync' ) ? 'penci-dtf-real' : 'penci-dtf-normal';
?>

<div class="penci-builder-element penci-data-time-format <?php echo esc_attr( $class ); ?>">
	<span
		class="<?php echo esc_attr( $time_class ); ?>"
		data-format="<?php echo esc_attr( $date_time_format ); ?>"
		data-local="<?php echo $local_time ? '1' : '0'; ?>"
	>
		<?php echo esc_html( $return ); ?>
	</span>
</div>

<?php if ( $local_time ) : ?>
<script>
(function () {

	const el = document.querySelector('.penci-data-time-format span[data-local="1"]');
	if (!el) return;

	const format = el.dataset.format;

	const pad = (n) => n.toString().padStart(2, '0');

	const wpDate = (date, format) => {
		const map = {
			// Day
			d: () => pad(date.getDate()),
			j: () => date.getDate(),
			l: () => date.toLocaleDateString(undefined, { weekday: 'long' }),
			D: () => date.toLocaleDateString(undefined, { weekday: 'short' }),

			// Month
			m: () => pad(date.getMonth() + 1),
			n: () => date.getMonth() + 1,
			F: () => date.toLocaleDateString(undefined, { month: 'long' }),
			M: () => date.toLocaleDateString(undefined, { month: 'short' }),

			// Year
			Y: () => date.getFullYear(),
			y: () => date.getFullYear().toString().slice(-2),

			// Time
			H: () => pad(date.getHours()),
			G: () => date.getHours(),
			h: () => pad((date.getHours() % 12) || 12),
			g: () => (date.getHours() % 12) || 12,
			i: () => pad(date.getMinutes()),
			s: () => pad(date.getSeconds()),
			a: () => date.getHours() < 12 ? 'am' : 'pm',
			A: () => date.getHours() < 12 ? 'AM' : 'PM'
		};

		return format.replace(/\\?([a-zA-Z])/g, function (match, token) {
			if (match.startsWith('\\')) {
				return token;
			}
			return map[token] ? map[token]() : token;
		});
	};

	const updateTime = () => {
		el.textContent = wpDate(new Date(), format);
	};

	updateTime();

	if (el.classList.contains('penci-dtf-real')) {
		setInterval(updateTime, 1000);
	}

})();
</script>
<?php endif; ?>