; //defensive semicolon
//////////////////////////////
// Test if touch event exists
//////////////////////////////
function is_touch_device() {
    return jQuery('body').hasClass('touch');
}

function getParameterByName(name, url) {
    name = name.replace(/[\[]/, "\\\[").replace(/[\]]/, "\\\]");
    var regexS = "[\\?&]" + name + "=([^&#]*)";
    var regex = new RegExp(regexS);
    var results = regex.exec(url);
    if (results == null)
        return "";
    else
        return decodeURIComponent(results[1].replace(/\+/g, " "));
}

// Begin jQuery functions
(function ($) {

    $.fn.serializeObject = function () {
        var o = {};
        var a = this.serializeArray();
        $.each(a, function () {
            if (o[this.name] !== undefined) {
                if (!o[this.name].push) {
                    o[this.name] = [o[this.name]];
                }
                o[this.name].push(this.value || '');
            } else {
                o[this.name] = this.value || '';
            }
        });
        return o;
    };

    $(window).load(function () {
        /////////////////////////////////////////////
        // Product slider
        /////////////////////////////////////////////
        if ($('.product-slides').length > 0) {
            if (!$.fn.carouFredSel) {
                Themify.LoadAsync(themify_vars.url + '/js/carousel.min.js', ThemifyProductSlider);
            }
            else {
                ThemifyProductSlider();
            }
        }
        function ThemifyProductSlider() {
            // Parse data from wp_localize_script
            themifyShop.autoplay = parseInt(themifyShop.autoplay);
            themifyShop.speed = parseInt(themifyShop.speed);
            themifyShop.scroll = parseInt(themifyShop.scroll);
            themifyShop.visible = parseInt(themifyShop.visible);
            themifyShop.wrap = null != themifyShop.wrap;
            themifyShop.play = 0 != themifyShop.autoplay;

            $('.product-slides').carouFredSel({
                responsive: true,
                prev: '#product-slider .carousel-prev',
                next: '#product-slider .carousel-next',
                pagination: {
                    container: "#product-slider .carousel-pager",
                    items: themifyShop.visible
                },
                width: '100%',
                circular: themifyShop.wrap,
                infinite: themifyShop.wrap,
                auto: {
                    play: themifyShop.play,
                    pauseDuration: themifyShop.autoplay * 1000,
                    duration: themifyShop.speed
                },
                swipe: true,
                scroll: {
                    items: themifyShop.scroll,
                    duration: themifyShop.speed
                },
                items: {
                    visible: {
                        min: 1,
                        max: themifyShop.visible
                    },
                    width: 150
                },
                onCreate: function () {
                    $('.product-sliderwrap').css({'height': 'auto', 'visibility': 'visible'});
                }
            });
        }
    });

    function toggleCartTag() {
        var $ct = $('#cart-tag,#cart-wrap');
        if ($('#cart-list .product').length < 1) {
            $ct.css('visibility', 'hidden');
        } else {
            $ct.css('visibility', 'visible');
        }
    }

    $(document).ready(function () {
        $('body').addClass(is_touch_device() ? 'is_mobile' : 'is_desktop');
        /////////////////////////////////////////////
        // Enable jScrollPane if is_desktop
        /////////////////////////////////////////////
        $('.is_desktop #cart-list').jScrollPane();
        $('.is_desktop #cart-wrap').hide().css('visibility', 'visible');

        /////////////////////////////////////////////
        // Toggle Cart
        /////////////////////////////////////////////
        $('body').on('click', '#cart-tag', function (e) {
            e.preventDefault();
            $('#cart-wrap').slideToggle().css('visibility', 'visible');
            return false;
        });

        /////////////////////////////////////////////
        // Toggle sorting nav
        /////////////////////////////////////////////
        $("body").on("click", '.sort-by', function (e) {
            if ($(this).next().is(':visible')) {
                $(this).next().slideUp();
                $(this).removeClass('active');
            }
            else {
                $(this).next().slideDown();
                $(this).addClass('active');
            }
            e.preventDefault();
        }).on("hover", '.orderby-wrap', function (e) {
            e.preventDefault();
            if (e.type == 'mouseenter' && !$(this).find('.orderby').is(':visible')) {
                $(this).find('.orderby').slideDown();
                $(this).find('.sort-by').addClass('active');
            }
            else if (e.type == 'mouseleave' && $(this).find('.orderby').is(':visible') && $(this).find('.sort-by').hasClass('active')) {
                $(this).find('.orderby').slideUp();
                $(this).find('.sort-by').removeClass('active');
            }

        }).on('wc_fragments_refreshed wc_fragments_loaded', function () {
            $('.is_desktop #cart-list').jScrollPane();
            $('.is_desktop #cart-wrap').hide().css('visibility', 'visible');

            toggleCartTag();
        });
        // Toggle Cart Button
        toggleCartTag();

       

        /////////////////////////////////////////////
        // Add to cart ajax
        /////////////////////////////////////////////
        if (woocommerce_params.option_ajax_add_to_cart == 'yes') {

            // Add to cart
            var $loadingIcon, cart = $('#cart-wrap');
            $('body').on('adding_to_cart', function (e, $button, data) {
                // hide cart wrap
                cart.hide();

                $('#cart-loader').addClass('loading');

                // This loading icon
                $loadingIcon = $('.loading-product', $button.closest('.product')).first();
                $loadingIcon.show();
            }).on('added_to_cart removed_from_cart', function (e, fragments, cart_hash) {
                cart.hide();

                if (typeof $loadingIcon !== 'undefined') {
                    // Hides loading animation
                    $loadingIcon.hide(300, function () {
                        $(this).addClass('loading-done');
                    });
                    $loadingIcon
                            .fadeIn()
                            .delay(500)
                            .fadeOut(300, function () {
                                $(this).removeClass('loading-done');
                            });
                }

                // Toggle Cart Button
                toggleCartTag();
                // close lightbox
                if ($('.mfp-content.themify_product_ajax').is(':visible')) {
                    $.magnificPopup.close();
                }
            });

            // remove item ajax
            $(document).on('click', '.remove-item-js', function () {

                $('#cart-loader').addClass('loading');

                // AJAX add to cart request
                var $thisbutton = $(this),
                    data = {
                        action: 'theme_delete_cart',
                        remove_item: $thisbutton.attr('data-product-key')
                    };

                // Ajax action
                $.post(woocommerce_params.ajax_url, data, function (response) {

                    var fragments = response.fragments,
                    cart_hash = response.cart_hash;
                    // Changes button classes
                    if ($thisbutton.parent().find('.added_to_cart').size() == 0)
                        $thisbutton.addClass('added');

                    // Replace fragments
                    if (fragments) {
                        $.each(fragments, function (key, value) {
                            $(key).addClass('updating').replaceWith(value);
                        });
                    }
                    // Trigger event so themes can refresh other areas
                    $('body').trigger('removed_from_cart', [fragments, cart_hash]);

                });

                return false;
            });

            // Ajax add to cart in single page
            ajax_add_to_cart_single_page();

        }

        // ajax variation lightbox
        function lightboxCallback(context) {
            $("a.variable-link", context).each(function () {
                $(this).magnificPopup({
                    type: 'ajax',
                    callbacks: {
                        updateStatus: function (data) {
                            $('.mfp-content').addClass('themify_product_ajax themify_variable_product_ajax');
                            ajax_variation_callback();
                        }
                    }
                });
            });
        }
        function ajax_variation_lightbox(context) {
            if ($("a.variable-link", context).length > 0) {
                Themify.LoadCss(themify_vars.url + '/css/lightbox.css', null);
                Themify.LoadAsync(themify_vars.url + '/js/lightbox.min.js', function () {
                    lightboxCallback(context)
                    return ('undefined' !== typeof $.fn.magnificPopup);
                });
            }
        }
        // Initial ajax variation lightbox
        if ('' != themifyScript.variableLightbox) {
            ajax_variation_lightbox(document);
            // Ajax variation lightbox for infinite scroll items
            $(document).on('newElements', function () {
                ajax_variation_lightbox($('.infscr_newElements'));
            });
        }

        // reply review
        $('.reply-review').click(function () {
            $('#respond').slideToggle('slow');
            return false;
        });

        // add review
        $('.add-reply-js').click(function () {
            $(this).hide();
            $('#respond').slideDown('slow');
            $('#cancel-comment-reply-link').show();
            return false;
        });
        $('#reviews #cancel-comment-reply-link').click(function () {
            $(this).hide();
            $('#respond').slideUp();
            $('.add-reply-js').show();
            return false;
        });

        /*function ajax add to cart in single page */
        function ajax_add_to_cart_single_page() {
            $(document).on('submit', 'form.cart', function (e) {

                if ( !$(this).find('.quantity').length ) return;
				e.preventDefault();

                var data = $(this).serializeObject(),
                    data2 = {action: 'theme_add_to_cart'};
                    if($(this).find('input[name="add-to-cart"]').length===0){
                        data2['add-to-cart'] = $(this).find('[name="add-to-cart"]').val();
                    }
                $.extend(true, data, data2);

                // Trigger event
                $('body').trigger('adding_to_cart', [$(this), data]);

                // Ajax action
                $.post(woocommerce_params.ajax_url, data, function (response) {
                    if (!response)
                        return;
                    if (themifyShop.redirect) {
                        window.location.href = themifyShop.redirect;
                        return;
                    }
                    var fragments = response.fragments,
                    cart_hash = response.cart_hash;
                    // Replace fragments
                    if (fragments) {
                        $.each(fragments, function (key, value) {
                             $(key).addClass('updating').replaceWith(value);
                        });
                    }
                    // Trigger event so themes can refresh other areas
                    $('body').trigger('added_to_cart', [fragments, cart_hash]);

                });
            });
        }

        /**
         * Limit the number entered in the quantity field.
         * @param $obj The quantity field object.
         * @param max_qty The max quantity allowed per the inventory current stock.
         */
        function limitQuantityByInventory($obj, max_qty) {
            var qty = $obj.val();
            if (qty > max_qty) {
                $obj.val(max_qty);
            }
        }

        // Limit number entered manually in quantity field in single view
        if ($('body').hasClass('single-product')) {
            $('.entry-summary').on('keyup', 'input[name="quantity"][max]', function () {
                limitQuantityByInventory($('input[name="quantity"]'), parseInt($(this).attr('max'), 10));
            });
        }

        $(document).on('click', '.plus, .minus', function () {

            // Get values
            var $qty = $(this).closest('.quantity').find('.qty'),
                    currentVal = parseFloat($qty.val()),
                    max = parseFloat($qty.prop('max')),
                    min = parseFloat($qty.prop('min')),
                    step = parseFloat($qty.prop('step'));

            // Format values
            if (!currentVal) {
                currentVal = 1;
            }
            if (!max) {
                max = false;
            }
            if (!min) {
                min = false;
            }
            if (!step) {
                step = 1;
            }
            // Change the value
            if ($(this).hasClass('plus')) {
                currentVal = max && currentVal >= max ? max : currentVal + step;
            } else {
                currentVal = min && currentVal <= min ? min : (currentVal > step ? currentVal - step : currentVal);
            }
            // Trigger change event
            $qty.val(currentVal).trigger('change');
        }).on('keyup', 'form.cart input[name="quantity"]', function () {
            var $max = parseFloat($(this).prop('max'));
            if ($max > 0) {
                limitQuantityByInventory($(this), parseInt($max, 10));
            }
        });

        /* function ajax variation callback */
        function ajax_variation_callback() {
            var forms = $('.variations_form');
            //Themify.InitGallery(forms.closest('#product_single_wrapper').find('.woocommerce-product-gallery'),{});
			if(forms.length>0){
				forms.closest('#product_single_wrapper').find('.woocommerce-product-gallery .woocommerce-product-gallery__image a').on('click', function(e){e.stopPropagation(); return false;});
                Themify.LoadAsync(themifyShop.wc_variation_url, function(){
                    if(typeof wc_add_to_cart_variation_params ==='undefined'){
                        wc_add_to_cart_variation_params =themifyShop.variations_text;
                    }
                    forms.wc_variation_form();
                }, themifyShop.wc_version, null, function () {
                    return ('undefined' !== typeof $.fn.wc_variation_form);
                });
            }
        }

    });

}(jQuery));
