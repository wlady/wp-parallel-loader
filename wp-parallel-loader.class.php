<?php

if ( ! class_exists( 'ParallelLoader' ) ) {

	class ParallelLoader {
	
		protected $options = null;
	
		public function __construct() {
			$this->init();
		}
	
		/**
		* Initialize the default options during plugin activation
		*
		* @return none
		* @since 2.0.3
		*/
		protected function init () {
			if ( !($this->options = get_option( 'wp-parallel-loader' )) ) {
				$this->options = $this->defaults();
				add_option( 'wp-parallel-loader' , $this->options );
			}
		}
	
		/**
		* Return the default options
		*
		* @return array
		* @since 2.0.3
		*/
		protected function defaults () {
			return array (
				'wp-parallel-loader-images' => '',
				'wp-parallel-loader-scripts' => '',
				'wp-parallel-loader-css' => '',
				'wp-parallel-loader-hosts' => '',
				'wp-parallel-loader-repository' => '',
			);
		}
	
		/**
		* Get specific option from the options table
		*
		* @param string $option Name of option to be used as array key for retrieving the specific value
		* @return mixed
		* @since 2.0.3
		*/
		public function get_option ( $option , $options = null ) {
			if ( is_null ( $options ) ) {
				$options = &$this->options;
			}
			if ( isset ( $options[$option] ) ) {
				return $options[$option];
			} else {
				return false;
			}
		}
	
		public function getCDNs() {
			$cdns = $this->get_option('wp-parallel-loader-hosts');
			if (is_array($cdns)) {
				$empties = array();
				array_walk($cdns, function(&$item, $key) use (&$empties) {
					if ($item) {
						$item = trim(strtolower($item));
						if (substr($item, 0, 4)!=='http') {
							$item = 'http://'.$item;
						}
						if (substr($item, -1)!=='/') {
							$item .= '/';
						}
					} else {
						// mark empty item
						$empties[$key] = '';
					}
				});
				if (count($empties)) {
					// remove empty items
					$cdns = array_diff_key($cdns, $empties);
				}
				return count($cdns) ? $cdns : false;
			}
			return false;
		}
	
		/**
		* Deletes all plugin options & transient
		*/
		public function plugin_deactivation( $network_wide ) {
			delete_option ( 'wp-parallel-loader' );
		}
	}
}