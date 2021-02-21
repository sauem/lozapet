<?php
/**
 * Related Products
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/single-product/related.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see         https://docs.woocommerce.com/document/template-structure/
 * @package     WooCommerce\Templates
 * @version     3.9.0
 */

if (!defined('ABSPATH')) {
    exit;
}

if ($related_products) : ?>

    <div class="product-area bg-gray pb-80 mb-95 related-product">
        <div class="container">
            <div class="section-title text-center">
                <div class="section-img d-flex justify-content-center">
                    <h2>Related products</h2>
                </div>
            </div>
        </div>
        <div class="container text-center">
            <div class="product-carousel">
                <?php #woocommerce_product_loop_start(); ?>
                <?php foreach ($related_products as $related_product) : ?>

                    <?php
                    $post_object = get_post($related_product->get_id());

                    setup_postdata($GLOBALS['post'] =& $post_object); // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited, Squiz.PHP.DisallowMultipleAssignments.Found
                    echo '<div class="custom-col">';
                    wc_get_template_part('content', 'product');
                    echo '</div>';
                    ?>
                <?php endforeach; ?>
                <?php #woocommerce_product_loop_end(); ?>
            </div>
        </div>
    </div>

<?php
endif;

wp_reset_postdata();
