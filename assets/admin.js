jQuery(document).ready(function () {
	var $euvat_numitems = Number(jQuery("#euvat-numitems").val()) ;

	jQuery("#euvat-productsettings").on("change","input[id^=euvat-productitem-id]",function(event) {
		$the_text = jQuery(this).val() ;
		$the_id = "#"+this.id.replace("id","shortcode");
		jQuery($the_id).text('[euvat_countrydetect product_id="'+$the_text+'"]') ;
	}) ;
	jQuery("#euvat-productsettings").on("click","span[id^=euvat-productitem-delete]",function(event) {
		var $euvat_deleteconfirm = confirm("Are you sure you wish to delete this item?");
		if ($euvat_deleteconfirm == true) {
			$the_id = this.id.replace("delete","block") ;
			jQuery("#"+$the_id).remove() ;
		}

	}) ;

        jQuery("#euvat-addlink").click(function(event) {
		$euvat_numitems++ ;
		$euvat_itemvar = "euvat-productitem-" ;
		jQuery("#euvat-productsettings").append('<div class="euvat-productitem-block" id="'+$euvat_itemvar+'block-'+$euvat_numitems+'"><div class="euvat-productitem-item"><h3>Product '+$euvat_numitems+'</h3><p class="euvat-delete-button"><span class="euvat-button" id="'+$euvat_itemvar+'delete-'+$euvat_numitems+'">Delete item</span></p></div><div class="euvat-productitem-meta"><p><label for="'+$euvat_itemvar+'id-'+$euvat_numitems+'">ID</label> <input type="text" id="'+$euvat_itemvar+'id-'+$euvat_numitems+'" name="'+$euvat_itemvar+'id-'+$euvat_numitems+'" size="30" /></p><p><label for="'+$euvat_itemvar+'needvat-'+$euvat_numitems+'">EU URL</label> <input type="text" name="'+$euvat_itemvar+'needvat-'+$euvat_numitems+'" id="'+$euvat_itemvar+'needvat-'+$euvat_numitems+'" size="30" /></p><p><label for="'+$euvat_itemvar+'vatfree-'+$euvat_numitems+'">Non-EU URL</label> <input type="text" name="'+$euvat_itemvar+'vatfree-'+$euvat_numitems+'" id="'+$euvat_itemvar+'vatfree-'+$euvat_numitems+'" size="30" /></p><p><span class="euvat-label">Shortcode</span> <code id="'+$euvat_itemvar+'shortcode-'+$euvat_numitems+'">...</code></p></div></div>');
		jQuery("#euvat-numitems").val($euvat_numitems) ;
        });
}) ;
