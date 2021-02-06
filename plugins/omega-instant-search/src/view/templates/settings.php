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
 */ /** @var OmegaCommerce\Controller\Admin\SettingController $this */ ?>
<div class="wrap">
    <?php
    if (isset($error_message)) : ?>
        <div class="notice notice-error">
            <p><?php echo __($error_message); ?></p>
        </div>
    <?php endif; ?>
    <form method="post" action="options.php">

        <?php @settings_fields('omega_search-group'); ?>
        <h2>Settings</h2>
        <table class="form-table">
            <tr valign="top">
                <th scope="row"><label for="omega_search_box_selector">Parent CSS selector</label></th>
                <td>
                    <input type="text" name="omega_search_box_selector" id="omega_search_box_selector"
                           value="<?php echo get_option('omega_search_box_selector'); ?>" size="50"/>

                    <p class="description">If you would like to <b>insert a search box</b>, please enter a CSS selector of
                        parent block (eg. <i>#logo</i>).<br>If you need a help, please contact us <a
                            href="mailto:support@omegacommerce.com">support@omegacommerce.com</a></p>
                </td>
            </tr>
            <tr valign="top">
                <th scope="row"><label for="omega_search_custom_css">Custom CSS</label></th>
                <td>
                    <textarea name="omega_search_custom_css" id="omega_search_custom_css" rows="5"
                              cols="60"><?php echo get_option('omega_search_custom_css'); ?></textarea>

                    <p class="description">HTML markup and CSS classes may change. Don’t add complex CSS.</p>
                </td>
            </tr>
            <tr>
                <th scope="row"><label for="omega_search_exclude_pages">Disable Omega Search On URL</label></th>
                <td>
                    <textarea name="omega_search_exclude_pages" id="omega_search_exclude_pages" rows="5"
                              cols="60"><?php echo get_option('omega_search_exclude_pages'); ?></textarea>
                    <p class="description">If you do not need Omega Search service on certain pages, add URLs here, one per row. Wildcards allowed.</p>
                </td>
            </tr>
            <tr valign="top">
                <th scope="row"><label for="omega_api_access_is_validate_ssl">Enable validation of SSL
                        certificate</label></th>
                <td>
                    <input type="hidden" name="omega_api_access_is_validate_ssl" value="0">
                    <input type="checkbox" name="omega_api_access_is_validate_ssl" id="omega_api_access_is_validate_ssl"
                           value="1" <?php checked('1', get_option('omega_api_access_is_validate_ssl')); ?> >
                </td>
            </tr>
            <?php echo apply_filters('omega_commerce_core_setting_form', '') ?>
        </table>
        <h2>API Access Info</h2>
        <table class="form-table">
            <tr valign="top">
                <th scope="row"><label for="omega_api_access_base_url">API URL</label></th>
                <td>
                    <input type="text" name="omega_api_access_base_url" id="omega_api_access_base_url"
                           value="<?php echo get_option('omega_api_access_base_url'); ?>" size="50"/>

                    <p class="description">Used for debugging. Don't change this</p>
                </td>
            </tr>
            <tr valign="top">
                <th scope="row"><label for="omega_api_access_header">API headers</label></th>
                <td>
                    <input type="text" name="omega_api_access_header" id="omega_api_access_header"
                           value="<?php echo get_option('omega_api_access_header'); ?>" size="50"/>

                    <p class="description">Used for debugging. Don't change this</p>
                </td>
            </tr>
        </table>
        <?php @submit_button(); ?>
    </form>
</div>