$(document).ready(function() {
	
	// Expand Panel
	$("#close").click(function(){
		$("div#panel").slideUp("slow");
	
	});	
	
	// Collapse Panel
	$("#open").click(function(){
		$("div#panel").slideDown("slow");	
	});		
	
	// Switch buttons from "Log In | Register" to "Close Panel" on click
	$("#toggle a").click(function () {
		$("#toggle a").toggle();
	});		
		
});

function slide_div()
{
   $("div#panel").slideDown();
} 

function slide_down()
{
   $("div#panel").slideUp();
} 