var watchedfun;
var unwatchedfun;
watchedfun = function(eo){
	var id = $(this).data('id');
	$.ajax("./?watchedvideo=" + id);
	$(this).removeClass("watchedbtn");
	$(this).addClass("unwatchedbtn");
	$(this).html("Mark as not watched");
	$(this).removeClass("btn-primary");
	$(this).addClass("btn-danger");
	$(this).unbind("click");
	$(this).click(unwatchedfun);
};
unwatchedfun = function(eo){
	var id = $(this).data('id');
	$.ajax("./?unwatchedvideo=" + id);
	$(this).removeClass("unwatchedbtn");
	$(this).addClass("watchedbtn");
	$(this).html("Mark as watched");
	$(this).removeClass("btn-danger");
	$(this).addClass("btn-primary");
	$(this).unbind("click");
	$(this).click(watchedfun);
};
$('.watchedbtn').click(watchedfun);
$('.unwatchedbtn').click(unwatchedfun);

$("body").bind("dragenter dragover", function(){
    $("#link_grabber").addClass("active");
    $("#notice").addClass("active");
}).bind("dragleave dragexit", function(){
	$("#notice").removeClass("active");
});

setInterval(function(){
    if($("#link_grabber").val() != ""){
        var val = $("#link_grabber").val();
        $("#link_grabber").val("").removeClass("active");
        $("#notice").removeClass("active");
		$("#add_link").val(val);
		$("#add_link_form").submit();
    }
}, 100);