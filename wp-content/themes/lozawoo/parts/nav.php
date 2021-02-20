<?php
global $woocommerce;
$totalItems = $woocommerce->cart->cart_contents_count;
$items = WC()->cart->get_cart();

?>
<header class="header-area header-sticky">
    <div class="header-container">
        <div class="row">
            <div class="col-lg-5 display-none-md display-none-xs">
                <div class="ht-main-menu">
                    <nav>
                        <?= wp_nav_menu([
                            "menu" => "head",
                            'container' => 'nav',
                            'container_class' => '',
                            'container_id' => '',
                            'menu_class' => '',
                        ]); ?>
                    </nav>
                </div>
            </div>
            <div class="col-lg-2 col-sm-4">
                <div class="logo text-center">
                    <a href="<?= home_url()?>"><img src="<?= get_logo() ?>" alt="<?= get_bloginfo()?>"></a>
                </div>
            </div>
            <div class="col-lg-5 col-sm-8">
                <div class="header-content d-flex justify-content-end">
                    <div class="search-wrapper">
                        <a href="#"><span class="icon icon-Search"></span></a>
                        <form action="#" class="search-form">
                            <input type="text" placeholder="Search entire store here ...">
                            <button type="button">Search</button>
                        </form>
                    </div>
                    <div class="cart-wrapper">
                        <a href="#">
                            <i class="icon icon-FullShoppingCart"></i>
                            <span><?php echo $totalItems > 0 ? $totalItems : 0 ?></span>
                        </a>
                        <div class="cart-item-wrapper">

                            <?php
                            if (!empty($items)) {

                                foreach ($items

                                         as $cart_item_key => $cart_item) {
                                    $product = $cart_item['data'];
                                    $product_id = $cart_item['product_id'];
                                    $quantity = $cart_item['quantity'];
                                    $price = WC()->cart->get_product_price($product);
                                    $subtotal = WC()->cart->get_product_subtotal($product, $cart_item['quantity']);
                                    $link = $product->get_permalink($cart_item);
                                    // Anything related to $product, check $product tutorial
                                    $meta = wc_get_formatted_cart_item_data($cart_item);
                                    ?>

                                    <div class="single-cart-item ">
                                        <div class="cart-img">
                                            <a href="<?= wc_get_cart_url() ?>">
                                                <img src="<?= get_the_post_thumbnail_url($product->get_id()) ?>"
                                                     width="80" height="80" alt="">
                                            </a>
                                        </div>
                                        <div class="cart-text-btn">
                                            <div class="cart-text">
                                                <h5>
                                                    <a href="<?= wc_get_cart_url() ?>"><?= strlimit($product->get_title()) ?></a>
                                                </h5>
                                                <span class="cart-qty">×<?= $quantity ?></span>
                                                <span class="cart-price"><?= $subtotal ?></span>
                                            </div>
                                            <a
                                                    href="<?= wc_get_cart_remove_url($cart_item_key) ?>"
                                                    data-product_sku="<?= $product->get_sku(); ?>"
                                                    data-cart_item_key="<?= $cart_item_key ?>"
                                                    class="remove remove_from_cart_button"
                                                    data-product_id="<?= $product_id ?>" type="button">
                                                <i class="fa fa-close"></i></a>
                                        </div>
                                    </div>

                                    <?php
                                }
                                ?>

                                <div class="cart-price-total">
                                    <div class="cart-price-info d-flex justify-content-between">
                                        <span>Sub-Total :</span>
                                        <span>$<?= WC()->cart->subtotal > 0 ? WC()->cart->subtotal : 0; ?></span>
                                    </div>
                                    <div class="woocommerce-mini-cart__buttons buttons cart-price-info d-flex justify-content-between">
                                        <span>Total :</span>
                                        <span class="woocommerce-Price-amount amount">$<?= WC()->cart->total > 0 ? WC()->cart->total : 0; ?></span>
                                    </div>
                                </div>
                                <div class="cart-links">
                                    <a href="<?= wc_get_cart_url() ?>">View cart</a>
                                    <a href="<?= wc_get_checkout_url() ?>">Checkout</a>
                                </div>
                                <?php
                            } else {
                                ?>
                                Cart Empty
                                <?php
                            }
                            ?>

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Header Area End -->
    <!-- Mobile Menu Area Start -->
    <div class="mobile-menu-area">
        <div class="mobile-menu container">
            <nav id="mobile-menu-active">
                <?= wp_nav_menu([
                    "menu" => "head",
                    'container' => 'nav',
                    'container_class' => '',
                    'container_id' => '',
                    'menu_class' => 'menu-overflow',
                ]); ?>

            </nav>
        </div>
    </div>
    <!-- Mobile Menu Area End -->
    <!--Start of Login Form-->
    <div class="modal fade" id="login_box" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                                aria-hidden="true"><i class="fa fa-close"></i></span></button>
                </div>
                <div class="modal-body">
                    <div class="form-pop-up-content">
                        <h2>Login to your account</h2>
                        <form action="#" method="post">
                            <div class="form-box">
                                <input type="text" placeholder="User Name" name="username">
                                <input type="password" placeholder="Password" name="pass">
                            </div>
                            <div class="checkobx-link">
                                <div class="left-col">
                                    <input type="checkbox" id="remember_me"><label for="remember_me">Remember
                                        Me</label>
                                </div>
                                <div class="right-col"><a href="#">Forget Password?</a></div>
                            </div>
                            <button type="submit">Sign In</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!--End of Login Form-->
    <!--Start of Register Form-->
    <div class="modal fade" id="register_box" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                                aria-hidden="true"><i class="fa fa-close"></i></span></button>
                </div>
                <div class="modal-body">
                    <div class="form-pop-up-content">
                        <h2>Sign Up</h2>
                        <form action="#" method="post">
                            <div class="form-box">
                                <input type="text" placeholder="Full Name" name="fullname">
                                <input type="text" placeholder="User Name" name="username">
                                <input type="email" placeholder="Email" name="email">
                                <input type="password" placeholder="Password" name="pass">
                                <input type="password" placeholder="Confirm Password" name="re_pass">
                            </div>
                            <div class="checkobx-link">
                                <div class="left-col">
                                    <input type="checkbox" id="remember_reg"><label for="remember_reg">Remember
                                        Me</label>
                                </div>
                            </div>
                            <button class="text-uppercase" type="submit">Register</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!--End of Register Form-->
</header>
