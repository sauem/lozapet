<?php
remove_action('woocommerce_shop_loop_item_title', 'woocommerce_template_loop_product_title', 10);
remove_action('woocommerce_before_shop_loop_item_title', 'woocommerce_template_loop_product_thumbnail', 10);

add_action('woocommerce_after_shop_loop_item', 'woocommerce_before_shop_loop_item_title', 10);
function woocommerce_template_loop_product_title()
{
    ?>
    <h5><a href="<?= get_the_permalink() ?>"><?= get_the_title() ?></a></h5>
    <?php
}

add_action('woocommerce_before_shop_loop_item_title', 'woocommerce_template_loop_product_thumbnail', 11);
function woocommerce_template_loop_product_thumbnail()
{
    ?>
    <div class="product-image">
        <?= woocommerce_get_product_thumbnail([268, 268]); ?>

        <div class="product-hover">

        </div>
    </div>
    <?php
}

function woo_related_products_limit() {
    global $product;

    $args['posts_per_page'] = 6;
    return $args;
}
add_filter( 'woocommerce_output_related_products_args', 'jk_related_products_args', 20 );
function jk_related_products_args( $args ) {
    $args['posts_per_page'] = 6; // 4 related products
    $args['columns'] = 0; // arranged in 2 columns
    return $args;
}
