<?php

function euvat_enqueue_css_script($hook) {
	if ('settings_page_eu-vat-redirect' != $hook) {
		return ; 
	}
	wp_enqueue_script('euvat_js',plugin_dir_url(__FILE__)."assets/admin.js") ;
	wp_enqueue_style('euvat_css',plugin_dir_url(__FILE__)."assets/admin.css") ;
}
add_action('admin_enqueue_scripts','euvat_enqueue_css_script') ;


add_action( 'admin_menu', 'euvat_admin_menu' );
function euvat_admin_menu() {
	$hook = add_options_page( 'EU VAT Redirect', 'EU VAT Redirect', 'manage_options','eu-vat-redirect', 'euvat_admin_options' );
	

}

function euvat_admin_options() {
	if ( !current_user_can( 'manage_options' ) )  {
		wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
	}

	// START MIGRATE TO MULTIPLE PRODUCTS CODE	
	// Check whether the options are seralised, and if not, convert

	$opt_tmp_vatfreeurl = get_option("euvat_vatfreeurl") ;
	$opt_tmp_needvaturl = get_option("euvat_needvaturl") ;
	
	if($opt_tmp_vatfreeurl) {
		if(!is_serialized($opt_tmp_vatfreeurl)) {
			$opt_productid[] = "init" ;
			$opt_vatfreeurl['init'] = $opt_tmp_vatfreeurl ;	
			update_option("euvat_vatfreeurl_m",$opt_vatfreeurl) ;
			update_option("euvat_vatfreeurl_tmp",$opt_tmp_vatfreeurl) ;
			update_option("euvat_numproducts","1") ;
			update_option("euvat_productid",array_unique($opt_productid)) ;
			delete_option("euvat_vatfreeurl") ;
		} else {
			$opt_vatfreeurl = $opt_tmp_vatfreeurl ;
		}
	}
	if($opt_tmp_needvaturl) {
		if(!is_serialized($opt_tmp_needvaturl)) {
			$opt_productid[] = "init" ;
			$opt_needvaturl['init'] = $opt_tmp_needvaturl ;
			update_option("euvat_needvaturl_m",$opt_needvaturl) ;
			update_option("euvat_needvaturl_tmp",$opt_tmp_needvaturl) ;
			update_option("euvat_numproducts","1") ;
			update_option("euvat_productid",array_unique($opt_productid)) ;
			delete_option("euvat_needvaturl") ;
		} else {
			$opt_needvaturl = $opt_tmp_needvaturl ;
		}
	}
	// END MIGRATE TO MULTIPLE PRODUCTS CODE	
	// Now go on to normal settings page stuff
	

	// Load in all the options that are stored in database
	$opt_ukvat = get_option("euvat_ukvat") ;
	$opt_locationslug = get_option("euvat_locationslug") ; 
	$opt_locationslugid = get_option("euvat_locationslug_id") ;
	$opt_confirm = get_option("euvat_confirm") ;
	$opt_confirmslug = get_option("euvat_confirmslug") ;
	$opt_confirmslugid = get_option("euvat_confirmslug_id");
	$opt_numproducts = get_option("euvat_numproducts") ;
	$opt_productid = get_option("euvat_productid");
	$opt_needvaturl = get_option("euvat_needvaturl_m") ;
	$opt_vatfreeurl = get_option("euvat_vatfreeurl_m") ;
	if (!isset($opt_numproducts)) {
		$opt_numproducts = 0 ;
	}

    	if (isset($_POST['euvat_hidden']) && $_POST['euvat_hidden'] == 'Y') {
		// Form has been submitted - let's extract the data


		if(isset($_POST['euvat-numitems'])) {
			$opt_numproducts = $_POST['euvat-numitems'] ;
		} 
	
		if ($opt_numproducts > 0 ) {
			unset($opt_productid) ;
			unset($opt_vatfreeurl) ;
			unset($opt_needvaturl) ;
			$j = 0 ;
			for ($i = 1 ; $i <= $opt_numproducts ; $i++) {
				if (isset($_POST['euvat-productitem-id-'.$i])) {
					$euvat_id = sanitize_text_field($_POST['euvat-productitem-id-'.$i]) ;
				} 
				if (isset($_POST['euvat-productitem-vatfree-'.$i])) {
					$euvat_vatfree = sanitize_text_field($_POST['euvat-productitem-vatfree-'.$i]) ;
				}
				if (isset($_POST['euvat-productitem-needvat-'.$i])) {
					$euvat_needvat = sanitize_text_field($_POST['euvat-productitem-needvat-'.$i]) ;
				}

				// First check to see that there's some content.  If all are empty,
				// then we don't need to bother with it.

				if (isset($euvat_id) || isset($euvat_vatfree) || isset($euvat_needvat)) {
					if($euvat_id == "" && $euvat_vatfree == "" && $euvat_needvat =="") {

					} else {
						if ($euvat_id == "") {		
							// Create random ID if there isn't one set.
							$euvat_id = uniqid() ;
						} 

						$opt_productid[] = $euvat_id ; 
						$opt_vatfreeurl[$euvat_id] = $euvat_vatfree ;
						$opt_needvaturl[$euvat_id] = $euvat_needvat ;


						$j++ ;


						if($euvat_vatfree == "" || $euvat_needvat == "" || $euvat_id == "") {
							$euvat_errorlog[] = __("Product ".$j." has missing data.","euvat") ;
						} elseif (euvat_validateurl($euvat_vatfree) == false || euvat_validateurl($euvat_needvat) == false) {
							// Now we check that URLs are valid
							$euvat_errorlog[] = __("Product ".$j." has an invalid URL.","euvat") ;
						}
	
					}
				}
				unset($euvat_id) ;
				unset($euvat_vatfree);
				unset($euvat_needvat);
			}
			$opt_numproducts = $j ;
			update_option("euvat_productid",$opt_productid) ;
			update_option("euvat_needvaturl_m",$opt_needvaturl) ;		
			update_option("euvat_vatfreeurl_m",$opt_vatfreeurl) ;		
			update_option("euvat_numproducts",$opt_numproducts) ;

		} 


		// Get other data
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
				$euvat_errorlog[] = __("Cannot find the redirect page.  Has it been deleted?  Please deactivate and re-activate this plugin to restore.","euvat") ;
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
				$euvat_errorlog[] = __("Cannot find the location confirmation page.  Has it been deleted?  Please deactivate and re-activate this plugin to restore.","euvat") ;
			}
		}



		echo '<div class="updated"><p><strong>'.__('Your settings have been saved.', 'euvat' ).'</strong></p></div>';
    	}

	// Display any errors

	if (isset($euvat_errorlog)) {
		echo '<div class="error">' ;
		foreach ($euvat_errorlog as $i) {
			echo "<p><strong>".$i."</strong></p>"; 	
		}
		echo "</div>";
	}


    	// Now display the settings editing screen
	echo '<div class="wrap">';
	echo "<h2>" . __( 'EU VAT Redirect', 'euvat' ) . "</h2>";
    
	?>

	<form name="euvat" method="post" action="">


	<div id="euvat-productsettings" class="euvat-settingsbox">
	<h2><?php _e('Configure your products') ?></h2>
	<p><?php _e('Please include <code>http://</code> with all links') ?></p>
	<div id="euvat-addlinkbutton"><span class="euvat-button" id="euvat-addlink"><?php _e('Add a product','euvat') ; ?></span></div>

	<?php 
	$j = 1 ;
	if ($opt_productid != false) {
		foreach (array_unique($opt_productid) as $item) {
			$euvat_itemvar = "euvat-productitem-" ;
			if (isset($opt_vatfreeurl[$item])) {
				$build_vatfreeurl = $opt_vatfreeurl[$item] ;
			} else {
				$build_vatfreeurl = "" ;
			}
			if (isset($opt_needvaturl[$item])) {
				$build_needvaturl = $opt_needvaturl[$item] ;
			} else {
				$build_needvaturl = "" ;
			}
	
			echo '<div class="euvat-productitem-block" id="'.$euvat_itemvar.'block-'.$j.'"><div class="euvat-productitem-item"><h3>Product '.$j.'</h3><p class="euvat-delete-button"><span class="euvat-button" id="'.$euvat_itemvar.'delete-'.$j.'">Delete item</span></p></div><div class="euvat-productitem-meta"><p><label for="'.$euvat_itemvar.'id-'.$j.'">ID</label> <input type="text" id="'.$euvat_itemvar.'id-'.$j.'" name="'.$euvat_itemvar.'id-'.$j.'" size="30" value="'.$item.'" /></p><p><label for="'.$euvat_itemvar.'needvat-'.$j.'">EU URL</label> <input type="text" name="'.$euvat_itemvar.'needvat-'.$j.'" id="'.$euvat_itemvar.'needvat-'.$j.'" size="30" value="'.$build_needvaturl.'" /></p><p><label for="'.$euvat_itemvar.'vatfree-'.$j.'">Non-EU URL</label> <input type="text" name="'.$euvat_itemvar.'vatfree-'.$j.'" id="'.$euvat_itemvar.'vatfree-'.$j.'" size="30" value="'.$build_vatfreeurl.'" /></p><p><span class="euvat-label">Shortcode</span> <code id="'.$euvat_itemvar.'shortcode-'.$j.'">[euvat_countrydetect product_id="'.$item.'"]</code></p></div></div>' ;
			$j++ ;
		}
	}
	?>
		
	</div>
