<?php
	/**
	 * Plugin Name: WPCasa Migration Tool
	 * Plugin URI: https://www.thivinfo.com
	 * Description: Migrate Listing from old WPCasa Framework to the new WPCasa Plugin
	 * Author: Sébastien SERRE
	 * Author URI: https://thivinfo.com
	 * Text Domain: wpcasa-migration-tool
	 * Domain Path: /languages/
	 * Version: 1.0.0
	 **/

	if ( ! defined( 'ABSPATH' ) ) {
		exit;
	} // Exit if accessed directly

	function wpcasamt_load_textdomain() {
		load_plugin_textdomain( 'wpcasa-migration-tool', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
	}

	add_action( 'plugins_loaded', 'wpcasamt_load_textdomain' );

	function wpcasamt_migrate_posttype() {

		$args = array(
			'post_type'      => array( 'property' ),
			'posts_per_page' => - 1
		);

		$query = new WP_Query( $args );

		if ( $query->have_posts() ) {

			while ( $query->have_posts() ) {
				$query->the_post();
				wp_update_post( array( 'post_type' => 'listing' ) );

			}

		} else {
			add_action( 'admin_notices', 'wpcasamt_notice__no_cpt' );
		}

		wp_reset_postdata();

	}

	add_action( 'admin_init', 'wpcasamt_migrate_posttype' );

	function wpcasamt_notice__no_cpt() {
		?>
        <div class="notice notice-success is-dismissible">
            <p><?php _e( 'Seems all properties has been migrated! if Taxonomy are migrated too, you can remove the plugin called "WPCasa Migration Tool"', 'wpcasa-migration-tool' ); ?></p>
        </div>
		<?php
	}

	function wpcasamt_update_taxo() {
	    global $wpdb;

		/**
		 * Migrate the property-type taxonomy to listing-type taxo
		 */

	    $types = $wpdb->get_results(
	            "SELECT *
                FROM $wpdb->term_taxonomy
            WHERE taxonomy = 'property-type'"
        );
        if ( !empty($types) ){
            $wpdb->update($wpdb->prefix . 'term_taxonomy', array('taxonomy' =>  'listing-type' ), array( 'taxonomy' =>  'property-type'));
        } else {
	        add_action( 'admin_notices', 'wpcasamt_notice__no_taxo' );
        }

		/**
		 * Migrate the property-category taxonomy to listing-category taxo
		 */
		$categories = $wpdb->get_results(
			"SELECT *
                FROM $wpdb->term_taxonomy
            WHERE taxonomy = 'property-category'"
		);
		if ( !empty($categories) ){
			$wpdb->update($wpdb->prefix . 'term_taxonomy', array('taxonomy' =>  'listing-category' ), array( 'taxonomy' =>  'property-category'));
		} else {
			add_action( 'admin_notices', 'wpcasamt_notice__no_taxo' );
		}
    }
    add_action( 'admin_init', 'wpcasamt_update_taxo');

	function wpcasamt_notice__no_taxo() {
		?>
        <div class="notice notice-success is-dismissible">
            <p><?php _e( 'Seems all taxonomies has been migrated! if listings are migrated too, you can remove the plugin called "WPCasa Migration Tool"', 'wpcasa-migration-tool' ); ?></p>
        </div>
		<?php
	}