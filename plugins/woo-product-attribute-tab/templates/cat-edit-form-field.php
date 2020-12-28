<tr class="form-field">
    <th scope="row" valign="top">
        <label for="<?php echo $meta_key; ?>"><?php _e('Product additional description', 'woo-product-attribute-tab'); ?></label>
    </th>
    <td>
        <textarea class="postform" id="<?php echo $meta_key; ?>" name="<?php echo $meta_key; ?>" rows="5"><?php echo $description; ?></textarea>
        <p class="description"><?php _e('Enter a description to show for products within this category.', 'woo-product-attribute-tab'); ?></p>
    </td>
</tr>