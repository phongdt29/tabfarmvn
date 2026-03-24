<?php

namespace Soledad\PageSpeed;

use Soledad\PageSpeed\Admin\Cache;

/**
 * Admin initialization.
 *
 * @author  asadkn
 * @modified pencidesign
 * @since   1.0.0
 */
class Admin {
	/**
	 * @var Soledad\PageSpeed\Admin\Cache
	 */
	protected $cache;

	/**
	 * Setup hooks
	 */
	public function init() {
		$this->cache = new Cache;
		$this->cache->init();
		add_action( 'wp_ajax_penci_speed_delete_cache', [ $this, 'delete_cache' ] );
		add_action( 'wp_ajax_penci_generate_critical_css', [ $this, 'penci_generate_critical_css' ] );
		add_action( 'init', [ $this, 'delete_page_cache' ] );
		add_action( 'add_meta_boxes', function () {
			add_meta_box(
				'penci_critical_css_cache',        // ID
				__( 'Critical CSS', 'penci-shortcodes' ), // Title
				[ $this, 'render_home_button_metabox' ], // Callback
				'page',                       // Post type
				'side',                       // Context (sidebar)
				'high'                        // Priority
			);
		} );
		add_action( 'admin_enqueue_scripts', [ $this, 'critical_files' ] );
		add_action( 'save_post', function ( $post_id ) {
			if ( array_key_exists( 'penci_critical_disable', $_POST ) ) {
				update_post_meta(
					$post_id,
					'penci_critical_disable',
					$_POST['penci_critical_disable']
				);
			} else {
				delete_post_meta( $post_id, 'penci_critical_disable' );
			}
		} );
		//add_action( 'admin_menu', array( $this, 'admin_menu' ), 90 );
	}

	function critical_files() {
		wp_enqueue_script( 'penci-critical-js', PENCI_SOLEDAD_SHORTCODE_URL . 'pagespeed/assets/critical.js', [ 'jquery' ], PENCI_SOLEDAD_SHORTCODE_PERFORMANCE, true );
		wp_enqueue_style( 'penci-critical-css', PENCI_SOLEDAD_SHORTCODE_URL . 'pagespeed/assets/critical.css', [], PENCI_SOLEDAD_SHORTCODE_PERFORMANCE );
	}

	public static function admin_menu() {

		add_submenu_page(
			'soledad_dashboard_welcome',
			'Critical CSS Tools',
			'Critical CSS Tools',
			'manage_options',
			'critical-css-tools',
			array( __CLASS__, 'create_admin_page' ),
			3
		);
	}

	public static function penci_convert_to_bytes( $val ) {
		$val  = trim( $val );
		$last = strtolower( $val[ strlen( $val ) - 1 ] );
		$val  = (int) $val;
		switch ( $last ) {
			case 'g':
				$val *= 1024;
			case 'm':
				$val *= 1024;
			case 'k':
				$val *= 1024;
		}

		return $val;
	}

