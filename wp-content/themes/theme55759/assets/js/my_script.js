jQuery(window).load(function(){
    
    jQuery(".search-form-wrapper .fa").click(function(){
        jQuery(".static-search-form .search-form").addClass("show");
        document.getElementById("search-form-text").focus();
    })

    jQuery(document).click(function(event) {
	    if (!jQuery(event.target).is(".search-form-wrapper .fa, .search-form-text, .search-form-submit")) {
	        jQuery(".static-search-form .search-form").removeClass("show");
	    }
	})

    jQuery('.chart_1 .cherry-progress-bar').each(function(){
		var currentElement = jQuery(this);
		var percent = currentElement.find(".percent").text();
		currentElement.find('.cherry-charts-progress').attr('data-percent', percent);
	})
	    
});