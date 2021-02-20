<?php
/**
 * The template for displaying all pages.
 *
 * This is the template that displays all pages by default.
 * Please note that this is the WordPress construct of pages
 * and that other 'pages' on your WordPress site will use a
 * different template.
 *
 * @package storefront
 */

get_header(); ?>
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
<?php
while ( have_posts() ) :
    the_post();

    do_action( 'storefront_page_before' );

    get_template_part( 'content', 'page' );

    /**
     * Functions hooked in to storefront_page_after action
     *
     * @hooked storefront_display_comments - 10
     */
    do_action( 'storefront_page_after' );

endwhile; // End of the loop.
?>

<?php
get_footer();
