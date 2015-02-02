<?php
/**
 * Plugin Name: EU VAT Redirect
 * Plugin URI: http://andrewbowden.me.uk/wordpress/eu-vat-redirect
 * Description: Allows you to set buy links differently for visitors from the EU and outside the EU, for VAT purposes.
 * Version: 1.1.0
 * Author: Andrew Bowden
 * Author URI: http://andrewbowden.me.uk/
 * License: GPL2 or later
 */
/*  Copyright 2014 Andrew Bowden  (email : bods@durge.org)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as 
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/


/* 
Snags list

1) on activation, if page is in trash (which it shouldn't be), it is not re-created and a new page is created.
2) if someone deletes the redirect page, it is not re-created unless they activate/deactivate the plugin

Things to do
1) check uninstall script works
2) document
3) seeing as we have the ID for the redirect page, we probably should work out the slug from than at all times (i.e. on the settings page), rather than store a variable
4) split out into seperate files for clarity 
*/
defined('ABSPATH') or die("Your name's not down, you're not coming in");

// Admin page contained in seperate file
include_once dirname( __FILE__ ) . '/eu-vat-redirect-admin.php';


// URL Validator
function euvat_validateurl($url) {
	if (!filter_var($url,FILTER_VALIDATE_URL)) {
		return false ;
	} else {
		return true ; 
	}
}

// IP address detector
// Remember when this stuff was simple, eh?
function euvat_ipdetect() {
        if (isset($_SERVER['HTTP_CLIENT_IP'])) {
                $userIP = $_SERVER['HTTP_CLIENT_IP'] ;
        } elseif (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
                $userIP = $_SERVER['HTTP_X_FORWARDED_FOR'] ;
        } elseif (isset($_SERVER['HTTP_X_FORWARDED'])) {
                $userIP = $_SERVER['HTTP_X_FORWARDED'] ;
        } elseif (isset($_SERVER['HTTP_FORWARDED_FOR'])) {
                $userIP = $_SERVER['HTTP_FORWARDED_FOR'] ; 
        } elseif (isset($_SERVER['HTTP_FORWARDED'])) {
                $userIP = $_SERVER['HTTP_FORWARDED'] ;
        } elseif (isset($_SERVER['REMOTE_ADDR'])) {
                $userIP = $_SERVER['REMOTE_ADDR'] ;
        } else {
                $userIP = "NONE" ;
        }
	return $userIP ;
}

// Check query string for product
function euvat_which_product() {
	if(isset($_REQUEST['product'])) {
		$product = sanitize_text_field($_REQUEST['product']) ;
	} else {
		// If no product set, maybe someone who had the original "one product only" version of the plugin.  
		// If so, use product = init exists
		$product = "init" ;
	}
	return $product ;
}





