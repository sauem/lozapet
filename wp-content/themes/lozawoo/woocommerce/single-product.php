<?php
/**
 * The Template for displaying all single products
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/single-product.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see         https://docs.woocommerce.com/document/template-structure/
 * @package     WooCommerce\Templates
 * @version     1.6.4
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

get_header( 'shop' ); ?>
    <div class="breadcrumb-area bg-12 text-center">
        <div class="container">
            <h1>Single Shop</h1>
            <nav aria-label="breadcrumb">
                <ul class="breadcrumb">
                    <li class="breadcrumb-item"><a href="<?= home_url() ?>">Home</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Single Shop</li>
                </ul>
            </nav>
        </div>
    </div>
<?php while ( have_posts() ) : ?>
    <?php the_post(); ?>

    <?php wc_get_template_part( 'content', 'single-product' ); ?>

<?php endwhile; ?>

<?php
get_footer( 'shop' );

/* Omit closing PHP tag at the end of PHP files to avoid "headers already sent" issues. */
