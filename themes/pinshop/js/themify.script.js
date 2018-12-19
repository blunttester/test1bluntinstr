;// Themify Theme Scripts - https://themify.me/

// Begin jQuery functions
(function($) {

	// Test if touch event exists //////////////////////////////
	function is_touch_device() {
		return $('body').hasClass('touch');
	}

	// Fixed Header /////////////////////////
	FixedHeader = {
		init: function() {
			var cons = is_touch_device() ? 15 : 115,
				events = is_touch_device() ? 'touchstart.touchScroll touchmove.touchScroll scroll' : 'scroll',
				headerWrapHeight = $('#headerwrap').height();
			FixedHeader.headerHeight = headerWrapHeight - cons;
			this.activate();
			$(window).on(events, this.activate);
		},
		activate: function() {
			var scrollTop = $(window).scrollTop();
			if( scrollTop > FixedHeader.headerHeight ) {
				FixedHeader.scrollEnabled();
			} else {
				FixedHeader.scrollDisabled();
			}
		},
		scrollDisabled: function() {
			$('#headerwrap').removeClass('fixed-header');
			$('#header').removeClass('header-on-scroll');
			$('body').removeClass('fixed-header-on');
			$('#pagewrap' ).css('paddingTop', '');
		},
		scrollEnabled: function() {
			var $headerWrap = $('#headerwrap');
			$headerWrap.addClass('fixed-header');
			$('#header').addClass('header-on-scroll');
			$('#pagewrap' ).css('paddingTop', FixedHeader.headerHeight + $headerWrap.height());
			$('body').addClass('fixed-header-on');
		}
	};

$(document).ready(function($){
	
	// Execute auto width (and isotope) on products page and grid layouts
	$('body.post-type-archive-product ul.products, body.tax-product_cat ul.products, .grid4 .loops-wrapper, .grid3 .loops-wrapper, .grid2 .loops-wrapper').addClass('AutoWidthElement');
	$('ul.products .product').wrapInner('<div class="product-inner"/>');
	
	//Show slider after it's fully loaded
	$('#sliderwrap').css( {'height': 'auto', 'visibility' : 'visible'} );

	// Fixed header /////////////////////////////
	if ( themifyScript.fixedHeader ) {
		FixedHeader.init();
	}

	/////////////////////////////////////////////
	// Scroll to top 							
	/////////////////////////////////////////////
	$('.back-top a').click(function () {
		$('body,html').animate({scrollTop: 0}, 800);
		return false;
	});

	/////////////////////////////////////////////
	// Toggle top nav on mobile 							
	/////////////////////////////////////////////
	$("#top-menu-icon").click(function(){
		$("#top-nav").fadeToggle();
		$("#headerwrap #main-nav").hide();
		$(this).toggleClass("active");
	});

	/////////////////////////////////////////////
	// Toggle main nav on mobile 							
	/////////////////////////////////////////////
	$("#menu-icon").click(function(){
		$("#main-nav").fadeToggle();
		$("#headerwrap #top-nav").hide();
		$(this).toggleClass("active");
	});

	/////////////////////////////////////////////
	// Toggle searchform on mobile 				
	/////////////////////////////////////////////
	$("#search-icon").click(function(){
		$("#headerwrap #main-nav").hide();
		$("#headerwrap #top-nav").hide();
		$(this).toggleClass("active");
	});
	
	// Show/Hide search form and unfold/fold search options
	$('#headerwrap #searchform').on('touchend mouseenter', function() {
		$('#headerwrap #searchform #s').stop().animate({
			width : 140
		}, {
			queue : false,
			duration : 300
			, easing: 'easeInOutCubic'
		});
		$('#headerwrap .search-option').css('visibility', 'visible').stop().animate({
			opacity : 1,
			height : 21,
			left : 0,
			width : 117
		}, {
			queue : false,
			duration : 500
			, easing: 'easeInOutCubic'
		});
	});
	
	$('#headerwrap #searchform').on('mouseleave', function() {
		$('#headerwrap #searchform #s').stop().animate({
			width : '55'
		}, {
			queue : false,
			duration : 200
		});
		$('#headerwrap .search-option').stop().animate({
			opacity : 0,
			height : '0',
			left : 117,
			width : 0
		}, {
			queue : false,
			duration : 200
		});	
	});

	$('#headerwrap .search-option').each(function(index){
		$this = $(this);
		$('input', $this).change(function(e){
			searchValue = $(this).val();
			$(this).parent().siblings('.search-type').val( searchValue );
		});
	});
});

/////////////////////////////////////////////
// Page Width Calculation
/////////////////////////////////////////////
var ItemBoard = {
	init : function(config) {
		this.config = config;
		this.bindEvents();
	},
	columns : 0,
	itemMargin : 30,
	itemPadding : 0,
	sidebarGap : 30,
	bindEvents : function() {
		var _self = this;
		jQuery(document).ready(function() {
			_self.elementSetup()
		});
		jQuery(window).resize(function() {
			_self.elementSetup()
		});
	},

	elementSetup : function() {
		var item = jQuery(this.config.itemElement),
				viewport_width = this.viewportWidth(),
				fixwidth,
				maxWidth,
				content_w;

		this.itemWidthOuter = this.itemWidthInner() + this.itemMargin + this.itemPadding;
		this.columns = parseInt(viewport_width / this.itemWidthOuter);

		fixwidth = this.columns * this.itemWidthInner() + ((this.columns - 1) * this.itemMargin);
		maxWidth = '100%';

		// check if there sidebar
		if(jQuery('#sidebar').length > 0){
			content_w = fixwidth - (this.itemWidthOuter * 2) + 5; // somehow need to add +5 for webkit browser compatibility
			jQuery('#content').width(content_w);
			fixwidth = content_w + jQuery('#sidebar').width() + this.sidebarGap;

			if(viewport_width < 965){
				jQuery('#content').css({width : ''}); // reset width inline
				fixwidth = this.columns * this.itemWidthInner() + ((this.columns - 1) * this.itemMargin); // reset page width
			}
		}

		// make exception for width smaller than 480px then dont apply the inline width
		// assume 480 = 1 column item and apply to only viewport <= 505
		if (this.columns <= 1 && viewport_width <= 505) {
			fixwidth = 978;
			maxWidth = '94%';
		}
		jQuery(this.config.appliedTo).each(function() {
			jQuery(this).css({
				'width' : fixwidth + 'px',
				'max-width' : maxWidth
			});
		});
	},

	itemWidthInner : function() {
		var innerwidth = jQuery(this.config.itemElement).width();
		return innerwidth;
	},

	viewportWidth : function() {
		return jQuery(window).width();
	}
};

$.fn.bindWithDelay = function( type, data, fn, timeout, throttle ) {
	if ( $.isFunction( data ) ) {
		throttle = timeout;
		timeout = fn;
		fn = data;
		data = undefined;
	}
	// Allow delayed function to be removed with fn in unbind function
	fn.guid = fn.guid || ($.guid && $.guid++);

	// Bind each separately so that each element has its own delay
	return this.each(function() {
        var wait = null;
        function cb() {
            var e = $.extend(true, { }, arguments[0]);
            var ctx = this;
            var throttler = function() {
            	wait = null;
            	fn.apply(ctx, [e]);
            };
            if (!throttle) { clearTimeout(wait); wait = null; }
            if (!wait) { wait = setTimeout(throttler, timeout); }
        }
        cb.guid = fn.guid;
        $(this).bind(type, data, cb);
	});
}

function infiniteIsotope(containerSelector, itemSelectorIso, itemSelectorInf, containerInfinite){
	
	// Get max pages for regular category pages and home
	var scrollMaxPages = parseInt(themifyScript.maxPages),
		$container = $(containerSelector),
		$containerInfinite = $(containerInfinite);
	// Get max pages for Query Category pages
	if( typeof qp_max_pages != 'undefined')
		scrollMaxPages = qp_max_pages;
		
	// auto width
	if( $container.length > 0 && !$('body').hasClass('sidebar1') ){
		ItemBoard.init({
			itemElement : '.AutoWidthElement ' + itemSelectorIso,
			appliedTo   : '.grid2 .pagewidth, .grid3 .pagewidth, .grid4 .pagewidth'
		});
	}

	// isotope init
	if ( 'undefined' != typeof $.fn.isotope ) {
		$container.isotope({
			itemSelector : itemSelectorIso,
			transformsEnabled : false,
			isOriginLeft : ! $( 'body' ).hasClass( 'rtl' )
		});
	}
	$(window).resize();

	// infinite scroll
	$containerInfinite.infinitescroll({
		navSelector  : '#load-more a', 		// selector for the paged navigation
		nextSelector : '#load-more a', 		// selector for the NEXT link (to page 2)
		itemSelector : itemSelectorInf, 	// selector for all items you'll retrieve
		loadingText  : '',
		donetext     : '',
		loading 	 : { img: themifyScript.loadingImg },
		maxPage      : scrollMaxPages,
		behavior	 : 'auto' != themifyScript.autoInfinite? 'twitter' : '',
		pathParse 	 : function (path, nextPage) {
			return path.match(/^(.*?)\b\d\b(?!.*\b\d\b)(.*?$)/).slice(1);
		},
		state: {
			currPage: Number.isInteger(+themifyScript.currentPage)
				? +themifyScript.currentPage : 1
		}
	}, function(newElements, instance, url) {
		// call Isotope for new elements
		var $newElems = $(newElements).wrapInner('<div class="product-inner"/>').hide();
		$newElems.imagesLoaded(function(){

			$newElems.fadeIn();
			if ( 'undefined' != typeof $.fn.isotope ) {
				$container.isotope('appended', $newElems );
			}

			$('#infscr-loading').fadeOut('normal');
			if( 1 == scrollMaxPages ){
				jQuery('#load-more, #infscr-loading').remove();
			}

			if (history.pushState && !+themifyScript.infiniteURL) {
				history.pushState(null, null, url);
			}
			
			// Apply lightbox/fullscreen gallery to new items
			Themify.InitGallery();
			// Remove tag to recently new elements
			$('.infscr_newElements').removeClass('infscr_newElements');

			// Tag elements added as new
			$newElems.addClass('infscr_newElements');

			// Tell everyone we have new elements
			$(document).trigger('newElements');
		});

		scrollMaxPages = scrollMaxPages - 1;
		if( 1 < scrollMaxPages && 'auto' != themifyScript.autoInfinite)
			$('#load-more a').show();
	});

	// disable auto infinite scroll based on user selection
	if( 'auto' == themifyScript.autoInfinite ){
		jQuery('#load-more, #load-more a').hide();
	}
}

$(window).load(function() {
	
	if($('.products').length > 0){
		// isotope/autowidth container, isotope item, item fetched by infinite scroll, infinite scroll
		var infiniteSelector = '.woocommerce.archive';
		infiniteIsotope('.products', '.product', '#content .product', infiniteSelector + ' .products');
	}
	if($('.post').length > 0){
		// isotope/autowidth container, isotope item, item fetched by infinite scroll, infinite scroll
		infiniteIsotope('.AutoWidthElement', '.post', '#content .post', '#loops-wrapper');
	}

}); // end window load 

})(jQuery);

function createCookie(name,value) {
	document.cookie = name+"="+value+"; path=/";
}

function readCookie(name) {
	name = name + "=";
	var ca = document.cookie.split(';');
	for(var i=0;i < ca.length;i++) {
		var c = ca[i];
		while (c.charAt(0)==' ') c = c.substring(1,c.length);
		if (c.indexOf(name) == 0) return c.substring(name.length,c.length);
	}
	return null;
}