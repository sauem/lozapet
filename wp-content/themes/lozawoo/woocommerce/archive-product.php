<?php
/**
 * The Template for displaying product archives, including the main shop page which is a post type archive
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/archive-product.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see https://docs.woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 3.4.0
 */

defined('ABSPATH') || exit;

get_header('shop');

/**
 * Hook: woocommerce_before_main_content.
 *
 * @hooked woocommerce_output_content_wrapper - 10 (outputs opening divs for the content)
 * @hooked woocommerce_breadcrumb - 20
 * @hooked WC_Structured_Data::generate_website_data() - 30
 */
do_action('woocommerce_before_main_content');

?>
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

    <div class="shop-area pt-110 pb-100 bg-gray mb-95">
        <div class="container">
            <div class="row">

                <div class="col-xl-9 col-lg-8">
                    <div class="ht-product-tab">
                        <div class="ht-tab-content">
                            <div class="shop-items">

                                <?php if (woocommerce_product_loop()) {
                                    echo woocommerce_result_count();
                                } ?>
                            </div>
                        </div>
                        <div class="shop-results-wrapper">
                            <div class="shop-results">
                            </div>
                            <div class="shop-results"><span>Sort By:</span>
                                <div class="shop-select">
                                    <?php
                                    echo woocommerce_catalog_ordering();
                                    ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="ht-product-shop tab-content text-center">
                        <div class="tab-pane active show fade" id="grid" role="tabpanel">
                            <?php


                            if (woocommerce_product_loop()) {

                                /**
                                 * Hook: woocommerce_before_shop_loop.
                                 *
                                 * @hooked woocommerce_output_all_notices - 10
                                 * @hooked woocommerce_result_count - 20
                                 * @hooked woocommerce_catalog_ordering - 30
                                 */


                                woocommerce_product_loop_start();

                                if (wc_get_loop_prop('total')) {
                                    while (have_posts()) {
                                        the_post();

                                        /**
                                         * Hook: woocommerce_shop_loop.
                                         */
                                        do_action('woocommerce_shop_loop');
                                        echo '<div class="custom-col">';
                                        wc_get_template_part('content', 'product');
                                        echo '</div>';
                                    }
                                }

                                woocommerce_product_loop_end();

                                /**
                                 * Hook: woocommerce_after_shop_loop.
                                 *
                                 * @hooked woocommerce_pagination - 10
                                 */
                            } else {
                                /**
                                 * Hook: woocommerce_no_products_found.
                                 *
                                 * @hooked wc_no_products_found - 10
                                 */
                                do_action('woocommerce_no_products_found');
                            }

                            ?>
                        </div>
                    </div>
                    <?php if (woocommerce_product_loop()) { ?>
                        <div class="pagination-wrapper">
                            <p><?php echo woocommerce_result_count() ?></p>
                            <?php woocommerce_pagination(); ?>
                        </div>
                    <?php } ?>
                </div>
                <?php wc_get_template_part('global/sidebar'); ?>
            </div>
        </div>
    </div>
<?php
get_footer('shop');
