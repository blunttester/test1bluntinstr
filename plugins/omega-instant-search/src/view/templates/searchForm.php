<?php
/**
 * Omega Commerce
 *
 * Licence: MIT https://opensource.org/licenses/MIT
 * Copyright: 2017 Omega Commerce LLC https://omegacommerce.com
 */
/**
 * Omega Commerce
 *
 * Licence: MIT https://opensource.org/licenses/MIT
 * Copyright: 2017 Omega Commerce LLC https://omegacommerce.com
 */
$selector = get_option('omega_search_box_selector');
if ($selector == "") {
    return;
}
?>
<style>
    .omega-search-box {
        border: 1px solid #E0E0E0;
        border-radius: 2px;
        position: relative;
        margin-bottom: 10px;
        max-width: 350px;
    }

    .omega-search-box input {
        background: #fff !important;
        box-shadow: none !important;
        border-radius: 2px !important;
        border: none !important;
        font-size: 16px !important;
        color: #737373 !important;
        line-height: 28px !important;
        padding: 0 40px 0 10px !important;
        width: 100% !important;
        box-sizing: border-box !important;
        margin: 0 !important;
        height: 28px !important;
        min-height: 28px !important;
        max-width: 100000px !important;
        outline: none !important;
    }

    .omega-search-box .button {
        width: 20px !important;
        height: 20px !important;
        display: block !important;
        right: 10px !important;
        top: 4px !important;
        position: absolute !important;
        border: none !important;;
        background: none !important;
        box-shadow: none !important;
        padding: 0 !important;;
        margin: 0 !important;
        cursor: pointer !important;
        outline: none !important;
    }

    .omega-search-box .button:after {
        background: url("data:image/svg+xml,%3Csvg width='20px' height='20px' viewBox='10 7 20 20' version='1.1' xmlns='http://www.w3.org/2000/svg' xmlns:xlink='http://www.w3.org/1999/xlink'%3E%3Cpath fill='%23E0E0E0' d='M17.4285714,7 C21.5312581,7 24.8571429,10.3258847 24.8571429,14.4285714 C24.8571429,16.2685714 24.1828571,17.96 23.0742857,19.2628571 L23.3828571,19.5714286 L24.2857143,19.5714286 L30,25.2857143 L28.2857143,27 L22.5714286,21.2857143 L22.5714286,20.3828571 L22.2628571,20.0742857 C20.96,21.1828571 19.2685714,21.8571429 17.4285714,21.8571429 C13.3258847,21.8571429 10,18.5312581 10,14.4285714 C10,10.3258847 13.3258847,7 17.4285714,7 L17.4285714,7 Z M17.4285714,9.28571429 C14.5714286,9.28571429 12.2857143,11.5714286 12.2857143,14.4285714 C12.2857143,17.2857143 14.5714286,19.5714286 17.4285714,19.5714286 C20.2857143,19.5714286 22.5714286,17.2857143 22.5714286,14.4285714 C22.5714286,11.5714286 20.2857143,9.28571429 17.4285714,9.28571429 L17.4285714,9.28571429 Z' stroke='none'%3E%3C/path%3E%3C/svg%3E") no-repeat !important;
        width: 20px !important;
        height: 20px !important;
        display: block !important;
        content: ' ' !important;
        position: relative !important;
        opacity: 1 !important;
        left: 0 !important;
        top: 0 !important;
        margin: 0 !important;
        padding: 0 !important;
    }

</style>

<div class="omega-search-box widget">
    <form role="search" action="<?php echo esc_url(home_url('/')) ?>">
        <input type="text" name="s" data-container="input" value="<?php echo get_search_query() ?>"
               placeholder="<?php echo esc_attr_x('Search &hellip;', 'placeholder') ?>">
        <button type="submit" title="Search" class="button search-button"></button>
    </form>
</div>
<script>
    function omega_insert_searchbox() {
        var div = jQuery('<?php echo $selector ?>');
        if (div.length > 0) {
            div.append(jQuery('.omega-search-box').css('display', 'block'))
        } else {
            setTimeout(omega_insert_searchbox, 20);
        }
    }
    setTimeout(omega_insert_searchbox, 20);
</script>