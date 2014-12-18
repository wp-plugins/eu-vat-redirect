<?php
/**
 * Plugin Name: EU VAT Redirect
 * Plugin URI: http://andrewbowden.me.uk/wordpress/eu-vat-redirect
 * Description: Allows you to set buy links differently for visitors from the EU and outside the EU, for VAT purposes.
 * Version: 1.0
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
		$opt_vatfreeurl = get_option("euvat_vatfreeurl") ;
		$opt_needvaturl = get_option("euvat_needvaturl") ;
		$opt_ukvat = get_option("euvat_ukvat") ;
		$opt_confirm = get_option("euvat_confirm") ;
		$opt_confirmslug_id = get_option("euvat_confirmslug_id") ;

		if ($opt_vatfreeurl == false || $opt_needvaturl == false) {
			wp_die( __("Redirects have not been set.  Please visit the settings page to set up EU VAT Redirect") ) ;
		} 

		if (euvat_validateurl($opt_vatfreeurl) == false || euvat_validateurl($opt_needvaturl) == false) {
			wp_die( __("One or more of the URLs is not valid.  Please visit the EU VAT Redirect settings page to correct") ) ;
		}
		if ($opt_ukvat == "true") {
			$countriesInEU = array_diff($countriesInEU,array("GB")) ;
		}
		if ($opt_confirmslug_id == false) {
			wp_die( __("Cannot find location confirmation page.  Please deactivate and re-activate EU VAT Redirect to correct") ) ;
		}

		if (in_array($countrycode,$countriesInEU)) {
			wp_redirect($opt_needvaturl) ;
			exit() ;
		} else {
			if ($opt_confirm == "true") {
				wp_redirect(get_permalink($opt_confirmslug_id)) ;
			} else {
				wp_redirect($opt_vatfreeurl) ;
			}
			exit() ; 
		}
	}
}



// Location confirmation page shortcodes

function euvat_non_eu_url( $atts ) {
	$opt_vatfreeurl = get_option("euvat_vatfreeurl") ;
	$opt_needvaturl = get_option("euvat_needvaturl") ;
	$opt_ukvat = get_option("euvat_ukvat") ;

	if ($opt_vatfreeurl == false || $opt_needvaturl == false) {
		wp_die( __("Redirects have not been set.  Please visit the settings page to set up EU VAT Redirect") ) ;
	} 

	if (euvat_validateurl($opt_vatfreeurl) == false || euvat_validateurl($opt_needvaturl) == false) {
		wp_die( __("One or more of the URLs is not valid.  Please visit the EU VAT Redirect settings page to correct") ) ;
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
	return '<a href="'.$opt_vatfreeurl.'">'.$text.'</a>' ;
}
function euvat_eu_url( $atts ) {
	$opt_vatfreeurl = get_option("euvat_vatfreeurl") ;
	$opt_needvaturl = get_option("euvat_needvaturl") ;
	$opt_ukvat = get_option("euvat_ukvat") ;

	if ($opt_vatfreeurl == false || $opt_needvaturl == false) {
		wp_die( __("Redirects have not been set.  Please visit the settings page to set up EU VAT Redirect") ) ;
	} 

	if (euvat_validateurl($opt_vatfreeurl) == false || euvat_validateurl($opt_needvaturl) == false) {
		wp_die( __("One or more of the URLs is not valid.  Please visit the EU VAT Redirect settings page to correct") ) ;
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
	return '<a href="'.$opt_needvaturl.'">'.$text.'</a>' ;
}

function euvat_countrydetect( $atts ) {
	$opt_locationslugid = get_option("euvat_locationslug_id") ;

	if($opt_locationslugid) {
		$the_page = get_posts(
			Array(
				'ID' => $opt_locationslugid ,
				'post_type' => 'page' ,
			)
		) ;
	} else {
		$the_page == false ;
	}
	return get_permalink($the_page->ID) ;
}

add_shortcode( 'euvat_countrydetect', 'euvat_countrydetect' );
add_shortcode( 'euvat_non_eu_url', 'euvat_non_eu_url' );
add_shortcode( 'euvat_eu_url', 'euvat_eu_url' );





// Admin interface

add_action( 'admin_menu', 'euvat_admin_menu' );
function euvat_admin_menu() {
	add_options_page( 'EU VAT Redirect', 'EU VAT Redirect', 'manage-options','eu-vat-redirect', 'euvat_admin_options' );
}

function euvat_admin_options() {
	if ( !current_user_can( 'manage_options' ) )  {
		wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
	}
	
	$opt_vatfreeurl = get_option("euvat_vatfreeurl") ;
	$opt_needvaturl = get_option("euvat_needvaturl") ;
	$opt_ukvat = get_option("euvat_ukvat") ;
	$opt_locationslug = get_option("euvat_locationslug") ; 
	$opt_locationslugid = get_option("euvat_locationslug_id") ;
	$opt_confirm = get_option("euvat_confirm") ;
	$opt_confirmslug = get_option("euvat_confirmslug") ;
	$opt_confirmslugid = get_option("euvat_confirmslug_id");

    	if (isset($_POST['euvat_hidden']) && $_POST['euvat_hidden'] == 'Y') {
		if (isset($_POST['euvat_vatfreeurl'])) {
			$opt_vatfreeurl = sanitize_text_field($_POST['euvat_vatfreeurl']) ;
			update_option("euvat_vatfreeurl",$opt_vatfreeurl) ;
		}
		if (isset($_POST['euvat_needvaturl'])) {
			$opt_needvaturl = sanitize_text_field($_POST["euvat_needvaturl"]) ;
			update_option("euvat_needvaturl",$opt_needvaturl) ;
		}
		if (isset($_POST['euvat_ukvat'])) {
			$opt_ukvat = $_POST["euvat_ukvat"] ;
		} else { 
			$opt_ukvat = false ;
		}
		update_option("euvat_ukvat",$opt_ukvat) ;
		if (isset($_POST['euvat_locationslug']) && $_POST['euvat_locationslug'] != "") {
			$opt_locationslug = sanitize_title($_POST["euvat_locationslug"]) ;
			$newslug = $opt_locationslug ;
		} else {
			$opt_locationslug = "" ;
			$newslug = "location-detect" ;
		}
		update_option("euvat_locationslug",$opt_locationslug) ;
		if ($opt_locationslugid) {
			$the_page = get_posts(
				Array(
					'ID' => $opt_locationslugid ,
					'post_type' => 'page' ,
				)
			) ;
			if ($the_page) {
				wp_update_post(
					array (
       						'ID' => $opt_locationslugid ,
       						'post_name' => $newslug ,
				));
			} else {
				echo '<div class="error"><p><strong>'.__("Cannot find the redirect page.  Has it been deleted?  Please deactivate and re-activate this plugin to restore.","euvat")."</strong></p></div>" ;
			}
		}

		if (isset($_POST['euvat_confirm'])) {
			$opt_confirm = $_POST["euvat_confirm"] ;
		} else { 
			$opt_confirm = false ;
		}
		update_option("euvat_confirm",$opt_confirm) ;


		if (isset($_POST['euvat_confirmslug']) && $_POST['euvat_confirmslug'] != "") {
			$opt_confirmslug = sanitize_title($_POST["euvat_confirmslug"]) ;
			$newslug = $opt_confirmslug ;
		} else {
			$opt_confirmslug = "" ;
			$newslug = "confirm-location" ;
		}
		update_option("euvat_confirmslug",$opt_confirmslug) ;


		if ($opt_confirmslugid) {
			$the_page = get_posts(
				Array(
					'ID' => $opt_confirmslugid ,
					'post_type' => 'page' ,
				)
			) ;
			if ($the_page) {
				wp_update_post(
					array (
       						'ID' => $opt_confirmslugid ,
       						'post_name' => $newslug ,
				));
			} else {
				echo '<div class="error"><p><strong>'.__("Cannot find the location confirmation page.  Has it been deleted?  Please deactivate and re-activate this plugin to restore.","euvat")."</strong></p></div>" ;
			}
		}



		echo '<div class="updated"><p><strong>'.__('Your settings have been saved.', 'euvat' ).'</strong></p></div>';
    	}


	if (euvat_validateurl($opt_vatfreeurl) == false || euvat_validateurl($opt_needvaturl) == false) {
		echo '<div class="error"><p><strong>'. __('One or more of the URLs is not valid.  Please correct this below.','euvat').'</strong></p></div>' ;
	}


    	// Now display the settings editing screen
	echo '<div class="wrap">';
	echo "<h2>" . __( 'EU VAT Redirect', 'euvat' ) . "</h2>";
    
	?>

	<form name="euvat" method="post" action="">

	<p><strong><?php _e("Non-EU Payment URL",'euvat') ; ?></strong><br/>
	<?php _e("URL for people who are not liable to pay any VAT (e.g. people outside the EU.)  Please include <code>http://</code> at the start.", 'euvat' ); ?> <br/>
	<input type="text" name="euvat_vatfreeurl" value="<?php echo $opt_vatfreeurl; ?>" size="50" />
	</p>

	<p><strong><?php _e("EU Payment URL",'euvat') ; ?></strong><br/>
	<?php _e("URL for people who are liable to pay any VAT (e.g. people inside the EU.)  Please include <code>http://</code> at the start.  This will also be used if there is any doubt about the user's country of origin.",'euvat') ; ?><br />
	<input type="text" name="euvat_needvaturl" value="<?php echo $opt_needvaturl; ?>" size="50" />
	</p>

	<p><strong><?php _e("Treat UK as VAT free",'euvat') ; ?></strong><br/>
	<input type="checkbox" name="euvat_ukvat" value="true" <?php if ($opt_ukvat == "true") { echo "checked" ; } ?> /> 
	<?php _e("Treat people in the UK as not being liable for VAT.  Tick this box if you are in the UK, are below the UK VAT threshold, and wish to treat sales in the UK as being VAT free.","euvat") ; ?></p>

	<p><strong><?php _e("Page Name For Redirect Page","euvat") ; ?></strong><br/>
	<?php _e("By default, the URL for the location detection page is <code>".get_site_url()."/location-detect</code>.  To change this, enter a new name in the box below.  If you leave this blank, the default will be used.  This page will not be visible to visitors - it simply redifects to the correct URL you have set above.","euvat") ; ?><br/>
	<?php echo get_site_url() ; ?>/<input type="text" name="euvat_locationslug" value="<?php echo $opt_locationslug ; ?>" size="50" /></p>

	<p><strong><?php _e("Confirm Location") ; ?></strong><br/>
	<input type="checkbox" name="euvat_confirm" value="true" <?php if ($opt_confirm == "true") { echo "checked" ; } ?> />
	<?php _e("Ask visitors to confirm whether they are in the EU or not.  When enabled, anyone who is believed to be outside the EU, will be asked to confirm if they are actually in the EU or not.  This can be used as a failsafe step to ensure that EU visitors are sent through to the 'VAT To Be Paid' URL.","euvat") ; ?></p>

	<p><strong><?php _e("Page Name For Confirm Location Page","euvat") ; ?></strong><br/>
	<?php _e("By default, the URL for the confirm location page is <code>".get_site_url()."/confirm-location</code>.  To change this, enter a new name in the box below.  If you leave this blank, the default will be used.","euvat") ; ?><br/>
	<?php echo get_site_url() ; ?>/<input type="text" name="euvat_confirmslug" value="<?php echo $opt_confirmslug ; ?>" size="50" /></p>

	<p class="submit">
	<input type="submit" name="Submit" class="button-primary" value="<?php esc_attr_e('Save Changes') ?>" />
	<input type="hidden" name="euvat_hidden" value="Y">
	</p>
</form>
<hr/>
<p>Found this plugin useful?</p>
<form action="https://www.paypal.com/cgi-bin/webscr" method="post" target="_top">
<input type="hidden" name="cmd" value="_s-xclick">
<input type="hidden" name="hosted_button_id" value="Z945V82JAKG4W">
<input type="image" src="https://www.paypalobjects.com/en_US/GB/i/btn/btn_donateCC_LG.gif" border="0" name="submit" alt="PayPal – The safer, easier way to pay online.">
<img alt="" border="0" src="https://www.paypalobjects.com/en_GB/i/scr/pixel.gif" width="1" height="1">
</form>
<hr>
<p>This plugin includes GeoLite data created by MaxMind, available from
<a href="http://www.maxmind.com">http://www.maxmind.com</a>.</p>
</div>
<?php } 



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
}
