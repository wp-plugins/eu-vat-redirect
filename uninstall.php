<?php
if (!defined('WP_UNINSTALL_PLUGIN')) {
	exit();
}

$blog_list = get_blog_list( 0, 'all' );
foreach ($blog_list AS $blog) {
	switch_to_blog($blog['blog_id']) ;
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
	delete_option("euvat_ukvat");
	delete_option("euvat_locationslug_id") ;
	delete_option("euvat_confirmslug_id") ;
	delete_option("euvat_confirmslug") ;
	delete_option("euvat_confirm") ;
}
restore_current_blog();
