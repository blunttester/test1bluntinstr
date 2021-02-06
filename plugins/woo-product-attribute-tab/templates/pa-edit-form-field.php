<tr class="form-field term-group-wrap">
    <th scope="row">
        <label for="product-tab"><?php _e('Product additional description', 'woo-product-attribute-tab'); ?></label>
    </th>
    <td>
        <textarea class="postform" id="<? echo $meta_key; ?>" name="<?php echo $meta_key; ?>" rows="5"><?php echo $description; ?></textarea>
        <p class="description">
            <?php _e('Enter a description to show within the custom attribute tab for products with this particular attribute value assigned.', 'woo-product-attribute-tab'); ?>
        </p>
    </td>
</tr>