<?php
/**
 * The template for displaying product content within loops
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/content-product.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see     https://docs.woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 3.6.0
 */

defined( 'ABSPATH' ) || exit;

global $product;

// Ensure visibility.
if ( empty( $product ) || ! $product->is_visible() ) {
    return;
}
?>

<div class="single-product-item">
    <div class="product-image">
        <a href="<?= $product->get_permalink()?>">
            <?= $product->get_image([268,268])?>
        </a>
        <div class="product-hover">
            <a href="<?= $product->get_permalink()?>" type="button" class="p-cart-btn">Add to cart</a>
        </div>
    </div>
    <div class="product-text">
        <div class="product-rating">
            <i class="fa fa-star-o color"></i>
            <i class="fa fa-star-o color"></i>
            <i class="fa fa-star-o color"></i>
            <i class="fa fa-star-o"></i>
            <i class="fa fa-star-o"></i>
        </div>
        <h5><a href="<?= $product->get_permalink()?>">
                <?= $product->get_name()?>
            </a></h5>
        <div class="pro-price">
            <?= $product->get_price_html() ?>
        </div>
    </div>
</div>
