<div class="col-xl-3 col-lg-4">
    <div class="sidebar-wrapper">
        <h3>Layered Navigation</h3>
        <div class="sidebar-widget">
            <h3>Categories</h3>
            <div class="sidebar-widget-option-wrapper">
                <?php
                $cats = wp_list_categories([
                    'taxonomy' => 'category',
                    'title_li' => '',
                    'show_count' => true,
                    'echo' => 0,
                    'style' => 'none'
                ]);
                printf('<div class="sidebar-widget-option"><input type="checkbox"><label>%s</label></div>', $cats);
                ?>
            </div>
        </div>
        <div class="sidebar-widget price-widget">
            <h3>Price Filter</h3>
            <div class="price-slider-container">
                <div id="slider-range"></div>
                <div class="price_slider_amount">
                    <div class="slider-values">
                        <input type="text" id="amount" name="price" placeholder="Add Your Price"/>
                    </div>
                </div>
            </div>
        </div>
        <div class="sidebar-widget">
            <h3>Color</h3>
            <div class="sidebar-widget-option-wrapper">
                <div class="sidebar-widget-option">
                    <input type="checkbox" id="black">
                    <label for="black">Black <span>(4)</span></label>
                </div>
                <div class="sidebar-widget-option">
                    <input type="checkbox" id="blue">
                    <label for="blue">Blue <span>(3)</span></label>
                </div>
                <div class="sidebar-widget-option">
                    <input type="checkbox" id="brown">
                    <label for="brown">Brown <span>(3)</span></label>
                </div>
                <div class="sidebar-widget-option">
                    <input type="checkbox" id="white">
                    <label for="white">White <span>(3)</span></label>
                </div>
            </div>
        </div>
        <div class="sidebar-widget">
            <h3>Manufacturer</h3>
            <div class="sidebar-widget-option-wrapper">
                <div class="sidebar-widget-option">
                    <input type="checkbox" id="dior">
                    <label for="dior">Christian Dior <span>(6)</span></label>
                </div>
                <div class="sidebar-widget-option">
                    <input type="checkbox" id="ferragamo">
                    <label for="ferragamo">ferragamo <span>(7)</span></label>
                </div>
                <div class="sidebar-widget-option">
                    <input type="checkbox" id="hermes">
                    <label for="hermes">hermes <span>(8)</span></label>
                </div>
                <div class="sidebar-widget-option">
                    <input type="checkbox" id="louis">
                    <label for="louis">louis vuitton <span>(6)</span></label>
                </div>
            </div>
        </div>
    </div>
    <div class="sidebar-banner-img">
        <a href="#"><img src="<?= ASSET ?>/img/banner/6.png" alt=""></a>
    </div>
    <div class="sidebar-wrapper">
        <?php echo dynamic_sidebar('Sidebar'); ?>
    </div>
</div>
