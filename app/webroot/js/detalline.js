$(document).ready(function() {

	$("#b-button-enter").bind("click",function() {
		$(this).next(".b-shadow").removeClass("b-shadow_hide");
		$("#login_form_overlay").removeClass("b-shadow_hide");
		return!1
	});
	
	$("#login_form_overlay").bind("click",function(){	
		var c=$("#b-button-enter").next(".b-shadow");
		if(c)
			c.addClass("b-shadow_hide");
		$("#login_form_overlay").addClass("b-shadow_hide")
	});

	$(".b-shadow__icon_close").bind("click",function(){		
		var c=$(this).parent(".b-shadow");
		if(c)
			c.addClass("b-shadow_hide");
		$("#login_form_overlay").addClass("b-shadow_hide")
	});
	
	$(".main_menu_more").bind("click",function() {
		$(this).next(".b-filter__toggle").removeClass("b-filter__toggle_hide");
		$(".b-filter").css("z-index","0");
		$(this).parent(".b-filter").css("z-index","10");
		var c=$('<div>', {
			class: 'b-filter__overlay'
		});
		
		if($(this.parentNode))
			$(this.parentNode).append(c);
			
		$(".b-filter__overlay").bind("click",function(){	
			$(".b-filter__toggle").addClass("b-filter__toggle_hide");
			$(".b-filter__overlay").remove();
			/*Browser.ie8&&($$(".b-filter").setStyle("overflow","visible"),
				void 0!=this.getParent(".b-filter")&&(this.getParent(".b-filter").setStyle("overflow","hidden"),this.getParent(".b-filter").setStyle("overflow","visible"))
				)*/
		});

		return!1
	});
	
	$(".b-filter__link_toggler").bind("click",function() {
		$(".b-filter__toggle").addClass("b-filter__toggle_hide");
		$(".b-filter__overlay").remove();
		return!1
	});
	
	$(".b-input-hint__label").bind("click",function(){
		$(this).addClass("b-input-hint__label_hide");
		if($(this).next(".b-input"))
		{
			$(this).next(".b-input").children(".b-input__text").bind("blur",function(){				
				if($(this).val() == '') {				
					$(this).parent(".b-input").prev(".b-input-hint__label").removeClass("b-input-hint__label_hide");
				}
			});
		}
	});
	
	$(".b-input-hint .b-input__text").bind("click",function(){
		$(this).parent(".b-input-hint").children(".b-input-hint__label").addClass("b-input-hint__label_hide");
	});
	
	$("#search_by_auto_link").bind("click",function(){	
		$("#search-by-number").addClass("b-search_hide");
		$("#search-by-auto").removeClass("b-search_hide");
		$("#search_by_number_link").parent(".b-menu__item").removeClass("b-menu__item_active");
		$(this).parent(".b-menu__item").addClass("b-menu__item_active");
		document.getElementById('form-search').tp.value = 'spareparts';
		return false;
	});
	
	$("#search_by_number_link").bind("click",function(){	
		$("#search-by-number").removeClass("b-search_hide");
		$("#search-by-auto").addClass("b-search_hide");
		$("#search_by_auto_link").parent(".b-menu__item").removeClass("b-menu__item_active");
		$(this).parent(".b-menu__item").addClass("b-menu__item_active");
		document.getElementById('form-search').tp.value = 'numbers';
		return false;
	});
	
	//---accordion---------
   	$('.toggler').click(function() {
		
		var selfClick = $(this).next('.element').is(':visible');
		if(!selfClick) {
			$(this)
				.parent().parent().parent()
				.find('> li ul:visible')
				.slideToggle();
		}
		
		$(this)
		  .next('.element')
		  .stop(true, true)
		  .slideToggle();
	});
	  /*$('#accordion > li, #accordion > li > ul > li').click(function(){
		var selfClick = $(this).find('ul:first').is(':visible');
		if(!selfClick) {
		  $(this)
			.parent()
			.find('> li ul:visible')
			.slideToggle();
		}
		
		$(this)
		  .find('ul:first')
		  .stop(true, true)
		  .slideToggle();
	  });*/
	  //---END accordion---------
	  
	  //---carousel---------
	  function mycarousel_itemLoadCallback(carousel, state)
		{
			// Check if the requested items already exist
			if (carousel.has(carousel.first, carousel.last)) {
				return;
			}
	
			jQuery.get(
				'/interest_suggestions.html',
				{
					first: carousel.first,
					last: carousel.last
				},
				function(xml) {					
					mycarousel_itemAddCallback(carousel, carousel.first, carousel.last, xml);
				},
				'xml'
			);
		};

		function mycarousel_itemAddCallback(carousel, first, last, xml)
		{
			// Set the size of the carousel
			carousel.size(parseInt(jQuery('total', xml).text()));
			
			jQuery('product', xml).each(function(i) {
				carousel.add(first + i, mycarousel_getItemHTML(jQuery(this).find("image").text(), jQuery(this).find("mark_and_model").text(), jQuery(this).find("name").text(), jQuery(this).find("price").text(), jQuery(this).find("id").text(), jQuery(this).find("qty").text()));
			});
		};

		/**
		 * Item html creation helper.
		 */
		function mycarousel_getItemHTML(img,mark_and_model,name,price,id,qty)
		{
			return '<a class="b-carusel__piclink" href="'+img+'" target=_blank><img src="'+img+'" alt="'+mark_and_model+' - '+name+'" class="b-carusel__pic" border="0" width="92"></a><p class="b-carusel__txt b-carusel__txt_padtop_5">'+mark_and_model+' - '+name+'<br><font color=#EE7514>÷ена: '+price+' руб.</font><br>'+(qty>0 ? '<a class="button grey" href="/cart.html?add='+id+'&skolko=1">купить</a>':'нет в наличии')+'</p>';
		};

		jQuery('#mycarousel').jcarousel({
				scroll: 1,
				//visible: 5,
				auto: 10,
				wrap: "circular",
				// Uncomment the following option if you want items
				// which are outside the visible range to be removed
				// from the DOM.
				// Useful for carousels with MANY items.

				// itemVisibleOutCallback: {onAfterAnimation: function(carousel, item, i, state, evt) { carousel.remove(i); }},
				
				itemLoadCallback: mycarousel_itemLoadCallback
		});
		//---END carousel---------
		
		//----poll-------
		$(".b-promo__slide").children(".b-promo__link").bind("click",function(){
			var h=$(".b-promo__main-inner").hasClass("b-promo_height_80")?"min":"max";
			if("min"===h) {
				$(".b-promo__main-block").removeClass("b-layout_hide");
				$("#promo-minimize").removeClass("b-promo__slide_hide");
				$("#promo-maximize").addClass("b-promo__slide_hide");				
				 $('.b-promo__main-inner').animate({
					height: '340px'
					}, 1000, function() {
					// Animation complete.
					$(".b-promo__main-inner").removeClass("b-promo_overflow_hidden");
				});
				 $('.b-carusel').animate({
					top: '+=260'
					}, 1000);		
				$('.b-layout__page').animate({
					marginTop: '+=260'
					}, 1000);
				$(".b-promo__main-inner").toggleClass("b-promo_height_80");
			}
			if("max"===h) {
				$("#promo-minimize").addClass("b-promo__slide_hide");
				$("#promo-maximize").removeClass("b-promo__slide_hide");
				 $('.b-promo__main-inner').animate({
					height: '10px'
					}, 1000, function() {
					// Animation complete.
					$(".b-promo__main-inner").toggleClass("b-promo_height_80");
					$(".b-promo__main-block").addClass("b-layout_hide");
				});
				$('.b-carusel').animate({
					top: '-=260'
					}, 1000);		
				$('.b-layout__page').animate({
					marginTop: '-=260'
					}, 1000);
				$(".b-promo__main-inner").toggleClass("b-promo_overflow_hidden");				
			}
		});
		//---END poll-----
		
		
});

function openWindow(link,w,h) //opens new window
{
	var win = "width="+w+",height="+h+",menubar=no,location=no,resizable=yes,scrollbars=yes";
	newWin = window.open(link,'newWin',win);
	newWin.focus();
}

function showHideBlock(id) {
	if($('#'+id).css("display") == "none") 
		$('#'+id).css({"display":"block"});
	else
		$('#'+id).css({"display":"none"});
	return false;
}

function choose_model(id, sel, sp, selObj)
{	
    selObj.disabled = true;											  
	$.ajax({
		type: "GET",
		async: true,
		url: "/choose_models.html",
		data: {id: id, sel: sel},
		success: function(data, status) {
		              sp.html(data);
                             selObj.disabled = false;	
		}

	});
}

function choose_cities(id, sel, sp, selObj)
{	
    selObj.disabled = true;											  
	$.ajax({
		type: "GET",
		async: true,
		url: "/choose_cities.html",
		data: {id: id, sel: sel},
		success: function(data, status) {
		              sp.html(data);
                             selObj.disabled = false;	
		}

	});
}