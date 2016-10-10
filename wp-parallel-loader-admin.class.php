<?php
/**
 * ParallelLoader class for admin actions
 *
 * This class contains all functions and actions required for ParallelLoader to work in the admin of WordPress
 *
 */

class ParallelLoaderAdmin extends ParallelLoader {

	/**
	 * Full file system path to the main plugin file
	 *
	 * @since 3.0.0.0
	 * @var string
	 */
	var $plugin_file;

	/**
	 * Path to the main plugin file relative to WP_CONTENT_DIR/plugins
	 *
	 * @since 3.0.0.0
	 * @var string
	 */
	var $plugin_basename;

	/**
	 * Name of options page hook
	 *
	 * @since 3.0.0.1
	 * @var string
	 */
	var $options_page_hookname;

	/**
	 * Plugin slug to detect available updates
	 * @var string
	 */
	var $plugin_slug;
	
	/**
	 * Setup backend functionality in WordPress
	 *
	 * @return none
	 * @since 3.0.0.0
	 */
	public function __construct () {
		parent::__construct();
		$this->plugin_file = dirname (__FILE__) . '/wp-parallel-loader.php';
		$this->plugin_basename = plugin_basename ( $this->plugin_file );
		$this->plugin_slug = basename(dirname(__FILE__));
		// Activation hook
		register_activation_hook ( $this->plugin_file , array ( &$this , 'init' ) );
		// Whitelist options
		add_action ( 'admin_init' , array ( &$this , 'register_settings' ) );
		// Activate the options page
		add_action ( 'admin_menu' , array ( &$this , 'add_page' ) ) ;
	}

	/**
	 * Whitelist the wp-parallel-loader options
	 *
	 * @since 3.0.0.1
	 * @return none
	 */
	function register_settings () {
		register_setting ( 'wp-parallel-loader' , 'wp-parallel-loader' , array ( &$this , 'update' ) );
	}

	/**
	 * Update/validate the options in the options table from the POST
	 *
	 * @since 3.0.0.1
	 * @return none
	 */
	function update ( $options ) {
		if ( ! empty( $_POST['wp-parallel-loader-defaults'] ) && 'Reset to Defaults' == $_POST['wp-parallel-loader-defaults'] ) {
			$this->options = $this->defaults ();
		} else {
			foreach ( $this->defaults () as $key => $value ) {
				if ( ( ! isset ( $options[$key] ) || empty ( $options[$key] ) ) ) {
					$options[$key] = $value;
				}
			}
			// save configured hosts
			$options['wp-parallel-loader-hosts'] = $this->options['wp-parallel-loader-hosts'];
			$this->options = $options;
		}
		return $this->options;
	}

	/**
	 * Add the options page
	 *
	 * @return none
	 * @since 2.0.3
	 */
	function add_page () {
		if ( current_user_can ( 'manage_options' ) ) {
			$this->options_page_hookname = add_options_page ( __( 'WP Parallel Loader' , 'wp-parallel-loader' ) , __( 'WP Parallel Loader' , 'wp-parallel-loader' ) , 'manage_options' , 'wp-parallel-loader' , array ( &$this , 'admin_page' ) );
		}
	}

	/**
	 * Output the options page
	 *
	 * @return none
	 * @since 2.0.3
	 */
	function admin_page () {
		$cdns = $this->getCDNs();
		$error = false;
		$message = '';
		if (isset($_POST['add_host'])) {
			$addHost = trim($_POST['add_host']);
			if ($this->get_host_health($addHost)) {
				if (substr($addHost, -1)!=='/') {
					$addHost .= '/';
				}
				if (is_array($cdns)) {
					$cdns[] = $addHost;
					$cdns = array_unique($cdns);
				} else {
					$cdns = array($addHost);
				}
				$this->options['wp-parallel-loader-hosts'] = $cdns;
				update_option( 'wp-parallel-loader' , $this->options );
				$message = sprintf('Host <strong>%s</strong> was added', $addHost);
			} else {
				$error = true;
				$message = sprintf('Host <strong>%s</strong> is unreachable', $addHost);
			}
		} else if (isset($_POST['hosts']) && is_array($_POST['hosts'])) {
			$cdns = array_diff($cdns, $_POST['hosts']);
			$this->options['wp-parallel-loader-hosts'] = $cdns;
			update_option( 'wp-parallel-loader' , $this->options );
			$message = 'Hosts were updated';
		}
		if ( ! @include ( dirname(__FILE__).'/wp-parallel-loader-options-page.php' ) ) {
			_e ( sprintf ( '<div id="message" class="updated fade"><p>The options page for the <strong>WP Parallel Loader</strong> cannot be displayed.  The file <strong>%s</strong> is missing.  Please reinstall the plugin.</p></div>' , dirname ( __FILE__ ) . '/wp-parallel-loader-options-page.php' ) );
		}
	}

	function get_host_health($url) {
		$header = current(get_headers($url));
		return (stristr($header, '200 OK')!==false) ? true : false;
	}
}
