jQuery(function(c){var d=c(".mpsl-sliders-table"),b=c("#mpsl-import-export-wrapper"),f=b.find(".mpsl-export-table");d.on("click",".mpsl-delete-slider-btn",function(a){a.preventDefault();var b=c(this),e=c(this).attr("data-mpsl-slider-id");if(0==confirm(MPSL.Vars.lang.slider_want_delete_single.replace("%d",e)))return!0;b.attr("disabled","disabled");c.ajax({type:"POST",url:MPSL.Vars.ajax_url,data:{action:"mpsl_delete_slider",nonce:MPSL.Vars.nonces.delete_slider,id:e},success:function(a){b.removeAttr("disabled");
a.result&&!0===a.result?(b.closest("tr").remove(),d.find("tbody>tr").length||d.hide(),window.location.reload(!0),MPSL.Functions.showMessage(MPSL.Vars.lang.slider_deleted_id.replace("%d",e),MPSL.Functions.MSG_SUCCESS_TYPE)):MPSL.Functions.showMessage(a.error,MPSL.Functions.MSG_ERROR_TYPE)},error:function(a){console.error(a)},dataType:"JSON"})});d.on("click",".mpsl-duplicate-slider-btn",function(a){a.preventDefault();var b=c(this);b.attr("disabled","disabled");a=c(this).attr("data-mpsl-slider-id");
c.ajax({type:"POST",url:MPSL.Vars.ajax_url,data:{action:"mpsl_duplicate_slider",nonce:MPSL.Vars.nonces.duplicate_slider,id:a},success:function(a){b.removeAttr("disabled");a.hasOwnProperty("result")&&!0===a.result?(d.append(a.html),MPSL.Functions.showMessage(MPSL.Vars.lang.slider_duplicated,MPSL.Functions.MSG_SUCCESS_TYPE),window.location.reload(!0)):MPSL.Functions.showMessage(a.error,MPSL.Functions.MSG_ERROR_TYPE)},error:function(a){console.error(a)},dataType:"JSON"})});var g=b.find("#mpsl-import-form"),
h=b.find("#mpsl-export-form");b.dialog({resizable:!1,draggable:!1,autoOpen:!1,modal:!0,width:800,height:c(window).height()-85,title:MPSL.Vars.lang.import_export_dialog_title,closeText:"",dialogClass:"mpsl-import-export-dialog",close:function(a,b){},open:function(){g[0].reset();h[0].reset()}});c(".ui-widget-overlay").on("click",function(){b.dialog("isOpen")&&b.dialog("close")});c("#import-export-btn").on("click",function(){b.dialog("open")});b.on("click",".export-check-all",function(a){a=c(a.target).prop("checked");
f.find(".mpsl-export-id-checkbox").prop("checked",a)});b.on("click","#mpsl-export-btn",function(a){f.find(".mpsl-export-id-checkbox:checked").length||(a.preventDefault(),a.stopPropagation(),MPSL.Functions.showMessage(MPSL.Vars.lang.no_sliders_selected_to_export,MPSL.Functions.MSG_ERROR_TYPE))});b.on("change","input[name=mpsl_http_auth]",function(a){var d=b.find("input[name=mpsl_http_auth_login], input[name=mpsl_http_auth_password]"),e=b.find(".need-mpsl_http_auth");c(a.target).is(":checked")?(d.removeAttr("disabled").attr("required",
"required"),e.show()):(d.removeAttr("required").attr("disabled","disabled"),e.hide())})});