	public static function create_admin_page() {
		$mode = isset( $_GET['mode'] ) ? sanitize_text_field( wp_unslash( $_GET['mode'] ) ) : 'auto';
		?>
        <div class="wrap">
            <h1>Critical CSS Tools</h1>

			<div class="penci-critical-tools-container">

				<div class="penci-critical-tools-wrap">

					<div class="penci-critical-tools-tabs">
						<ul>
							<li<?php if ( $mode == 'auto' ): ?> class="active"<?php endif; ?>><a
										href="<?php echo esc_url( admin_url( 'admin.php?page=critical-css-tools&mode=auto' ) ); ?>">Auto
									Mode</a></li>
							<li<?php if ( $mode == 'manual' ): ?> class="active"<?php endif; ?>><a
										href="<?php echo esc_url( admin_url( 'admin.php?page=critical-css-tools&mode=manual' ) ); ?>">Manual
									Mode</a></li>
						</ul>
					</div>

					<?php
					$memory_limit = ini_get( 'memory_limit' );
					$timeout      = ini_get( 'max_execution_time' );

					$memory_limit_bytes = self::penci_convert_to_bytes( $memory_limit );
					$min_memory_bytes   = 512 * 1024 * 1024; // 512M in bytes
					$timeout_int        = (int) $timeout;

					if ( $memory_limit_bytes < $min_memory_bytes || $timeout_int < 300 ) : ?>
						<div class="penci-critical-notice">

							<strong><?php _e( 'Warning:', 'penci-shortcodes' ); ?></strong>
							<?php _e( '<p>Your server settings may not be optimal for generating Critical CSS.</p><p>For best results, set <code>memory_limit</code> to at least <strong>512M</strong> and <code>max_execution_time</code> to at least <strong>300</strong> seconds.</p><p>Current values:</p>', 'penci-shortcodes' ); ?>
							<div>
								<code>memory_limit = <?php echo esc_html( $memory_limit ); ?></code>,
								<code>max_execution_time = <?php echo esc_html( $timeout ); ?></code>
							</div>

						</div>
					<?php endif; ?>

					<?php if ( $mode == 'auto' ): ?>
						<div id="penci-critical-tools-auto-mode" class="pcct-tcontent">
							<h2>Auto Mode</h2>
							<p>Instead of generating Critical CSS when a user first visits your site, this tool allows you
								to create a Critical CSS cache directly here. This saves time and improves loading speed for
								all users.</p>
							<p>This mode will generate basic Critical CSS files for the homepage, single posts/pages,
								categories, and other taxonomy pages. To generate Critical CSS for a specific page, please
								adjust the settings in that page’s editor screen. It may take some time to process all
								pages, depending on the number of pages on your site. Please be patient.</p>
							<p><strong>Note:</strong> Ensure that your server can handle multiple requests and has
								sufficient resources to avoid timeouts during the generation process.</p>
							<a data-type="auto" data-id="" href="#" class="penci-regenerate-css button button-primary">Create
								Critical CSS Files</a>
						</div>
					<?php endif; ?>

					<?php if ( $mode == 'manual' ): ?>
						<div id="penci-critical-tools-manual-mode" class="pcct-tcontent">
							<h2>Manual Mode</h2>
							<p>This mode helps you generate Critical CSS based on the URLs entered in the form below, with
								one URL per line.</p>

							<textarea name="penci-critical-urls" id="penci-critical-urls"
									placeholder="<?php echo esc_url( home_url( '' ) ) . '/example'; ?>"></textarea>

							<p><strong>Note:</strong> This method is useful for generating critical CSS for specific pages
								or terms that may require special attention.</p>
							<a data-type="manual" data-id="" href="#" class="penci-regenerate-css button button-primary">Create
								Critical CSS Files</a>
						</div>
					<?php endif; ?>

				</div>
				<div id="penci-critical-tools-response">
					<h4 class="pcc-title">System Log</h4>
					<div class="penci-critical-tools-content"></div>
				</div>
			</div>
        </div>
		<?php
	}

	function render_home_button_metabox( $post ) {
		$critical_css = get_post_meta( $post->ID, 'penci_critical_disable', true );
		if ( ! $critical_css ) :
			?>
            <div style="text-align:center; padding:10px;">
                <a href="#" data-type="page" data-id="<?php echo get_the_ID(); ?>"
                   class="button button-primary penci-regenerate-css" target="_blank">
					<?php _e( 'Generate Critical CSS Cache', 'penci-shortcodes' ); ?>
                </a>
            </div>
		<?php endif; ?>
        <div class="post-field">
            <input type="checkbox" name="penci_critical_disable" id="penci_critical_disable"
                   value="1" <?php checked( $critical_css, 1 ); ?> />
            <label for="penci_critical_disable"><?php _e( 'Disable Critical CSS', 'penci-shortcodes' ); ?></label>
        </div>
		<?php
	}

	/**
	 * Delete Cache short page.
	 */
	public function delete_page_cache() {
		if ( current_user_can( 'manage_options' ) && isset( $_GET['clear_pencilazy_css'] ) ) {
			$this->_delete_cache();
			$this->increase_cache_version();
		}

		if ( current_user_can( 'manage_options' ) && isset( $_GET['clear_pencilazy_css_single'] ) ) {
			$this->_delete_single_cache();
			$this->increase_cache_version();
		}

		return false;
	}

	public function increase_cache_version() {
		$current_version = get_option( 'penci_speed_file_version' );
		$current_version = $current_version ? $current_version : 1.0;

		$file_version = $current_version + 0.1;

		update_option( 'penci_speed_file_version', $file_version );
	}

	public function _delete_single_cache() {
		$this->cache->empty_single_site();

		/**
		 * Hook after deleting cache.
		 */
		do_action( 'soledad_pagespeed/after_delete_cache' );
	}

	/**
	 * Callback: Delete the cache.
	 *
	 * @access private
	 */
	public function _delete_cache() {
		$this->cache->empty();

		/**
		 * Hook after deleting cache.
		 */
		do_action( 'soledad_pagespeed/after_delete_cache' );
	}

