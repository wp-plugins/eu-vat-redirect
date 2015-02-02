<?php

if ( !defined( 'WP_UNINSTALL_PLUGIN' ) ) 
    exit();

// Leave no trail
$option_name = 'plugin_option_name';

if ( !is_multisite() )  {

	$opt_locationslugid = get_option("euvat_locationslug_id") ;
	$opt_confirmslugid = get_option("euvat_confirmslug_id") ;
	if ($opt_locationslugid) {
		wp_delete_post($opt_locationslugid,true);
		delete_option("euvat_locationslug_id") ;
	}

	// n.b. as the confirm page may have bespoke content, we do not delete it.
	delete_option("euvat_locationslug") ;
	delete_option("euvat_vatfreeurl");
	delete_option("euvat_needvaturl");
	delete_option("euvat_vatfreeurl_m");
	delete_option("euvat_needvaturl_m");
	delete_option("euvat_vatfreeurl_tmp");
	delete_option("euvat_needvaturl_tmp");
	delete_option("euvat_productid");
	delete_option("euvat_ukvat");
	delete_option("euvat_locationslug_id") ;
	delete_option("euvat_confirmslug_id") ;
	delete_option("euvat_confirmslug") ;
	delete_option("euvat_confirm") ;
	delete_option("euvat_numproducts") ;
	delete_option("euvat_db_update");
	wp_clear_scheduled_hook("euvat_update_geoip");

} else  {
    global $wpdb;
    $blog_ids = $wpdb->get_col( "SELECT blog_id FROM $wpdb->blogs" );
    $original_blog_id = get_current_blog_id();

    foreach ( $blog_ids as $blog_id ) {
        switch_to_blog( $blog_id );

	$opt_locationslugid = get_option("euvat_locationslug_id") ;
	$opt_confirmslugid = get_option("euvat_confirmslug_id") ;
	if ($opt_locationslugid) {
		wp_delete_post($opt_locationslugid,true);
		delete_option("euvat_locationslug_id") ;
	}

	// n.b. as the confirm page may have bespoke content, we do not delete it.
	delete_option("euvat_locationslug") ;
	delete_option("euvat_vatfreeurl");
	delete_option("euvat_needvaturl");
	delete_option("euvat_vatfreeurl_m");
	delete_option("euvat_needvaturl_m");
	delete_option("euvat_vatfreeurl_tmp");
	delete_option("euvat_needvaturl_tmp");
	delete_option("euvat_productid");
	delete_option("euvat_ukvat");
	delete_option("euvat_locationslug_id") ;
	delete_option("euvat_confirmslug_id") ;
	delete_option("euvat_confirmslug") ;
	delete_option("euvat_confirm") ;
	delete_option("euvat_numproducts") ;

	delete_option("euvat_db_update");
	wp_clear_scheduled_hook("euvat_update_geoip");

    }

    switch_to_blog( $original_blog_id );
}

