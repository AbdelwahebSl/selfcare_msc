 
jQuery(document).ready(function($) {

	"use strict";

	
/*==================================================================
        [ Daterangepicker ]*/
		try {
			$('.js-datepicker').daterangepicker({
				"singleDatePicker": true,
				"showDropdowns": true,
				"autoUpdateInput": false,
				locale: {
					format: 'DD/MM/YYYY'
				},
			});
		
			var myCalendar = $('.js-datepicker');
			var isClick = 0;
		
			$(window).on('click',function(){
				isClick = 0;
			});
		
			$(myCalendar).on('apply.daterangepicker',function(ev, picker){
				isClick = 0;
				$(this).val(picker.startDate.format('DD/MM/YYYY'));
		
			});
		
			$('.js-btn-calendar').on('click',function(e){
				e.stopPropagation();
		
				if(isClick === 1) isClick = 0;
				else if(isClick === 0) isClick = 1;
		
				if (isClick === 1) {
					myCalendar.focus();
				}
			});
		
			$(myCalendar).on('click',function(e){
				e.stopPropagation();
				isClick = 1;
			});
		
			$('.daterangepicker').on('click',function(e){
				e.stopPropagation();
			});
		
		
		} catch(er) {console.log(er);}



	var siteMenuClone = function() {

		$('.js-clone-nav').each(function() {
			var $this = $(this);
			$this.clone().attr('class', 'site-nav-wrap').appendTo('.site-mobile-menu-body');
		});


		setTimeout(function() {
			
			var counter = 0;
      $('.site-mobile-menu .has-children').each(function(){
        var $this = $(this);
        
        $this.prepend('<span class="arrow-collapse collapsed">');

        $this.find('.arrow-collapse').attr({
          'data-toggle' : 'collapse',
          'data-target' : '#collapseItem' + counter,
        });

        $this.find('> ul').attr({
          'class' : 'collapse',
          'id' : 'collapseItem' + counter,
        });

        counter++;

      });

    }, 1000);

		$('body').on('click', '.arrow-collapse', function(e) {
      var $this = $(this);
      if ( $this.closest('li').find('.collapse').hasClass('show') ) {
        $this.removeClass('active');
      } else {
        $this.addClass('active');
      }
      e.preventDefault();  
      
    });

		$(window).resize(function() {
			var $this = $(this),
				w = $this.width();

			if ( w > 768 ) {
				if ( $('body').hasClass('offcanvas-menu') ) {
					$('body').removeClass('offcanvas-menu');
				}
			}
		})

		$('body').on('click', '.js-menu-toggle', function(e) {
			var $this = $(this);
			e.preventDefault();

			if ( $('body').hasClass('offcanvas-menu') ) {
				$('body').removeClass('offcanvas-menu');
				$this.removeClass('active');
			} else {
				$('body').addClass('offcanvas-menu');
				$this.addClass('active');
			}
		}) 

	
	}; 
	siteMenuClone();



	var siteSticky = function() {
		$(".js-sticky-header").sticky({topSpacing:0});
	};
	siteSticky();

	// navigation
  var OnePageNavigation = function() {
    var navToggler = $('.site-menu-toggle');
   	$("body").on("click", ".main-menu li a[href^='#'], .smoothscroll[href^='#'], .site-mobile-menu .site-nav-wrap li a", function(e) {
      e.preventDefault();

      var hash = this.hash;

      $('html, body').animate({
        'scrollTop': $(hash).offset().top
      }, 600, 'easeInOutCirc', function(){
        window.location.hash = hash;
      });

    });
  };
  OnePageNavigation();

  var siteScroll = function() {

  	

  	$(window).scroll(function() {

  		var st = $(this).scrollTop();

  		if (st > 100) {
  			$('.js-sticky-header').addClass('shrink');
  		} else {
  			$('.js-sticky-header').removeClass('shrink');
  		}

  	}) 

  };
  siteScroll();



  $("#cin").on('keyup',function(){
			var cin = $("#cin").val();
			$("#error_cin").empty();
			if(cin.length >8 ){
				
				$("#error_cin").html('Votre CIN doit être composé 8 chiffres') ;
			}
			if(cin.length == 8 && cin == '00000000' || cin == '11111111' ){
				$("#error_cin").html('Votre CIN doit être different de 000000/111111') ;

			}
  });

  



$("#moyenne_bac").on('keyup',function(){
	var regex     = /^[0-9]{2},[0-9]{2}$/;
	$("#error_moyenne_bac").html('La moyenne du bac doit être composé par une virgule xx,xx ') ;
	var moy = $("#moyenne_bac").val();
	var moy_float = parseFloat(moy);
	//$("#error_moyenne_bac").empty();
	if(regex.test(moy) && moy_float >0 && moy_float<= 20){
		$("#error_moyenne_bac").empty();
		//$("#error_moyenne_bac").html('Votre CIN dsoit être composé 8 chiffres') ;
	}
	else {
		console.log('false');
	}

});
//$("#Textbox").rules("add", { regex: "^[0-9]{2}+,+[0-9]$" })







});