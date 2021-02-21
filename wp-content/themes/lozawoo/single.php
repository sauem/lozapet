<?php
/**
 * The template for displaying all single posts.
 *
 * @package storefront
 */

get_header(); ?>
    <div class="breadcrumb-area bg-12 text-center">
        <div class="container">
            <h1>Blog Details Left Sidebar</h1>
            <nav aria-label="breadcrumb">
                <ul class="breadcrumb">
                    <li class="breadcrumb-item"><a href="index.html">Home</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Blog Details Left Sidebar</li>
                </ul>
            </nav>
        </div>
    </div>
    <div class="post-area blog-area pt-110 pb-95 post-details">
        <div class="container">
            <div class="row">
                <div class="order-xl-2 order-lg-2 col-xl-9 col-lg-8">
                    <?php
                    while (have_posts()) :
                        the_post();

                        do_action('storefront_single_post_before');

                        get_template_part('content', 'single');

                        do_action('storefront_single_post_after');

                    endwhile; // End of the loop.
                    ?>
                </div>
                <div class="col-xl-3 col-lg-4">
                    <div class="sidebar-wrapper">
                        <div class="sidebar-widget sidebar-search-widget">
                            <h3>Search</h3>
                            <form action="#" class="sidebar-search-box">
                                <input type="text" placeholder="Search...">
                                <button type="button"><i class="fa fa-search"></i></button>
                            </form>
                        </div>
                        <div class="sidebar-widget">
                            <h3>Blog Archives</h3>
                            <div class="sidebar-widget-option-wrapper">

                                <?php
                                echo wp_list_categories([
                                    'taxonomy' => 'category',
                                    'title_li' => '',
                                    'show_count' => true,
                                    'separator' => '',
                                    'style' => '<div class="sidebar-widget-option">%s</div>'
                                ]);
                                ?>

                            </div>
                        </div>
                        <div class="sidebar-widget">
                            <h3>Product Archives</h3>
                            <div class="sidebar-widget-option-wrapper">
                                <?php
                                echo wp_list_categories([
                                    'taxonomy' => 'product_cat',
                                    'title_li' => '',
                                    'show_count' => true,
                                    'separator' => '<br>',
                                    'style' => '<div class="sidebar-widget-option">%s</div>'
                                ]);
                                ?>
                            </div>
                        </div>
                        <div class="sidebar-widget">
                            <h3>Post Tags</h3>
                            <ul class="sidebar-widget-tag">
                                <?php
                                echo get_the_tag_list('<li>', '', '</li>');
                                ?>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

<?php
get_footer();
