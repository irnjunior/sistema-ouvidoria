/*
   Copyright (c) nogubbins.com
 */

$(document).ready(function(){
    
    /*
	    * Presentation styles and elements
	    * Why do it here? osTicket is difficult to update for the average Joe so this is an attempt to make it as simple as possible
	*/
	
	// Add bootstrap classes to form elements
	$('input:not([style="vertical-align:top;"]), textarea, select').addClass('form-control');
	$('input[style="vertical-align:top;"] + em').css('display','block');
	
	// Add some nice icons to the nav
	
	$('#navbar-main ul li a.home').prepend('<i class="fa fa-home" aria-hidden="true"></i>');
	$('#navbar-main ul li a.kb').prepend('<i class="fa fa-lightbulb-o" aria-hidden="true"></i>');
	$('#navbar-main ul li a.new').prepend('<i class="fa fa-bullhorn" aria-hidden="true"></i>');
	$('#navbar-main ul li a.status, #navbar-main ul li a.tickets').prepend('<i class="fa fa-search" aria-hidden="true"></i>');

});