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
		 * Path to glossary.
		 *
		 * @var string
		 */
		public $path_to_glossary;

		/**
		 * URI to glossary.
		 *
		 * @var string
		 */
		public $uri_to_glossary;

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

			$this->path_to_glossary = apply_filters( 'theme_docs_path_to_glossary', plugin_dir_path( __FILE__ ) . 'glossary' );
			$this->uri_to_glossary  = apply_filters( 'theme_docs_uri_to_glossary', plugin_dir_url( __FILE__ ) . 'glossary' );

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

			// Check if the path to the docs folder exists.
			$docs_directory_exists = file_exists( $this->path_to_docs );

			// Add our top level menu page.
			$theme_docs_page = \add_menu_page(
				__( 'Theme Docs', 'td' ),
				__( 'Theme Docs', 'td' ),
				'edit_posts',
				'theme-documentation',
				'',
				'dashicons-text-page',
				61
			);

			// If the docs folder exists, add the submenu page.
			if ( $docs_directory_exists ) {

				$theme_docs_docs_page = \add_submenu_page(
					'theme-documentation',
					__( 'Documentation', 'td' ),
					__( 'Documentation', 'td' ),
					'edit_posts',
					'theme-documentation',
					[ $this, 'theme_documentation_page' ],
				);

				// Load the JS conditionally.
				add_action( 'load-' . $theme_docs_docs_page, [ $this, 'load_admin_js' ] );

			}

			// Add the glossary submenu page, changing the slug
			// based on whether the docs folder exists.
			$theme_docs_glossary_page = \add_submenu_page(
				'theme-documentation',
				__( 'Glossary', 'td' ),
				__( 'Glossary', 'td' ),
				'edit_posts',
				$docs_directory_exists ? 'theme-glossary' : 'theme-documentation',
				[ $this, 'theme_glossary_page' ],
			);

			// Load the JS conditionally.
			add_action( 'load-' . $theme_docs_glossary_page, [ $this, 'load_admin_js' ] );

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
		 * Adds the docs admin menu page markup.
		 *
		 * @return void
		 * @author Garrett Baldwin <garrett.baldwin@webdevstudios.com>
		 * @since  2023-07-28
		 */
		public function theme_documentation_page() {

			// check if docs folder exists, and output html.
			$directory_exists = file_exists( $this->path_to_docs );
			?>
		<h1><?php esc_html_e( 'Theme Documentation', 'td' ); ?></h1>
			<?php if ( ! $directory_exists ) : ?>
			<script>
				const td = false;
			</script>
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
		 * Adds the glossary admin menu page markup.
		 *
		 * @return void
		 * @author Garrett Baldwin <garrett.baldwin@webdevstudios.com>
		 * @since  2023-09-29
		 */
		public function theme_glossary_page() {

			// check if glossary folder exists, and output html.
			$directory_exists = file_exists( $this->path_to_glossary );
			?>
		<h1><?php esc_html_e( 'Glossary', 'td' ); ?></h1>
			<?php if ( ! $directory_exists ) : ?>
			<script>
				const td = false;
			</script>
		<p><?php esc_html_e( 'No glossary exists.', 'td' ); ?></p>
		<?php else : ?>
<script>
const td = {};
td.files = <?php echo wp_kses_post( $this->get_documentation_files( $this->path_to_glossary, 'glossary' ) ); ?>
</script>
		<div id="theme-docs" class="documentation-container"></div>
		<?php endif; ?>
			<?php
		}

		/**
		 * Get documentation files and sort into array.
		 *
		 * @param string $dir Directory path.
		 * @param string $type Type of path.
		 * @return string JSON encoded files array.
		 * @author Garrett Baldwin <garrett.baldwin@webdevstudios.com>
		 * @since  2023-07-28
		 */
		public function get_documentation_files( $dir, $type = 'docs' ) {

			// set our path and uri keys.
			$path = 'path_to_' . $type;
			$uri  = 'uri_to_' . $type;

			// get files without .. or .
			$scanned_dir = array_diff( scandir( $dir ), [ '..', '.' ] );
			$files       = [];

			// loop through files and add to array.
			foreach ( $scanned_dir as $key => $value ) {

				$current_filepath = str_replace( $this->$path, $this->$uri, $dir . DIRECTORY_SEPARATOR . $value );

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
			// glossary needs this for case insensitive alpha sort.
			if ( 'glossary' === $type ) {
				$key_values = array_column( $files, 'name' );
				array_multisort( $key_values, SORT_ASC, SORT_NATURAL | SORT_FLAG_CASE, $files );
			} else {
				// docs is set up a bit different, to push folders to the bottom.
				asort( $files );
			}

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
