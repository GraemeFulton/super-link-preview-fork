jQuery(document).ready(function($){
	$("img.lazy").each(function(index, element) {
		var src = $(this).data("src");
		if (typeof src !== 'undefined')
			$(this).attr("src", src);
	});
	
});