	/**
	 * Delete Cache page.
	 */
	public function delete_cache() {
		check_ajax_referer( 'penci_speed_delete_cache', '_nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( __( 'User Permission Error', 'soledad' ) );
		}

		$this->_delete_cache();

		wp_send_json_success();

		die();
	}

	public function penci_get_site_urls( $type='' ) {
		$urls = [];
	
		// Home
		$urls['home'] = home_url('/');
	
		// Front page (if set)
		if ( get_option('page_on_front') ) {
			$urls['front'] = get_permalink( get_option('page_on_front') );
		}
	
		// One Page
		$page = get_pages([ 'number' => 1, 'post_status' => 'publish' ]);
		if ( ! empty( $page ) ) {
			$urls['page'] = get_permalink( $page[0]->ID );
		}
	
		// One Singular Post
		$post = get_posts([
			'post_type'      => 'post',
			'posts_per_page' => 1,
			'post_status'    => 'publish',
		]);
		if ( ! empty( $post ) ) {
			$urls['singular'] = get_permalink( $post[0]->ID );
		}
	
		// One Category
		$cat = get_categories([ 'number' => 1, 'hide_empty' => false ]);
		if ( ! empty( $cat ) ) {
			$urls['category'] = get_category_link( $cat[0]->term_id );
		}
	
		// One Tag
		$tag = get_tags([ 'number' => 1, 'hide_empty' => false ]);
		if ( ! empty( $tag ) ) {
			$urls['tag'] = get_tag_link( $tag[0]->term_id );
		}
	
		// One Custom Taxonomy Term
		$taxonomies = get_taxonomies([ 'public' => true, '_builtin' => false ], 'objects' );
		if ( ! empty( $taxonomies ) ) {
			$taxonomy = reset( $taxonomies ); // first taxonomy object
			if ( is_object( $taxonomy ) && isset( $taxonomy->name ) ) {
				$terms = get_terms([ 'taxonomy' => $taxonomy->name, 'number' => 1, 'hide_empty' => false ]);
				if ( ! empty( $terms ) && ! is_wp_error( $terms ) ) {
					$urls['taxonomy'] = get_term_link( $terms[0] );
				}
			}
		}
	
		// One Author Archive
		$authors = get_users([
			'role'   => 'Author',
			'number' => 1,
		]);
		if ( ! empty( $authors ) ) {
			$urls['author'] = get_author_posts_url( $authors[0]->ID );
		}
	
		// Example Search URL
		$urls['search'] = home_url( '/?s=example' );

		if ( $type && isset( $urls[$type] ) ) {
			return [ $urls[$type] ];
		}
	
		return $urls;
	}

	public function penci_generate_critical_css() {
		check_ajax_referer( 'ajax-nonce', 'nonce' );

		$type = $_REQUEST['type'];
		$id   = isset( $_REQUEST['id'] ) ? sanitize_textarea_field( wp_unslash( $_REQUEST['id'] ) ) : '';
		$url  = [];

		if ( $type == 'page' ) {
			$url[] = get_permalink( $id );
		} elseif ( $type == 'term' ) {
			$url[] = get_term_link( (int) $id );
		} elseif ( $type == 'auto' ) {
			$url = $this->penci_get_site_urls( $id );
		} elseif ( $type == 'manual' ) {
			$url[] = esc_url_raw( $id );
		}

		if ( $url ) {
			$errors = [];
		
			// Prevent timeout
			@set_time_limit( 0 );
			ignore_user_abort( true );
			
			foreach ( $url as $single_url ) {
				$single_url = add_query_arg( 'critical_css', '1', $single_url );
		
				// Keep timeout small so request can't hang forever
				$response = wp_remote_get( $single_url, [
					'timeout'   => 500,        // 30 seconds per URL max
					'sslverify' => false,
				] );
		
				if ( is_wp_error( $response ) ) {
					$errors[] = $response->get_error_message();
				} else {
					$code = wp_remote_retrieve_response_code( $response );
					if ( $code !== 200 ) {
						$errors[] = "Error {$code} at $single_url";
					}
				}
			}
		
			if ( ! empty( $errors ) ) {
				wp_send_json_error( implode( '; ', $errors ) );
			} else {
				wp_send_json_success( __( 'Generated successfully!', 'penci-shortcodes' ) );
			}
		} else {
			wp_send_json_error( __( 'Invalid URL', 'penci-shortcodes' ) );
		}
		
	}
}