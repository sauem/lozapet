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
                                                        <?= $product->get_image([268,268])?>
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
                <div class="col-lg-6 d-flex align-items-center">
                    <div class="banner-text pt-15">
                        <h3>Cold Process <span>Organic</span></h3>
                        <h1>Savon Stories</h1>
                        <h2>
                            <img src="<?= ASSET ?>/img/icon/mark.png" alt="">
                            <span>Buy 1 get 1 free</span>
                        </h2>
                        <p>Typi non habent claritatem insitam, est usus legentis in iis qui facit eorum claritatem.
                            Investigationes demonstraverunt lectores legere me lius quod ii legunt saepius.</p>
                        <a href="shop.html" class="default-btn">shop now</a>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="banner-image-wrapper">
                        <div class="banner-image">
                            <img src="<?= ASSET ?>/img/banner/1.jpg" alt="">
                        </div>
                        <div class="banner-image-text">
                            <h4>organic savon stories</h4>
                            <p>We believe that healthy eating, clean air, and gentle character is the best start to
                                genuine wellbeing.</p>
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
                        <h3>Organic <span>new arrivals</span></h3>
                        <div class="feaured-carousel">
                            <div class="single-featured-carousel">
                                <div class="single-featured-item">
                                    <div class="feature-image">
                                        <a href="shop.html"><img src="<?= ASSET ?>/img/featured/4.jpg" alt=""></a>
                                    </div>
                                    <div class="product-text">
                                        <div class="product-rating">
                                            <i class="fa fa-star-o color"></i>
                                            <i class="fa fa-star-o color"></i>
                                            <i class="fa fa-star-o"></i>
                                            <i class="fa fa-star-o"></i>
                                            <i class="fa fa-star-o"></i>
                                        </div>
                                        <h5><a href="shop.html">Radiant Tee</a></h5>
                                        <div class="pro-price">
                                            <span class="new-price">$241.99</span>
                                        </div>
                                        <a href="#" class="feature-cart"><i class="icon icon-FullShoppingCart"></i></a>
                                    </div>
                                </div>
                                <div class="single-featured-item">
                                    <div class="feature-image">
                                        <a href="shop.html"><img src="<?= ASSET ?>/img/featured/17.jpg" alt=""></a>
                                    </div>
                                    <div class="product-text">
                                        <div class="product-rating">
                                            <i class="fa fa-star-o color"></i>
                                            <i class="fa fa-star-o color"></i>
                                            <i class="fa fa-star-o color"></i>
                                            <i class="fa fa-star-o color"></i>
                                            <i class="fa fa-star-o"></i>
                                        </div>
                                        <h5><a href="shop.html">Farm Fresh Beet</a></h5>
                                        <div class="pro-price">
                                            <span class="new-price">$98.99</span>
                                        </div>
                                        <a href="#" class="feature-cart"><i class="icon icon-FullShoppingCart"></i></a>
                                    </div>
                                </div>
                                <div class="single-featured-item">
                                    <div class="feature-image">
                                        <a href="shop.html"><img src="<?= ASSET ?>/img/featured/7.jpg" alt=""></a>
                                    </div>
                                    <div class="product-text">
                                        <div class="product-rating">
                                            <i class="fa fa-star-o color"></i>
                                            <i class="fa fa-star-o color"></i>
                                            <i class="fa fa-star-o color"></i>
                                            <i class="fa fa-star-o color"></i>
                                            <i class="fa fa-star-o"></i>
                                        </div>
                                        <h5><a href="shop.html">Orange Juice</a></h5>
                                        <div class="pro-price">
                                            <span class="new-price">$68.00</span>
                                            <span class="old-price">$74.00</span>
                                        </div>
                                        <a href="#" class="feature-cart"><i class="icon icon-FullShoppingCart"></i></a>
                                    </div>
                                </div>
                                <div class="single-featured-item">
                                    <div class="feature-image">
                                        <a href="shop.html"><img src="<?= ASSET ?>/img/featured/15.jpg" alt=""></a>
                                    </div>
                                    <div class="product-text">
                                        <div class="product-rating">
                                            <i class="fa fa-star-o color"></i>
                                            <i class="fa fa-star-o color"></i>
                                            <i class="fa fa-star-o color"></i>
                                            <i class="fa fa-star-o color"></i>
                                            <i class="fa fa-star-o"></i>
                                        </div>
                                        <h5><a href="shop.html">Fresh Orange</a></h5>
                                        <div class="pro-price">
                                            <span class="new-price">$123.00</span>
                                            <span class="old-price">$74.00</span>
                                        </div>
                                        <a href="#" class="feature-cart"><i class="icon icon-FullShoppingCart"></i></a>
                                    </div>
                                </div>
                            </div>
                            <div class="single-featured-carousel">
                                <div class="single-featured-item">
                                    <div class="feature-image">
                                        <a href="shop.html"><img src="<?= ASSET ?>/img/featured/1.jpg" alt=""></a>
                                    </div>
                                    <div class="product-text">
                                        <div class="product-rating">
                                            <i class="fa fa-star-o color"></i>
                                            <i class="fa fa-star-o color"></i>
                                            <i class="fa fa-star-o"></i>
                                            <i class="fa fa-star-o"></i>
                                            <i class="fa fa-star-o"></i>
                                        </div>
                                        <h5><a href="shop.html">Juicy Grape</a></h5>
                                        <div class="pro-price">
                                            <span class="new-price">$675.99</span>
                                        </div>
                                        <a href="#" class="feature-cart"><i class="icon icon-FullShoppingCart"></i></a>
                                    </div>
                                </div>
                                <div class="single-featured-item">
                                    <div class="feature-image">
                                        <a href="shop.html"><img src="<?= ASSET ?>/img/featured/2.jpg" alt=""></a>
                                    </div>
                                    <div class="product-text">
                                        <div class="product-rating">
                                            <i class="fa fa-star-o color"></i>
                                            <i class="fa fa-star-o color"></i>
                                            <i class="fa fa-star-o color"></i>
                                            <i class="fa fa-star-o color"></i>
                                            <i class="fa fa-star-o"></i>
                                        </div>
                                        <h5><a href="shop.html">Fresh Banana</a></h5>
                                        <div class="pro-price">
                                            <span class="new-price">$76.53</span>
                                            <span class="old-price">$78.00</span>
                                        </div>
                                        <a href="#" class="feature-cart"><i class="icon icon-FullShoppingCart"></i></a>
                                    </div>
                                </div>
                                <div class="single-featured-item">
                                    <div class="feature-image">
                                        <a href="shop.html"><img src="<?= ASSET ?>/img/featured/3.jpg" alt=""></a>
                                    </div>
                                    <div class="product-text">
                                        <div class="product-rating">
                                            <i class="fa fa-star-o color"></i>
                                            <i class="fa fa-star-o color"></i>
                                            <i class="fa fa-star-o color"></i>
                                            <i class="fa fa-star-o color"></i>
                                            <i class="fa fa-star-o"></i>
                                        </div>
                                        <h5><a href="shop.html">Red Capsicum</a></h5>
                                        <div class="pro-price">
                                            <span class="new-price">$43.00</span>
                                        </div>
                                        <a href="#" class="feature-cart"><i class="icon icon-FullShoppingCart"></i></a>
                                    </div>
                                </div>
                                <div class="single-featured-item">
                                    <div class="feature-image">
                                        <a href="shop.html"><img src="<?= ASSET ?>/img/featured/5.jpg" alt=""></a>
                                    </div>
                                    <div class="product-text">
                                        <div class="product-rating">
                                            <i class="fa fa-star-o color"></i>
                                            <i class="fa fa-star-o color"></i>
                                            <i class="fa fa-star-o color"></i>
                                            <i class="fa fa-star-o color"></i>
                                            <i class="fa fa-star-o"></i>
                                        </div>
                                        <h5><a href="shop.html">Large Onion</a></h5>
                                        <div class="pro-price">
                                            <span class="new-price">$22.00</span>
                                            <span class="old-price">$23.00</span>
                                        </div>
                                        <a href="#" class="feature-cart"><i class="icon icon-FullShoppingCart"></i></a>
                                    </div>
                                </div>
                            </div>
                            <div class="single-featured-carousel">
                                <div class="single-featured-item">
                                    <div class="feature-image">
                                        <a href="shop.html"><img src="<?= ASSET ?>/img/featured/6.jpg" alt=""></a>
                                    </div>
                                    <div class="product-text">
                                        <div class="product-rating">
                                            <i class="fa fa-star-o color"></i>
                                            <i class="fa fa-star-o color"></i>
                                            <i class="fa fa-star-o"></i>
                                            <i class="fa fa-star-o"></i>
                                            <i class="fa fa-star-o"></i>
                                        </div>
                                        <h5><a href="shop.html">Large Coconut</a></h5>
                                        <div class="pro-price">
                                            <span class="new-price">$54.56</span>
                                        </div>
                                        <a href="#" class="feature-cart"><i class="icon icon-FullShoppingCart"></i></a>
                                    </div>
                                </div>
                                <div class="single-featured-item">
                                    <div class="feature-image">
                                        <a href="shop.html"><img src="<?= ASSET ?>/img/featured/7.jpg" alt=""></a>
                                    </div>
                                    <div class="product-text">
                                        <div class="product-rating">
                                            <i class="fa fa-star-o color"></i>
                                            <i class="fa fa-star-o color"></i>
                                            <i class="fa fa-star-o color"></i>
                                            <i class="fa fa-star-o color"></i>
                                            <i class="fa fa-star-o"></i>
                                        </div>
                                        <h5><a href="shop.html">Orange Juice</a></h5>
                                        <div class="pro-price">
                                            <span class="new-price">$43.43</span>
                                        </div>
                                        <a href="#" class="feature-cart"><i class="icon icon-FullShoppingCart"></i></a>
                                    </div>
                                </div>
                                <div class="single-featured-item">
                                    <div class="feature-image">
                                        <a href="shop.html"><img src="<?= ASSET ?>/img/featured/8.jpg" alt=""></a>
                                    </div>
                                    <div class="product-text">
                                        <div class="product-rating">
                                            <i class="fa fa-star-o color"></i>
                                            <i class="fa fa-star-o color"></i>
                                            <i class="fa fa-star-o color"></i>
                                            <i class="fa fa-star-o color"></i>
                                            <i class="fa fa-star-o"></i>
                                        </div>
                                        <h5><a href="shop.html">Juicy Pineapple</a></h5>
                                        <div class="pro-price">
                                            <span class="new-price">$43.00</span>
                                        </div>
                                        <a href="#" class="feature-cart"><i class="icon icon-FullShoppingCart"></i></a>
                                    </div>
                                </div>
                                <div class="single-featured-item">
                                    <div class="feature-image">
                                        <a href="shop.html"><img src="<?= ASSET ?>/img/featured/9.jpg" alt=""></a>
                                    </div>
                                    <div class="product-text">
                                        <div class="product-rating">
                                            <i class="fa fa-star-o color"></i>
                                            <i class="fa fa-star-o color"></i>
                                            <i class="fa fa-star-o color"></i>
                                            <i class="fa fa-star-o color"></i>
                                            <i class="fa fa-star-o"></i>
                                        </div>
                                        <h5><a href="shop.html">Fresh Cucumber</a></h5>
                                        <div class="pro-price">
                                            <span class="new-price">$32.00</span>
                                            <span class="old-price">$36.00</span>
                                        </div>
                                        <a href="#" class="feature-cart"><i class="icon icon-FullShoppingCart"></i></a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6">
                    <div class="featured-carousel-wrapper">
                        <h3>Organic <span>bestseller</span></h3>
                        <div class="feaured-carousel">
                            <div class="single-featured-carousel">
                                <div class="single-featured-item">
                                    <div class="feature-image">
                                        <a href="shop.html"><img src="<?= ASSET ?>/img/featured/10.jpg" alt=""></a>
                                    </div>
                                    <div class="product-text">
                                        <div class="product-rating">
                                            <i class="fa fa-star-o color"></i>
                                            <i class="fa fa-star-o color"></i>
                                            <i class="fa fa-star-o color"></i>
                                            <i class="fa fa-star-o color"></i>
                                            <i class="fa fa-star-o color"></i>
                                        </div>
                                        <h5><a href="shop.html">Juicy Grapes</a></h5>
                                        <div class="pro-price">
                                            <span class="new-price">$64.00</span>
                                        </div>
                                        <a href="#" class="feature-cart"><i class="icon icon-FullShoppingCart"></i></a>
                                    </div>
                                </div>
                                <div class="single-featured-item">
                                    <div class="feature-image">
                                        <a href="shop.html"><img src="<?= ASSET ?>/img/featured/12.jpg" alt=""></a>
                                    </div>
                                    <div class="product-text">
                                        <div class="product-rating">
                                            <i class="fa fa-star-o color"></i>
                                            <i class="fa fa-star-o"></i>
                                            <i class="fa fa-star-o"></i>
                                            <i class="fa fa-star-o"></i>
                                            <i class="fa fa-star-o"></i>
                                        </div>
                                        <h5><a href="shop.html">Farm Fresh Juice</a></h5>
                                        <div class="pro-price">
                                            <span class="new-price">$54.54</span>
                                        </div>
                                        <a href="#" class="feature-cart"><i class="icon icon-FullShoppingCart"></i></a>
                                    </div>
                                </div>
                                <div class="single-featured-item">
                                    <div class="feature-image">
                                        <a href="shop.html"><img src="<?= ASSET ?>/img/featured/13.jpg" alt=""></a>
                                    </div>
                                    <div class="product-text">
                                        <div class="product-rating">
                                            <i class="fa fa-star-o color"></i>
                                            <i class="fa fa-star-o color"></i>
                                            <i class="fa fa-star-o color"></i>
                                            <i class="fa fa-star-o color"></i>
                                            <i class="fa fa-star-o"></i>
                                        </div>
                                        <h5><a href="shop.html">Fresh Peas</a></h5>
                                        <div class="pro-price">
                                            <span class="new-price">$45.00</span>
                                            <span class="old-price">$46.00</span>
                                        </div>
                                        <a href="#" class="feature-cart"><i class="icon icon-FullShoppingCart"></i></a>
                                    </div>
                                </div>
                                <div class="single-featured-item">
                                    <div class="feature-image">
                                        <a href="shop.html"><img src="<?= ASSET ?>/img/featured/14.jpg" alt=""></a>
                                    </div>
                                    <div class="product-text">
                                        <div class="product-rating">
                                            <i class="fa fa-star-o color"></i>
                                            <i class="fa fa-star-o color"></i>
                                            <i class="fa fa-star-o"></i>
                                            <i class="fa fa-star-o"></i>
                                            <i class="fa fa-star-o"></i>
                                        </div>
                                        <h5><a href="shop.html">Fresh Onion</a></h5>
                                        <div class="pro-price">
                                            <span class="new-price">$34.00</span>
                                            <span class="old-price">$35.00</span>
                                        </div>
                                        <a href="#" class="feature-cart"><i class="icon icon-FullShoppingCart"></i></a>
                                    </div>
                                </div>
                            </div>
                            <div class="single-featured-carousel">
                                <div class="single-featured-item">
                                    <div class="feature-image">
                                        <a href="shop.html"><img src="<?= ASSET ?>/img/featured/16.jpg" alt=""></a>
                                    </div>
                                    <div class="product-text">
                                        <div class="product-rating">
                                            <i class="fa fa-star-o color"></i>
                                            <i class="fa fa-star-o color"></i>
                                            <i class="fa fa-star-o color"></i>
                                            <i class="fa fa-star-o color"></i>
                                            <i class="fa fa-star-o"></i>
                                        </div>
                                        <h5><a href="shop.html">Kewe's Juice</a></h5>
                                        <div class="pro-price">
                                            <span class="new-price">$53.43</span>
                                        </div>
                                        <a href="#" class="feature-cart"><i class="icon icon-FullShoppingCart"></i></a>
                                    </div>
                                </div>
                                <div class="single-featured-item">
                                    <div class="feature-image">
                                        <a href="shop.html"><img src="<?= ASSET ?>/img/featured/18.jpg" alt=""></a>
                                    </div>
                                    <div class="product-text">
                                        <div class="product-rating">
                                            <i class="fa fa-star-o color"></i>
                                            <i class="fa fa-star-o color"></i>
                                            <i class="fa fa-star-o color"></i>
                                            <i class="fa fa-star-o"></i>
                                            <i class="fa fa-star-o"></i>
                                        </div>
                                        <h5><a href="shop.html">Fresh Vegetables</a></h5>
                                        <div class="pro-price">
                                            <span class="new-price">$43.00</span>
                                            <span class="old-price">$58.00</span>
                                        </div>
                                        <a href="#" class="feature-cart"><i class="icon icon-FullShoppingCart"></i></a>
                                    </div>
                                </div>
                                <div class="single-featured-item">
                                    <div class="feature-image">
                                        <a href="shop.html"><img src="<?= ASSET ?>/img/featured/19.jpg" alt=""></a>
                                    </div>
                                    <div class="product-text">
                                        <div class="product-rating">
                                            <i class="fa fa-star-o color"></i>
                                            <i class="fa fa-star-o color"></i>
                                            <i class="fa fa-star-o color"></i>
                                            <i class="fa fa-star-o color"></i>
                                            <i class="fa fa-star-o color"></i>
                                        </div>
                                        <h5><a href="shop.html">Red Tomato</a></h5>
                                        <div class="pro-price">
                                            <span class="new-price">$67.00</span>
                                        </div>
                                        <a href="#" class="feature-cart"><i class="icon icon-FullShoppingCart"></i></a>
                                    </div>
                                </div>
                                <div class="single-featured-item">
                                    <div class="feature-image">
                                        <a href="shop.html"><img src="<?= ASSET ?>/img/featured/1.jpg" alt=""></a>
                                    </div>
                                    <div class="product-text">
                                        <div class="product-rating">
                                            <i class="fa fa-star-o color"></i>
                                            <i class="fa fa-star-o color"></i>
                                            <i class="fa fa-star-o color"></i>
                                            <i class="fa fa-star-o color"></i>
                                            <i class="fa fa-star-o"></i>
                                        </div>
                                        <h5><a href="shop.html">Large Grapes</a></h5>
                                        <div class="pro-price">
                                            <span class="new-price">$55.00</span>
                                            <span class="old-price">$57.00</span>
                                        </div>
                                        <a href="#" class="feature-cart"><i class="icon icon-FullShoppingCart"></i></a>
                                    </div>
                                </div>
                            </div>
                            <div class="single-featured-carousel">
                                <div class="single-featured-item">
                                    <div class="feature-image">
                                        <a href="shop.html"><img src="<?= ASSET ?>/img/featured/2.jpg" alt=""></a>
                                    </div>
                                    <div class="product-text">
                                        <div class="product-rating">
                                            <i class="fa fa-star-o color"></i>
                                            <i class="fa fa-star-o color"></i>
                                            <i class="fa fa-star-o color"></i>
                                            <i class="fa fa-star-o color"></i>
                                            <i class="fa fa-star-o color"></i>
                                        </div>
                                        <h5><a href="shop.html">Fresh Banana</a></h5>
                                        <div class="pro-price">
                                            <span class="new-price">$65.00</span>
                                        </div>
                                        <a href="#" class="feature-cart"><i class="icon icon-FullShoppingCart"></i></a>
                                    </div>
                                </div>
                                <div class="single-featured-item">
                                    <div class="feature-image">
                                        <a href="shop.html"><img src="<?= ASSET ?>/img/featured/3.jpg" alt=""></a>
                                    </div>
                                    <div class="product-text">
                                        <div class="product-rating">
                                            <i class="fa fa-star-o color"></i>
                                            <i class="fa fa-star-o color"></i>
                                            <i class="fa fa-star-o color"></i>
                                            <i class="fa fa-star-o color"></i>
                                            <i class="fa fa-star-o"></i>
                                        </div>
                                        <h5><a href="shop.html">Red Capsicum</a></h5>
                                        <div class="pro-price">
                                            <span class="new-price">$54.00</span>
                                        </div>
                                        <a href="#" class="feature-cart"><i class="icon icon-FullShoppingCart"></i></a>
                                    </div>
                                </div>
                                <div class="single-featured-item">
                                    <div class="feature-image">
                                        <a href="shop.html"><img src="<?= ASSET ?>/img/featured/5.jpg" alt=""></a>
                                    </div>
                                    <div class="product-text">
                                        <div class="product-rating">
                                            <i class="fa fa-star-o color"></i>
                                            <i class="fa fa-star-o color"></i>
                                            <i class="fa fa-star-o color"></i>
                                            <i class="fa fa-star-o"></i>
                                            <i class="fa fa-star-o"></i>
                                        </div>
                                        <h5><a href="shop.html">Farm's Onion</a></h5>
                                        <div class="pro-price">
                                            <span class="new-price">$54.00</span>
                                        </div>
                                        <a href="#" class="feature-cart"><i class="icon icon-FullShoppingCart"></i></a>
                                    </div>
                                </div>
                                <div class="single-featured-item">
                                    <div class="feature-image">
                                        <a href="shop.html"><img src="<?= ASSET ?>/img/featured/9.jpg" alt=""></a>
                                    </div>
                                    <div class="product-text">
                                        <div class="product-rating">
                                            <i class="fa fa-star-o color"></i>
                                            <i class="fa fa-star-o color"></i>
                                            <i class="fa fa-star-o color"></i>
                                            <i class="fa fa-star-o color"></i>
                                            <i class="fa fa-star-o"></i>
                                        </div>
                                        <h5><a href="shop.html">Fresh Cucumber</a></h5>
                                        <div class="pro-price">
                                            <span class="new-price">$32.00</span>
                                            <span class="old-price">$36.00</span>
                                        </div>
                                        <a href="#" class="feature-cart"><i class="icon icon-FullShoppingCart"></i></a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6">
                    <div class="featured-carousel-wrapper">
                        <h3>Organic <span>most viewed</span></h3>
                        <div class="feaured-carousel">
                            <div class="single-featured-carousel">
                                <div class="single-featured-item">
                                    <div class="feature-image">
                                        <a href="shop.html"><img src="<?= ASSET ?>/img/featured/5.jpg" alt=""></a>
                                    </div>
                                    <div class="product-text">
                                        <div class="product-rating">
                                            <i class="fa fa-star-o color"></i>
                                            <i class="fa fa-star-o color"></i>
                                            <i class="fa fa-star-o color"></i>
                                            <i class="fa fa-star-o"></i>
                                            <i class="fa fa-star-o"></i>
                                        </div>
                                        <h5><a href="shop.html">Farm's Onion</a></h5>
                                        <div class="pro-price">
                                            <span class="new-price">$54.00</span>
                                        </div>
                                        <a href="#" class="feature-cart"><i class="icon icon-FullShoppingCart"></i></a>
                                    </div>
                                </div>
                                <div class="single-featured-item">
                                    <div class="feature-image">
                                        <a href="shop.html"><img src="<?= ASSET ?>/img/featured/9.jpg" alt=""></a>
                                    </div>
                                    <div class="product-text">
                                        <div class="product-rating">
                                            <i class="fa fa-star-o color"></i>
                                            <i class="fa fa-star-o color"></i>
                                            <i class="fa fa-star-o color"></i>
                                            <i class="fa fa-star-o color"></i>
                                            <i class="fa fa-star-o"></i>
                                        </div>
                                        <h5><a href="shop.html">Fresh Cucumber</a></h5>
                                        <div class="pro-price">
                                            <span class="new-price">$32.00</span>
                                            <span class="old-price">$36.00</span>
                                        </div>
                                        <a href="#" class="feature-cart"><i class="icon icon-FullShoppingCart"></i></a>
                                    </div>
                                </div>
                                <div class="single-featured-item">
                                    <div class="feature-image">
                                        <a href="shop.html"><img src="<?= ASSET ?>/img/featured/6.jpg" alt=""></a>
                                    </div>
                                    <div class="product-text">
                                        <div class="product-rating">
                                            <i class="fa fa-star-o color"></i>
                                            <i class="fa fa-star-o color"></i>
                                            <i class="fa fa-star-o color"></i>
                                            <i class="fa fa-star-o color"></i>
                                            <i class="fa fa-star-o"></i>
                                        </div>
                                        <h5><a href="shop.html">Medium Coconut</a></h5>
                                        <div class="pro-price">
                                            <span class="new-price">$54.00</span>
                                        </div>
                                        <a href="#" class="feature-cart"><i class="icon icon-FullShoppingCart"></i></a>
                                    </div>
                                </div>
                                <div class="single-featured-item">
                                    <div class="feature-image">
                                        <a href="shop.html"><img src="<?= ASSET ?>/img/featured/14.jpg" alt=""></a>
                                    </div>
                                    <div class="product-text">
                                        <div class="product-rating">
                                            <i class="fa fa-star-o color"></i>
                                            <i class="fa fa-star-o color"></i>
                                            <i class="fa fa-star-o"></i>
                                            <i class="fa fa-star-o"></i>
                                            <i class="fa fa-star-o"></i>
                                        </div>
                                        <h5><a href="shop.html">Fresh Onion</a></h5>
                                        <div class="pro-price">
                                            <span class="new-price">$34.00</span>
                                            <span class="old-price">$35.00</span>
                                        </div>
                                        <a href="#" class="feature-cart"><i class="icon icon-FullShoppingCart"></i></a>
                                    </div>
                                </div>
                            </div>
                            <div class="single-featured-carousel">
                                <div class="single-featured-item">
                                    <div class="feature-image">
                                        <a href="shop.html"><img src="<?= ASSET ?>/img/featured/12.jpg" alt=""></a>
                                    </div>
                                    <div class="product-text">
                                        <div class="product-rating">
                                            <i class="fa fa-star-o color"></i>
                                            <i class="fa fa-star-o color"></i>
                                            <i class="fa fa-star-o color"></i>
                                            <i class="fa fa-star-o color"></i>
                                            <i class="fa fa-star-o"></i>
                                        </div>
                                        <h5><a href="shop.html">Strawberry Juice</a></h5>
                                        <div class="pro-price">
                                            <span class="new-price">$53.43</span>
                                        </div>
                                        <a href="#" class="feature-cart"><i class="icon icon-FullShoppingCart"></i></a>
                                    </div>
                                </div>
                                <div class="single-featured-item">
                                    <div class="feature-image">
                                        <a href="shop.html"><img src="<?= ASSET ?>/img/featured/1.jpg" alt=""></a>
                                    </div>
                                    <div class="product-text">
                                        <div class="product-rating">
                                            <i class="fa fa-star-o color"></i>
                                            <i class="fa fa-star-o color"></i>
                                            <i class="fa fa-star-o color"></i>
                                            <i class="fa fa-star-o color"></i>
                                            <i class="fa fa-star-o"></i>
                                        </div>
                                        <h5><a href="shop.html">Large Grapes</a></h5>
                                        <div class="pro-price">
                                            <span class="new-price">$55.00</span>
                                            <span class="old-price">$57.00</span>
                                        </div>
                                        <a href="#" class="feature-cart"><i class="icon icon-FullShoppingCart"></i></a>
                                    </div>
                                </div>
                                <div class="single-featured-item">
                                    <div class="feature-image">
                                        <a href="shop.html"><img src="<?= ASSET ?>/img/featured/2.jpg" alt=""></a>
                                    </div>
                                    <div class="product-text">
                                        <div class="product-rating">
                                            <i class="fa fa-star-o color"></i>
                                            <i class="fa fa-star-o color"></i>
                                            <i class="fa fa-star-o color"></i>
                                            <i class="fa fa-star-o color"></i>
                                            <i class="fa fa-star-o color"></i>
                                        </div>
                                        <h5><a href="shop.html">Fresh Banana</a></h5>
                                        <div class="pro-price">
                                            <span class="new-price">$65.00</span>
                                        </div>
                                        <a href="#" class="feature-cart"><i class="icon icon-FullShoppingCart"></i></a>
                                    </div>
                                </div>
                                <div class="single-featured-item">
                                    <div class="feature-image">
                                        <a href="shop.html"><img src="<?= ASSET ?>/img/featured/3.jpg" alt=""></a>
                                    </div>
                                    <div class="product-text">
                                        <div class="product-rating">
                                            <i class="fa fa-star-o color"></i>
                                            <i class="fa fa-star-o color"></i>
                                            <i class="fa fa-star-o color"></i>
                                            <i class="fa fa-star-o color"></i>
                                            <i class="fa fa-star-o"></i>
                                        </div>
                                        <h5><a href="shop.html">Red Capsicum</a></h5>
                                        <div class="pro-price">
                                            <span class="new-price">$54.00</span>
                                        </div>
                                        <a href="#" class="feature-cart"><i class="icon icon-FullShoppingCart"></i></a>
                                    </div>
                                </div>
                            </div>
                            <div class="single-featured-carousel">
                                <div class="single-featured-item">
                                    <div class="feature-image">
                                        <a href="shop.html"><img src="<?= ASSET ?>/img/featured/18.jpg" alt=""></a>
                                    </div>
                                    <div class="product-text">
                                        <div class="product-rating">
                                            <i class="fa fa-star-o color"></i>
                                            <i class="fa fa-star-o color"></i>
                                            <i class="fa fa-star-o color"></i>
                                            <i class="fa fa-star-o"></i>
                                            <i class="fa fa-star-o"></i>
                                        </div>
                                        <h5><a href="shop.html">Fresh Vegetables</a></h5>
                                        <div class="pro-price">
                                            <span class="new-price">$43.00</span>
                                            <span class="old-price">$58.00</span>
                                        </div>
                                        <a href="#" class="feature-cart"><i class="icon icon-FullShoppingCart"></i></a>
                                    </div>
                                </div>
                                <div class="single-featured-item">
                                    <div class="feature-image">
                                        <a href="shop.html"><img src="<?= ASSET ?>/img/featured/19.jpg" alt=""></a>
                                    </div>
                                    <div class="product-text">
                                        <div class="product-rating">
                                            <i class="fa fa-star-o color"></i>
                                            <i class="fa fa-star-o color"></i>
                                            <i class="fa fa-star-o color"></i>
                                            <i class="fa fa-star-o color"></i>
                                            <i class="fa fa-star-o color"></i>
                                        </div>
                                        <h5><a href="shop.html">Red Tomato</a></h5>
                                        <div class="pro-price">
                                            <span class="new-price">$67.00</span>
                                        </div>
                                        <a href="#" class="feature-cart"><i class="icon icon-FullShoppingCart"></i></a>
                                    </div>
                                </div>
                                <div class="single-featured-item">
                                    <div class="feature-image">
                                        <a href="shop.html"><img src="<?= ASSET ?>/img/featured/10.jpg" alt=""></a>
                                    </div>
                                    <div class="product-text">
                                        <div class="product-rating">
                                            <i class="fa fa-star-o color"></i>
                                            <i class="fa fa-star-o color"></i>
                                            <i class="fa fa-star-o color"></i>
                                            <i class="fa fa-star-o color"></i>
                                            <i class="fa fa-star-o color"></i>
                                        </div>
                                        <h5><a href="shop.html">Juicy Grapes</a></h5>
                                        <div class="pro-price">
                                            <span class="new-price">$64.00</span>
                                        </div>
                                        <a href="#" class="feature-cart"><i class="icon icon-FullShoppingCart"></i></a>
                                    </div>
                                </div>
                                <div class="single-featured-item">
                                    <div class="feature-image">
                                        <a href="shop.html"><img src="<?= ASSET ?>/img/featured/12.jpg" alt=""></a>
                                    </div>
                                    <div class="product-text">
                                        <div class="product-rating">
                                            <i class="fa fa-star-o color"></i>
                                            <i class="fa fa-star-o"></i>
                                            <i class="fa fa-star-o"></i>
                                            <i class="fa fa-star-o"></i>
                                            <i class="fa fa-star-o"></i>
                                        </div>
                                        <h5><a href="shop.html">Farm Fresh Juice</a></h5>
                                        <div class="pro-price">
                                            <span class="new-price">$54.54</span>
                                        </div>
                                        <a href="#" class="feature-cart"><i class="icon icon-FullShoppingCart"></i></a>
                                    </div>
                                </div>
                            </div>
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
                    <div class="slider-text">
                            <span class="testi-quote">
                                <img src="<?= ASSET ?>/img/icon/quote.png" alt="">
                            </span>
                        <p>This is Photoshops version of Lorem Ipsum. Proin gravida nibh vel velit.Lorem ipsum dolor sit
                            amet, consectetur adipiscing elit. In molestie augue magna. Pellentesque felis lorem,
                            pulvinar sed ero..</p>
                    </div>
                    <div class="slider-text">
                            <span class="testi-quote">
                                <img src="<?= ASSET ?>/img/icon/quote.png" alt="">
                            </span>
                        <p>Lorem ipsum dolor sit amet, consectetur adipisicing elit. Reprehenderit tenetur rerum maiores
                            eos fugit dolores neque eius ex eum quo, quis aspernatur odio accusantium architecto, amet
                            repellat.</p>
                    </div>
                    <div class="slider-text">
                            <span class="testi-quote">
                                <img src="<?= ASSET ?>/img/icon/quote.png" alt="">
                            </span>
                        <p>Reprehenderit tenetur rerum maiores eos fugit dolores neque eius ex eum quo, quis aspernatur
                            odio accusantium architecto, amet repellat Lorem ipsum dolor sit amet, consectetur
                            adipisicing elit.</p>
                    </div>
                    <div class="slider-text">
                            <span class="testi-quote">
                                <img src="<?= ASSET ?>/img/icon/quote.png" alt="">
                            </span>
                        <p>This is Photoshops version of Lorem Ipsum. Proin gravida nibh vel velit.Lorem ipsum dolor sit
                            amet, consectetur adipiscing elit. In molestie augue magna. Pellentesque felis lorem,
                            pulvinar sed ero..</p>
                    </div>
                    <div class="slider-text">
                            <span class="testi-quote">
                                <img src="<?= ASSET ?>/img/icon/quote.png" alt="">
                            </span>
                        <p>Lorem ipsum dolor sit amet, consectetur adipisicing elit. Reprehenderit tenetur rerum maiores
                            eos fugit dolores neque eius ex eum quo, quis aspernatur odio accusantium architecto, amet
                            repellat.</p>
                    </div>
                    <div class="slider-text">
                            <span class="testi-quote">
                                <img src="<?= ASSET ?>/img/icon/quote.png" alt="">
                            </span>
                        <p>Reprehenderit tenetur rerum maiores eos fugit dolores neque eius ex eum quo, quis aspernatur
                            odio accusantium architecto, amet repellat Lorem ipsum dolor sit amet, consectetur
                            adipisicing elit.</p>
                    </div>
                </div>
                <div class="image-carousel">
                    <div class="testi-img">
                        <img src="<?= ASSET ?>/img/testimonial/1.png" alt="">
                        <h4>Dewey Tetzlaff</h4>
                    </div>
                    <div class="testi-img">
                        <img src="<?= ASSET ?>/img/testimonial/2.png" alt="">
                        <h4>Rebecka Filson</h4>
                    </div>
                    <div class="testi-img">
                        <img src="<?= ASSET ?>/img/testimonial/3.png" alt="">
                        <h4>Alva Ono</h4>
                    </div>
                    <div class="testi-img">
                        <img src="<?= ASSET ?>/img/testimonial/1.png" alt="">
                        <h4>Dewey Tetzlaff</h4>
                    </div>
                    <div class="testi-img">
                        <img src="<?= ASSET ?>/img/testimonial/2.png" alt="">
                        <h4>Rebecka Filson</h4>
                    </div>
                    <div class="testi-img">
                        <img src="<?= ASSET ?>/img/testimonial/3.png" alt="">
                        <h4>Alva Ono</h4>
                    </div>
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
                    <img src="<?= ASSET ?>/img/icon/title.png" alt="">
                </div>
                <h2><span>Organic </span>from our blog</h2>
            </div>
        </div>
        <div class="container">
            <div class="custom-row">
                <div class="blog-carousel">
                    <div class="custom-col text-center">
                        <div class="single-blog">
                            <div class="blog-image">
                                <a href="blog-details.html"><img src="<?= ASSET ?>/img/blog/1.jpg" alt=""></a>
                            </div>
                            <div class="blog-text">
                                <h4><a href="blog-details.html">Coconut improve heart and immunity.</a></h4>
                                <div class="post-meta">
                                    <span class="author-name">post by: <span>Naturecircle Themes</span></span>
                                    <span class="post-date"> - Oct 30,2018</span>
                                </div>
                                <p>Coconut milk is one of the healthiest foods on world, health benefits of coconut milk
                                    make it quite popular.</p>
                                <a href="blog-details.html" class="default-btn">Read more</a>
                            </div>
                        </div>
                    </div>
                    <div class="custom-col text-center">
                        <div class="single-blog">
                            <div class="blog-image">
                                <a href="blog-details.html"><img src="<?= ASSET ?>/img/blog/2.jpg" alt=""></a>
                            </div>
                            <div class="blog-text">
                                <h4><a href="blog-details.html">The most healthful food you can eat.</a></h4>
                                <div class="post-meta">
                                    <span class="author-name">post by: <span>Naturecircle Themes</span></span>
                                    <span class="post-date"> - Sep 11,2018</span>
                                </div>
                                <p>Health benefits of apple include improved digestion, prevention of stomach disorders,
                                    gallstones.</p>
                                <a href="blog-details.html" class="default-btn">Read more</a>
                            </div>
                        </div>
                    </div>
                    <div class="custom-col text-center">
                        <div class="single-blog">
                            <div class="blog-image">
                                <a href="blog-details.html"><img src="<?= ASSET ?>/img/blog/3.jpg" alt=""></a>
                            </div>
                            <div class="blog-text">
                                <h4><a href="blog-details.html">Delicious and nutritious vegetable.</a></h4>
                                <div class="post-meta">
                                    <span class="author-name">post by: <span>Naturecircle Themes</span></span>
                                    <span class="post-date"> - Apr 22,2018</span>
                                </div>
                                <p>Research shows drinking beetroot juice benefit digestion, boost athletic performance.
                                    They are a good source.</p>
                                <a href="blog-details.html" class="default-btn">Read more</a>
                            </div>
                        </div>
                    </div>
                    <div class="custom-col text-center">
                        <div class="single-blog">
                            <div class="blog-image">
                                <a href="blog-details.html"><img src="<?= ASSET ?>/img/blog/1.jpg" alt=""></a>
                            </div>
                            <div class="blog-text">
                                <h4><a href="blog-details.html">Coconut improve heart and immunity.</a></h4>
                                <div class="post-meta">
                                    <span class="author-name">post by: <span>Naturecircle Themes</span></span>
                                    <span class="post-date"> - Oct 30,2018</span>
                                </div>
                                <p>Coconut milk is one of the healthiest foods on world, health benefits of coconut milk
                                    make it quite popular.</p>
                                <a href="blog-details.html" class="default-btn">Read more</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

<?php
get_footer();
