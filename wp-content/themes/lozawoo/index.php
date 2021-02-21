<?php
/**
 * The main template file.
 *
 * This is the most generic template file in a WordPress theme
 * and one of the two required files for a theme (the other being style.css).
 * It is used to display a page when nothing more specific matches a query.
 * E.g., it puts together the home page when no home.php file exists.
 * Learn more: https://developer.wordpress.org/themes/basics/template-hierarchy/
 *
 * @package storefront
 */

get_header(); ?>
<?php get_template_part('parts/slider') ?>
    <!-- Food Categry Area Start -->
    <div class="food-category-area pt-105 pb-70">
        <div class="container">
            <div class="section-title text-center">
                <div class="section-img d-flex justify-content-center">
                    <h2><span>Shopping </span>for your pet</h2>
                </div>
            </div>
        </div>
        <div class="container">
            <div class="ht-food-slider row">
                <?php
                $categories = get_terms([
                    'taxonomy' => 'product_cat',
                    'depth' => 1,
                    'hide_empty' => true,
                    'meta_query' => [
                        'key' => 'show_home',
                        'value' => 1,
                        'compare' => '='
                    ]
                ]);
                if (!empty($categories)) {
                    foreach ($categories as $category) {
                        $thumbnail_id = get_term_meta($category->term_id, 'thumbnail_id', true);
                        $image = wp_get_attachment_url($thumbnail_id);
                        ?>
                        <div class="col text-center">
                            <div class="single-food-category">
                                <a href="<?= get_term_link($category->term_id) ?>" class="food-cat-img">
                                    <img src="<?= $image ?>" alt="">
                                </a>
                                <img src="<?= ASSET ?>/img/icon/border.png" alt="">
                                <h4><a href="<?= get_term_link($category->term_id) ?>"><?= $category->name ?></a></h4>
                                <span>(<?= $category->count ?> items)</span>
                            </div>
                        </div>
                        <?php
                    }
                }
                ?>
            </div>
        </div>
    </div>
    <!-- Food Categry Area End -->
    <!-- Protuct Area Start -->
    <div class="product-area bg-1 pt-110 pb-80">
        <div class="container">
            <div class="section-title text-center">
                <div class="section-img d-flex justify-content-center">
                    <h2><span>Organic </span>featured products</h2>
                </div>
            </div>
        </div>
        <div class="container mt-5">
            <div class="product-tab-list nav" role="tablist">
                <?php if (!isEmpty($categories)) {
                    foreach ($categories as $k => $category) {
                        ?>
                        <a class="<?= $k == 0 ? 'active' : '' ?>" href="#tab<?= $k ?>" data-toggle="tab" role="tab"
                           aria-selected="true"
                           aria-controls="tab1"><?= $category->name ?></a>

                        <?php
                    }
                } ?>
            </div>
            <div class="tab-content text-center">
                <?php if (!isEmpty($categories)) {
                    foreach ($categories as $k => $category) {
                        ?>
                        <div class="tab-pane <?= $k == 0 ? 'active' : '' ?> fade" id="tab<?= $k ?>" role="tabpanel">
                            <div class="product-carousel">
                                <?php
                                $products = wc_get_products([
                                    'category' => [$category->slug],
                                    'status' => 'publish',
                                    'limit' => 12,
                                ]);
                                if (!isEmpty($products)) {
                                    foreach ($products as $product) {
                                        ?>
                                        <div class="custom-col">
                                            <div class="single-product-item">
                                                <div class="product-image">
                                                    <a href="<?= $product->get_permalink() ?>">
                                                        <?= $product->get_image([268, 268]) ?>
                                                    </a>
                                                    <div class="product-hover">
                                                        <button type="button" class="p-cart-btn">Add to cart</button>
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
                                                    <h5>
                                                        <a href="<?= $product->get_permalink() ?>"><?= $product->get_name() ?></a>
                                                    </h5>
                                                    <div class="pro-price">
                                                        <?= $product->get_price_html() ?>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <?php
                                    }
                                }
                                ?>

                            </div>
                        </div>
                        <?php
                    }
                } ?>
            </div>
        </div>
    </div>
    <!-- Protuct Area End -->
    <!-- Banner Area Start -->
    <div class="banner-area banner-one-area bg-2 fix pt-60 pb-80">
        <div class="container">
            <div class="row">
                <div class="col-lg-8 about-page">
                    <div class="row">
                        <div class="col-md-6 mb-5">
                            <h1>#01</h1>
                            <p>Quisque condimentum ipsum at velit hendrerit venenatis. Donec luctus metus enim, nunced.
                                Sed sit amet nisl id purus aliquet cursus...</p>
                        </div>
                        <div class="col-md-6 mb-5">
                            <h1>#02</h1>
                            <p>Quisque condimentum ipsum at velit hendrerit venenatis. Donec luctus metus enim, nunced.
                                Sed sit amet nisl id purus aliquet cursus...</p>
                        </div>
                        <div class="col-md-6 mb-5">
                            <h1>#03</h1>
                            <p>Quisque condimentum ipsum at velit hendrerit venenatis. Donec luctus metus enim, nunced.
                                Sed sit amet nisl id purus aliquet cursus...</p>
                        </div>
                        <div class="col-md-6 mb-5">
                            <h1>#04</h1>
                            <p>Quisque condimentum ipsum at velit hendrerit venenatis. Donec luctus metus enim, nunced.
                                Sed sit amet nisl id purus aliquet cursus...</p>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="banner-image-wrapper">
                        <div class="banner-image">
                            <img src="<?= ASSET ?>/img/banner/banner2.png" alt="">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Banner Area End -->
    <!-- Featured Area Start -->
    <div class="featured-area bg-3 pt-105 pb-90">
        <div class="container">
            <div class="row">
                <div class="col-lg-4 col-md-6">
                    <div class="featured-carousel-wrapper">
                        <h3>New <span>Accessories</span></h3>
                        <div class="feaured-carousel">
                            <?php
                            $products = wc_get_products([
                                'tag' => ['accessories'],
                                'status' => 'publish',
                                'limit' => 4,
                            ]);
                            if (!isEmpty($products)) {
                                ?>
                                <div class="single-featured-carousel">
                                    <?php foreach ($products as $product) {
                                        ?>
                                        <div class="single-featured-item">
                                            <div class="feature-image">
                                                <a href="<?= $product->get_permalink() ?>">
                                                    <?= $product->get_image([121, 121]) ?>
                                                </a>
                                            </div>
                                            <div class="product-text">
                                                <div class="product-rating">
                                                    <i class="fa fa-star-o color"></i>
                                                    <i class="fa fa-star-o color"></i>
                                                    <i class="fa fa-star-o"></i>
                                                    <i class="fa fa-star-o"></i>
                                                    <i class="fa fa-star-o"></i>
                                                </div>
                                                <h5>
                                                    <a href="<?= $product->get_permalink() ?>">
                                                        <?= $product->get_name() ?>
                                                    </a>
                                                </h5>
                                                <div class="pro-price">
                                                    <?= $product->get_price_html() ?>
                                                </div>
                                                <a href="<?= $product->get_permalink() ?>"
                                                   class="feature-cart"><i
                                                            class="icon icon-FullShoppingCart"></i>
                                                </a>
                                            </div>
                                        </div>
                                        <?php
                                    } ?>
                                </div>
                                <?php
                            }
                            ?>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6">
                    <div class="featured-carousel-wrapper">
                        <h3>Products <span>bestseller</span></h3>
                        <div class="feaured-carousel">
                            <?php
                            $products = wc_get_products([
                                'tag' => ['accessories'],
                                'status' => 'publish',
                                'limit' => 4,
                            ]);
                            if (!isEmpty($products)) {
                                ?>
                                <div class="single-featured-carousel">
                                    <?php foreach ($products as $product) {
                                        ?>
                                        <div class="single-featured-item">
                                            <div class="feature-image">
                                                <a href="<?= $product->get_permalink() ?>">
                                                    <?= $product->get_image([121, 121]) ?>
                                                </a>
                                            </div>
                                            <div class="product-text">
                                                <div class="product-rating">
                                                    <i class="fa fa-star-o color"></i>
                                                    <i class="fa fa-star-o color"></i>
                                                    <i class="fa fa-star-o"></i>
                                                    <i class="fa fa-star-o"></i>
                                                    <i class="fa fa-star-o"></i>
                                                </div>
                                                <h5>
                                                    <a href="<?= $product->get_permalink() ?>">
                                                        <?= $product->get_name() ?>
                                                    </a>
                                                </h5>
                                                <div class="pro-price">
                                                    <?= $product->get_price_html() ?>
                                                </div>
                                                <a href="<?= $product->get_permalink() ?>"
                                                   class="feature-cart"><i
                                                            class="icon icon-FullShoppingCart"></i>
                                                </a>
                                            </div>
                                        </div>
                                        <?php
                                    } ?>
                                </div>
                                <?php
                            }
                            ?>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6">
                    <div class="featured-carousel-wrapper">
                        <h3>Products <span>most popular</span></h3>
                        <div class="feaured-carousel">
                            <?php
                            $products = wc_get_products([
                                'tag' => ['accessories'],
                                'status' => 'publish',
                                'limit' => 4,
                            ]);
                            if (!isEmpty($products)) {
                                ?>
                                <div class="single-featured-carousel">
                                    <?php foreach ($products as $product) {
                                        ?>
                                        <div class="single-featured-item">
                                            <div class="feature-image">
                                                <a href="<?= $product->get_permalink() ?>">
                                                    <?= $product->get_image([121, 121]) ?>
                                                </a>
                                            </div>
                                            <div class="product-text">
                                                <div class="product-rating">
                                                    <i class="fa fa-star-o color"></i>
                                                    <i class="fa fa-star-o color"></i>
                                                    <i class="fa fa-star-o"></i>
                                                    <i class="fa fa-star-o"></i>
                                                    <i class="fa fa-star-o"></i>
                                                </div>
                                                <h5>
                                                    <a href="<?= $product->get_permalink() ?>">
                                                        <?= $product->get_name() ?>
                                                    </a>
                                                </h5>
                                                <div class="pro-price">
                                                    <?= $product->get_price_html() ?>
                                                </div>
                                                <a href="<?= $product->get_permalink() ?>"
                                                   class="feature-cart"><i
                                                            class="icon icon-FullShoppingCart"></i>
                                                </a>
                                            </div>
                                        </div>
                                        <?php
                                    } ?>
                                </div>
                                <?php
                            }
                            ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Featured Area End -->
    <!-- Testimonial Area Start -->
    <div class="testimonial-area pt-110 pb-95">
        <div class="container">
            <div class="testimonial-slider-wrapper">
                <div class="text-carousel text-center">
                    <?php
                    query_posts([
                        'posts_per_page' => -1,
                        'post_type' => 'testimonial'
                    ]);
                    if (have_posts()) {
                        while (have_posts()) {
                            the_post();
                            ?>
                            <div class="slider-text">
                                <span class="testi-quote">
                                    <img src="<?= ASSET ?>/img/icon/quote.png" alt="">
                                </span>
                                <p><?= get_the_content() ?></p>
                            </div>
                        <?php }
                    } ?>
                </div>
                <div class="image-carousel">
                    <?php
                    query_posts([
                        'posts_per_page' => -1,
                        'post_type' => 'testimonial'
                    ]);
                    if (have_posts()) {
                        while (have_posts()) {
                            the_post();
                            ?>
                            <div class="testi-img">
                                <img src="<?= get_the_post_thumbnail_url(get_the_ID(), [94, 94]) ?>" alt="">
                                <h4><?= get_the_title() ?></h4>
                            </div>
                            <?php
                        }
                    }
                    ?>
                </div>
            </div>
        </div>
    </div>
    <!-- Testimonial Area End -->
    <!-- Blog Area Start -->
    <div class="blog-area pb-95">
        <div class="container">
            <div class="section-title text-center mb-50">
                <div class="section-img d-flex justify-content-center">
                    <h2><span>Blog </span>for you</h2>
                </div>
            </div>
        </div>
        <div class="container">
            <div class="custom-row">
                <div class="blog-carousel">
                    <?php
                    query_posts([
                        'posts_per_page' => 12,
                        'post_type' => 'post',
                    ]);
                    if (have_posts()) {
                        while (have_posts()) {
                            the_post();
                            ?>
                            <div class="custom-col text-center">
                                <div class="single-blog">
                                    <div class="blog-image">
                                        <a href="<?= get_the_permalink() ?>">
                                            <?= get_the_post_thumbnail(get_the_ID(), [370, 267]) ?>
                                        </a>
                                    </div>
                                    <div class="blog-text">
                                        <h4>
                                            <a href="<?= get_the_permalink() ?>">
                                                <?= get_the_title() ?>
                                            </a>
                                        </h4>
                                        <div class="post-meta">
                                            <span class="post-date"><?= get_the_date() ?></span>
                                        </div>
                                        <p><?= get_the_excerpt() ?></p>
                                        <a href="<?= get_the_permalink() ?>" class="default-btn">Read more</a>
                                    </div>
                                </div>
                            </div>
                            <?php
                        }
                    }
                    ?>
                </div>
            </div>
        </div>
    </div>

<?php
get_footer();
