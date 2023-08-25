<?php
/**
 * Plugin Name:       Theme Docs
 * Description:       Adds theme documentation to the dashboard.
 * Requires at least: 6.1
 * Requires PHP:      7.0
 * Version:           1.0.0
 * Author:            Garrett Baldwin
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       td
 *
 * @package           td
 */

namespace TD;

if ( ! defined( 'ABSPATH' ) ) {
	return;
}

if ( ! class_exists( 'ThemeDocs' ) ) {

	/**
	 * The core plugin class.
	 *
	 * @since      1.0.0
	 * @package    Theme_Documentation
	 * @author     WDS <garrett.baldwin@webdevstudios.com>
	 */
	class ThemeDocs {

		/**
		 * Plugin instance.
		 *
		 * @see get_instance()
		 * @var object
		 */
		protected static $instance = null;

		/**
		 * Plugin version
		 *
		 * @var string
		 */
		public $version;

		/**
		 * Path to docs.
		 *
		 * @var string
		 */
		public $path_to_docs;

		/**
		 * URI to docs.
		 *
		 * @var string
		 */
		public $uri_to_docs;

		/**
		 * Get Instance.
		 *
		 * @return $instance
		 * @author Garrett Baldwin <garrett.baldwin@webdevstudios.com>
		 * @since  2023-06-30
		 */
		public static function get_instance() {
			if ( null === self::$instance ) {
				self::$instance = new self();
			}
			return self::$instance;
		}

		/**
		 * Constructor.
		 *
		 * @return void
		 * @author Garrett Baldwin <garrett.baldwin@webdevstudios.com>
		 * @since  2023-06-30
		 */
		public function __construct() {
			if ( ! function_exists( 'get_plugin_data' ) ) {
				require_once ABSPATH . 'wp-admin/includes/plugin.php';
			}
			$plugin_data   = \get_plugin_data( __FILE__ );
			$this->version = $plugin_data['Version'];

			$this->path_to_docs = apply_filters( 'theme_docs_path_to_docs', get_stylesheet_directory() . '/documentation' );
			$this->uri_to_docs  = apply_filters( 'theme_docs_uri_to_docs', get_stylesheet_directory_uri() . '/documentation' );

		}

		/**
		 * Setup plugin hooks.
		 *
		 * @return void
		 * @author Garrett Baldwin <garrett.baldwin@webdevstudios.com>
		 * @since  2023-06-30
		 */
		public function plugin_setup() {

			// add admin menu.
			add_action( 'admin_menu', [ $this, 'admin_menu' ] );

		}

		/**
		 * Adds the admin menu item.
		 *
		 * @return void
		 * @author Garrett Baldwin <garrett.baldwin@webdevstudios.com>
		 * @since  2023-07-28
		 */
		public function admin_menu() {

			$theme_docs_page = \add_menu_page(
				'Theme Docs',
				'Theme Docs',
				'edit_posts',
				'theme-documentation',
				[ $this, 'theme_documentation_page' ],
				'dashicons-text-page',
				61
			);

			// Load the JS conditionally.
			add_action( 'load-' . $theme_docs_page, [ $this, 'load_admin_js' ] );

		}

		/**
		 * Loads the plugin javascript.
		 *
		 * @return void
		 * @author Garrett Baldwin <garrett.baldwin@webdevstudios.com>
		 * @since  2023-07-28
		 */
		public function load_admin_js() {
			// Unfortunately we can't just enqueue our scripts here - it's too early. So register against the proper action hook to do it.
			add_action( 'admin_enqueue_scripts', [ $this, 'admin_enqueue_scripts' ] );
		}

		/**
		 * Adds the admin menu page markup.
		 *
		 * @return void
		 * @author Garrett Baldwin <garrett.baldwin@webdevstudios.com>
		 * @since  2023-07-28
		 */
		public function theme_documentation_page() {

			$directory_exists = file_exists( $this->path_to_docs );
			?>
		<h1>Theme Documentation</h1>
			<?php if ( ! $directory_exists ) : ?>
		<p><?php esc_html_e( 'No documentation exists.  To add it, add a /documentation folder to your theme, and add markdown files there.', 'td' ); ?></p>
		<?php else : ?>
<script>
const td = {};
td.files = <?php echo wp_kses_post( $this->get_documentation_files( $this->path_to_docs ) ); ?>
</script>
		<div id="theme-docs" class="documentation-container"></div>
		<?php endif; ?>
			<?php
		}

		/**
		 * Get documentation files and sort into array.
		 *
		 * @param string $dir Directory path.
		 * @return string JSON encoded files array.
		 * @author Garrett Baldwin <garrett.baldwin@webdevstudios.com>
		 * @since  2023-07-28
		 */
		public function get_documentation_files( $dir ) {

			// get files without .. or .
			$scanned_dir = array_diff( scandir( $dir ), [ '..', '.' ] );
			$files       = [];

			// loop through files and add to array.
			foreach ( $scanned_dir as $key => $value ) {

				$current_filepath = str_replace( $this->path_to_docs, $this->uri_to_docs, $dir . DIRECTORY_SEPARATOR . $value );

				if ( is_dir( $dir . DIRECTORY_SEPARATOR . $value ) ) {
					$files[ $value ] = $this->get_documentation_files( $dir . DIRECTORY_SEPARATOR . $value );
				} else {
					// check that file has .md extension.
					if ( strpos( $value, '.md' ) ) {
						$files[] = [
							'name'     => $value,
							'filepath' => $current_filepath,
						];
					}
				}
			}

			// sort files.
			asort( $files );

			// return json.
			return wp_json_encode( $files );

		}

		/**
		 * Enqueue scripts.
		 *
		 * @return void
		 * @author Garrett Baldwin <garrett.baldwin@webdevstudios.com>
		 * @since  2023-06-30
		 */
		public function admin_enqueue_scripts() {

			\wp_enqueue_script( 'theme-docs-script', plugin_dir_url( __FILE__ ) . '/lib/dist/index.js', [], $this->version, true );
			\wp_enqueue_style( 'theme-docs-style', plugin_dir_url( __FILE__ ) . '/lib/dist/bundle.css', [], $this->version );

		}

	}

	// initialize the plugin.
	add_action( 'plugins_loaded', [ ThemeDocs::get_instance(), 'plugin_setup' ] );

}