// Country detection script
function euvat_get_country() {

	$userCountry = "NONE" ;
	$ipaddress = euvat_ipdetect() ;

        if (isset($_REQUEST['ip'])) {
                $ipaddress = $_REQUEST['ip'] ;
                if(!filter_var($ipaddress, FILTER_VALIDATE_IP)) {
                        wp_die("Not a valid IP address") ;
                }
        } else {
                $ipaddress = euvat_ipdetect() ;
        } 

	include(plugin_dir_path(__FILE__)."/extlib/geoip.inc") ;
	if(filter_var($ipaddress, FILTER_VALIDATE_IP)) {
       		if(filter_var($ipaddress, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
                	$gi = geoip_open(plugin_dir_path( __FILE__ )."/db/GeoIPv6.dat",GEOIP_STANDARD);
                	$userCountry = geoip_country_code_by_addr_v6($gi,$ipaddress) ;
                	geoip_close($gi);
       	 	} else {
                	$gi = geoip_open(plugin_dir_path( __FILE__ )."/db/GeoIP.dat",GEOIP_STANDARD);
                	$userCountry = geoip_country_code_by_addr($gi,$ipaddress) ;
                	geoip_close($gi);
       		}
	} else {
		$userCountry = "NONE" ;
	}

	return $userCountry ;
}





// REDIRECT page
add_action('template_redirect','euvat_redirect_template');
function euvat_redirect_template($template) {
	nocache_headers() ;

	$countriesInEU = Array("BE","BG","CZ","DK","DE","EE","IE","GR","ES","FR","HR","IT","CY","LV","LT","LU","HU","MT","NL","AT","PL","PT","RO","SI","SK","FI","SE","GB","EU");
	$opt_locationslugid = get_option("euvat_locationslug_id") ;
	if ($opt_locationslugid && is_page($opt_locationslugid)) {
		$countrycode = euvat_get_country() ;
		$opt_vatfreeurl = get_option("euvat_vatfreeurl_m") ;
		$opt_needvaturl = get_option("euvat_needvaturl_m") ;
		$opt_ukvat = get_option("euvat_ukvat") ;
		$opt_confirm = get_option("euvat_confirm") ;
		$opt_confirmslug_id = get_option("euvat_confirmslug_id") ;
		$product = euvat_which_product() ;

		// Confirm if urls exist for the product.
		if (!isset($opt_vatfreeurl[$product]) || !isset($opt_needvaturl[$product])) {
			wp_die( __("Redirects have not been set for product '".$product."'.") ) ;
		} 

		// Confirm our URLs are valid
		if (euvat_validateurl($opt_vatfreeurl[$product]) == false|| euvat_validateurl($opt_needvaturl[$product]) == false) {
			wp_die( __("One or more of the URLs is not valid for product '".$product."'.") ) ;
		}

		// 
		if ($opt_ukvat == "true") {
			$countriesInEU = array_diff($countriesInEU,array("GB")) ;
		}
		if ($opt_confirmslug_id == false) {
			wp_die( __("Cannot find location confirmation page.") ) ;
		}

		if (in_array($countrycode,$countriesInEU)) {
			wp_redirect($opt_needvaturl[$product]) ;
			exit() ;
		} else {
			if ($opt_confirm == "true") {
				wp_redirect(add_query_arg( 'product', rawurlencode($product), get_permalink($opt_confirmslug_id) )) ;
			} else {
				wp_redirect($opt_vatfreeurl[$product]) ;
			}
			exit() ; 
		}
	}
}



// Location confirmation page shortcodes

function euvat_non_eu_url( $atts ) {
	$opt_vatfreeurl = get_option("euvat_vatfreeurl_m") ;
	$opt_needvaturl = get_option("euvat_needvaturl_m") ;
	$opt_ukvat = get_option("euvat_ukvat") ;

	$product = euvat_which_product() ;

	// Confirm if urls exist for the product.
	if (!isset($opt_vatfreeurl[$product]) || !isset($opt_needvaturl[$product])) {
		wp_die( __("Redirects have not been set for product '".$product."'.") ) ;
	} 
	// Confirm our URLs are valid
	if (euvat_validateurl($opt_vatfreeurl[$product]) == false|| euvat_validateurl($opt_needvaturl[$product]) == false) {
		wp_die( __("One or more of the URLs is not valid for product '".$product."'.") ) ;
	}

	if ($opt_ukvat == "true") {
		$default_link_text = "I live in the UK, or outside the European Union" ;
	} else {
		$default_link_text = "I live outside the European Union" ;
	}
	extract( shortcode_atts(
		array(
			'text' => $default_link_text,
		), $atts )
	);
	return '<a href="'.$opt_vatfreeurl[$product].'">'.$text.'</a>' ;
}
function euvat_eu_url( $atts ) {
	$opt_vatfreeurl = get_option("euvat_vatfreeurl_m") ;
	$opt_needvaturl = get_option("euvat_needvaturl_m") ;
	$opt_ukvat = get_option("euvat_ukvat") ;

	$product = euvat_which_product() ;

	// Confirm if urls exist for the product.
	if (!isset($opt_vatfreeurl[$product]) || !isset($opt_needvaturl[$product])) {
		wp_die( __("Redirects have not been set for product '".$product."'.") ) ;
	} 
	// Confirm our URLs are valid
	if (euvat_validateurl($opt_vatfreeurl[$product]) == false|| euvat_validateurl($opt_needvaturl[$product]) == false) {
		wp_die( __("One or more of the URLs is not valid for product '".$product."'.") ) ;
	}

	if ($opt_ukvat == true) {
		$default_link_text = "I live in the European Union, but not in the UK" ;
	} else {
		$default_link_text = "I live in the European Union" ;
	}
	extract( shortcode_atts(
		array(
			'text' => $default_link_text,
		), $atts )
	);
	return '<a href="'.$opt_needvaturl[$product].'">'.$text.'</a>' ;
}


function euvat_countrydetect( $atts ) {
	extract( shortcode_atts(
		array(
			'product_id' => "init" 
		), $atts )
	);

	$opt_locationslugid = get_option("euvat_locationslug_id") ;

	if(isset($opt_locationslugid)) {
		return add_query_arg('product',rawurlencode(sanitize_text_field($product_id)),get_permalink($opt_locationslugid)) ;
	}
	
}

add_shortcode( 'euvat_countrydetect', 'euvat_countrydetect' );
add_shortcode( 'euvat_non_eu_url', 'euvat_non_eu_url' );
add_shortcode( 'euvat_eu_url', 'euvat_eu_url' );







// PLUGIN ACTIVATION

register_activation_hook(__FILE__,'euvat_activation') ;
function euvat_activation() {

	global $wpdb;

	// Install the redirect page first

	$opt_locationslug = get_option("euvat_locationslug") ;
	$opt_locationslugid = get_option("euvat_locationslug_id") ;

	if ($opt_locationslug == false || $opt_locationslug == "") {
		$opt_locationslug = "location-detect" ;
	}

	if($opt_locationslugid) {
		$the_page = get_posts(
			Array(
				'ID' => $opt_locationslugid ,
				'post_type' => 'page' ,
			)
		) ;
	} 

	if(isset($the_page)) {
		// It exists!  Lets make sure it's published.
		$the_page_id = $the_page->ID ;
		$the_page->post_status = 'publish' ;
		$the_page_id = wp_update_post($the_page);
		update_option("euvat_locationslug_id",$the_page_id) ;
	} else {
		$newpage = array() ;
		$newpage['post_type'] = 'page' ;
		$newpage['post_content'] = 'This page is used by the EU VAT Redirect Plugin.  Please do not edit it in any way, or delete it.  Doing so is liable to break the plugin.' ;
		$newpage['post_title'] = 'EU VAT Redirect' ;
		$newpage['post_status'] = 'publish' ;
		$newpage['post_name'] = $opt_locationslug;
		$newpage['comment_status'] = 'closed' ;	
		$newpage['ping_status'] = 'closed' ;
		$the_page_id = wp_insert_post($newpage) ;
		update_option("euvat_locationslug_id",$the_page_id) ;
	}

	// Now do the "location confirmation" page

	$opt_confirmslug = get_option("euvat_confirmslug") ;
	$opt_confirmslugid = get_option("euvat_confirmslug_id") ;

	if ($opt_confirmslug == false || $opt_confirmslug == "") {
		$opt_confirmslug = "confirm-location" ;
	}

	if($opt_confirmslugid) {
		$the_page2 = get_posts(
			Array(
				'ID' => $opt_confirmslugid ,
				'post_type' => 'page' ,
			)
		) ;
	} 

	if(isset($the_page2)) {
		// It exists!  Lets make sure it's published.
		$the_page2_id = $the_page2->ID ;
		$the_page2->post_status = 'publish' ;
		$the_page2_id = wp_update_post($the_page);
		update_option("euvat_confirmslug_id",$the_page2_id) ;
	} else {
		$newpage = array() ;
		$newpage['post_type'] = 'page' ;
		$newpage['post_content'] = '<!-- This page is used by the EU VAT Redirect Plugin.  You can change the title, and add content below.  Some text is also automatically added by the plugin.  Please don\'t delete this page --><p style="text-align:center"><strong>[euvat_non_eu_url]</strong><br/>or<br/><strong>[euvat_eu_url]</strong></p>' ;
		$newpage['post_title'] = 'Confirm your location' ;
		$newpage['post_status'] = 'publish' ;
		$newpage['post_name'] = $opt_confirmslug;
		$newpage['comment_status'] = 'closed' ;	
		$newpage['ping_status'] = 'closed' ;
		$the_page2_id = wp_insert_post($newpage) ;
		update_option("euvat_confirmslug_id",$the_page2_id) ;
	}


}

register_deactivation_hook(__FILE__,'euvat_deactivation') ;
function euvat_deactivation() {
	$opt_locationslugid = get_option("euvat_locationslug_id") ;
	$opt_confirmslugid = get_option("euvat_confirmslug_id") ;

	if ($opt_locationslugid) {
		wp_delete_post($opt_locationslugid,true);
		delete_option("euvat_locationslug_id") ;

	}
	wp_clear_scheduled_hook("euvat_update_geoip");
}
