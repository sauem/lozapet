<?php
/**
 * The template for displaying archive pages.
 *
 * Learn more: https://developer.wordpress.org/themes/basics/template-hierarchy/
 *
 * @package storefront
 */

get_header(); ?>
<?php get_template_part('parts/bcread') ?>
    <div class="shop-area pt-110 pb-100 bg-gray mb-95">
        <div class="container">
            <div class="row">
                <?php if (have_posts()) : ?>

                    <?php
                    get_template_part('loop');

                else :

                    get_template_part('content', 'none');

                endif;
                ?>
            </div>
        </div>
    </div>

<?php
get_footer();
