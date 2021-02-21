<?php
/**
 * The template for displaying the footer.
 *
 * Contains the closing of the #content div and all content after
 *
 * @package storefront
 */

?>

</div><!-- .col-full -->
</div><!-- #content -->

<?php do_action('storefront_before_footer'); ?>

<footer class="footer-area">
    <!-- Footer Top Area Start -->
    <div class="footer-top bg-4 pt-120 pb-120">
        <!-- Newsletter Area Start -->

        <div class="service-area pt-50">
            <div class="container">
                <div class="service-container">
                    <div class="service-content">
                        <div class="row">
                            <div class="col-lg-4 col-md-4">
                                <div class="single-service">
                                    <div class="service-image">
                                        <img src="<?= ASSET?>/img/icon/rocket.png" alt="">
                                    </div>
                                    <div class="service-text">
                                        <h3>Free delivery</h3>
                                        <p>Nam liber tempor cum soluta nobis eleifend option congue.</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-4 col-md-4">
                                <div class="single-service">
                                    <div class="service-image">
                                        <img src="<?= ASSET?>/img/icon/money.png" alt="">
                                    </div>
                                    <div class="service-text">
                                        <h3>Money guarantee</h3>
                                        <p>Nam liber tempor cum soluta nobis eleifend option congue.</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-4 col-md-4">
                                <div class="single-service">
                                    <div class="service-image">
                                        <img src="<?= ASSET?>/img/icon/support.png" alt="">
                                    </div>
                                    <div class="service-text">
                                        <h3>Online support</h3>
                                        <p>Nam liber tempor cum soluta nobis eleifend option congue.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
    <!-- Footer Top Area End -->
    <!-- Footer Bottom Area Start -->
    <div class="footer-bottom-area pt-15 pb-30">
        <div class="container">
            <div class="row">
                <div class="col-lg-6 d-flex col-md-6">
                    <div class="footer-text-bottom">
                        <p>Copyright &copy; <a href="#">Lozapets</a>. All Rights Reserved</p>
                    </div>
                </div>
                <div class="col-lg-6 col-md-6">
                    <div class="payment-img d-flex justify-content-end">
                        <img src="<?= ASSET?>/img/payment.png" alt="">
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Footer Bottom Area End -->
</footer>




<script src="<?= ASSET ?>/js/vendor/jquery-3.2.1.min.js"></script>
<script src="<?= ASSET ?>/js/popper.min.js"></script>
<script src="<?= ASSET ?>/js/bootstrap.min.js"></script>
<script src="<?= ASSET ?>/js/plugins.js"></script>
<script src="<?= ASSET ?>/js/ajax-mail.js"></script>
<script src="<?= ASSET ?>/js/main.js"></script>

</div><!-- #page -->

<?php wp_footer(); ?>

</body>
</html>
