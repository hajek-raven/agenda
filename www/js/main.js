$(document).ready(function(){
	$("#drawer-trigger").click(function(){
		$("#obfuscator").addClass("visible");
		$("#drawer").addClass("opened");
	});
	$("#obfuscator").click(function(){
		$("#obfuscator").removeClass("visible");
		$("#drawer").removeClass("opened");
	});
});