<?php submit_button() ; ?>

	<div class="euvat-settingsbox">
	<h2>General Settings</h2>

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


	<input type="hidden" name="euvat_hidden" value="Y">
	<input type="hidden" name="euvat-numitems" id="euvat-numitems" value="<?php echo $opt_numproducts ; ?>" />

	</div>

	<?php submit_button() ; ?>	



</form>
<hr/>
<p><strong>Found this plugin useful?</strong><br/>
This plugin is free to use, however if you do find it useful, please do consider donating to help support further development.
</p>
<form action="https://www.paypal.com/cgi-bin/webscr" method="post" target="_top">
<input type="hidden" name="cmd" value="_s-xclick">
<input type="hidden" name="hosted_button_id" value="Z945V82JAKG4W">
<input type="image" src="https://www.paypalobjects.com/en_US/GB/i/btn/btn_donateCC_LG.gif" border="0" name="submit" alt="PayPal – The safer, easier way to pay online.">
<img alt="" border="0" src="https://www.paypalobjects.com/en_GB/i/scr/pixel.gif" width="1" height="1">
</form>
<hr>
<p><?php _e('This plugin includes GeoLite data created by MaxMind, available from <a href="http://www.maxmind.com">http://www.maxmind.com</a>.</p>') ?></p>
</div>
<?php } 

