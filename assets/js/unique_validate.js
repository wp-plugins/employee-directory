jQuery(document).ready(function($){
	$.validator.addMethod('uniqueAttr',function(val,element){
		var data_input = {};
		$.each(unique_vars.keys,function(i,val){
			data_input[val] = $('#'+val).val();
			});
		data_input['post_ID'] = $('#post_ID').val();
		var unique = true;
		$.ajax({
			type: 'GET',
			url: ajaxurl,
			cache: false,
			async: false,
			data: {action:'emd_check_unique',data_input:data_input, ptype:pagenow,myapp:unique_vars.app_name},
			success: function(response){
			unique = response;
			},
		});
		return unique;
	}, unique_vars.msg);
	$('#publish').click(function(){
		var msg = [];
		$.each(unique_vars.req_blt_tax,function(i,val){
			switch(i) {
				case 'blt_title':
					var title = $('[id^="titlediv"]').find('#title');
					if(title.val().length < 1) {
						$('#title').addClass('error');
						msg.push(val.msg);
					}
					else {
						$('#title').removeClass('error');
					}
					break;
				case 'blt_content':
					var content = $('[id^="wp-content-editor-container"]').find('#content');
					if(content.val().length < 1){
						$('#wp-content-wrap').addClass('error');
						msg.push(val.msg);
					}
					else {
						$('#wp-content-wrap').removeClass('error');
					}
					break;
				case 'blt_excerpt':
					var excerpt = $('[id^="postexcerpt"]').find('#excerpt');
					if(excerpt.val().length < 1){
						$('#excerpt').addClass('error');
						msg.push(val.msg);
					}
					else {
						$('#excerpt').removeClass('error');
					}
					break;
				default:
				//check if there is any conditional which hides this tax then don't do any required check
					if(val.hier == 0 && val.type == 'multi'){
						var tcount = $("div.tagchecklist a").length;
						var txn_div = 'tagsdiv-'+i;
					}
					else if(val.type == 'single'){
						var tcount = $("input[name='radio_tax_input["+ i + "][]']:checked").length;
						if(val.hier == 0){
							var txn_div = 'radio-tagsdiv-'+i;
						}
						else {
							var txn_div = 'radio-'+i+'div';
						}
					}
					else {
						var tcount = $("input[name='tax_input[" + i + "][]']:checked").length;
						var txn_div = i +'div';
					}
					if(tcount < 1 && $('#'+txn_div).is(':hidden') != true){
						$('#'+txn_div).css({'border-left':'4px solid #DD3D36'});
						msg.push(val.label);
					}else if($('#'+txn_div).is(':hidden') != true){
						$('#'+txn_div).attr('style','');
					}
					break;
			}
		});
		if(msg.length > 0){
			$('#publish').removeClass('button-primary-disabled');
			$('#ajax-loading').attr( 'style','');
			$('#post').siblings('#message').remove();
			$('#post').before('<div id="message" class="error"><p>'+msg.join(', ')+  ' ' + unique_vars.reqtxt + '</p></div>');
			return false;
		}
	});
});
