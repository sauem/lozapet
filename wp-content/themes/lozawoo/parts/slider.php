<div class="ht-hero-section fix">
    <div class="ht-hero-slider">
        <?php
        query_posts([
            'posts_per_page' => 3,
            'post_type' => 'slider',
        ]);
        if (have_posts()) {
            while (have_posts()) {
                the_post();
                ?>
                <!-- Single Slide Start -->
                <div class="ht-single-slide" style="background-image: url(<?= ASSET ?>/img/slider/1.jpg)">
                    <div class="ht-hero-content-one container">
                        <h3><?= get_the_excerpt() ?></h3>
                        <h1 class="cssanimation leDoorCloseLeft sequence"><?= get_the_title() ?></h1>
                        <p><?= get_the_content() ?></p>
                        <a href="<?= get_post_meta(get_the_ID(), 'link',TRUE) ?>"
                           class="default-btn large circle blue hover-blue uppercase">Shop now</a>
                    </div>
                </div>
                <?php
            }
        }
        ?>
    </div>
</div>
