<?php

/**
 * Description of A2W_Woocommerce
 *
 * @author andrey
 */
if (!class_exists('A2W_Woocommerce')) {

    class A2W_Woocommerce {

        private static $active_plugins;
        private $attachment_model;
        private $reviews_model;
        private $helper;

        public function __construct() {
            $this->attachment_model = new A2W_Attachment();
            $this->reviews_model = new A2W_Review();
            $this->helper = new A2W_Helper();
        }

        public static function is_woocommerce_installed() {
            if (!self::$active_plugins) {
                self::$active_plugins = (array) get_option('active_plugins', array());
                if (is_multisite()) {
                    self::$active_plugins = array_merge(self::$active_plugins, get_site_option('active_sitewide_plugins', array()));
                }
            }

            return in_array('woocommerce/woocommerce.php', self::$active_plugins) || array_key_exists('woocommerce/woocommerce.php', self::$active_plugins);
        }

        public function build_steps($product) {
            $steps = array('init');

            $images_to_preload = A2W_Utils::get_all_images_from_product($product, true);
            foreach($images_to_preload as $img_id=>$image){
                $steps[] = 'preload_images#'.$img_id;
            }

            if($this->need_import_variations($product)){
                $steps[] = 'variations#attributes';
                foreach($product['sku_products']['variations'] as $variation){
                    $steps[] = 'variations#variation#'.$variation['id'];
                };
                $steps[] = 'variations#sync';
            } else {
                $steps[] = 'variations';
            }

            $images_data = $this->prepare_product_images($product);
            if($images_data['thumb']){
                $steps[]  = 'images#'.md5($images_data['thumb']);
            }
            foreach($images_data['images'] as $image_url){
                $steps[]  = 'images#'.md5($image_url);
            }
            $steps[]  = 'finishing'; 

            return $steps;
        }

        private function need_import_variations($product, $product_type = false) {
            $product_type = $product_type?$product_type:((isset($product['product_type']) && $product['product_type']) ? $product['product_type'] : a2w_get_setting('default_product_type', 'simple'));
            return !a2w_check_defined('A2W_DO_NOT_IMPORT_VARIATIONS') && 
                $product_type !== "external" && 
                !empty($product['sku_products']['variations']) && 
                count($product['sku_products']['variations']) > 1;
        }

        private function is_product_exist($product_id) {
            global $wpdb;
            return !!$wpdb->get_row($wpdb->prepare("SELECT p.ID FROM $wpdb->posts p WHERE p.ID = %d and p.post_type='product' LIMIT 1", $product_id));
        }

        private function prepare_product_images($product){
            $thumb_url = '';
            $tmp_all_images = A2W_Utils::get_all_images_from_product($product);

            if (isset($product['thumb_id'])) {
                foreach ($tmp_all_images as $img_id => $img) {
                    if ($img_id === $product['thumb_id']) {
                        $thumb_url = A2W_Utils::clear_url($img['image']);
                        break;
                    }
                }
            }

            $result = array('thumb'=>'', 'images'=>array());

            if (isset($product['images'])) {
                $image_to_load = array();
                foreach ($product['images'] as $image) {
                    if (!in_array(md5($image), $product['skip_images'])) {
                        $image_to_load[md5($image)] = $image;
                    }
                }

                foreach ($product['tmp_copy_images'] as $img_id => $source) {
                    if (isset($tmp_all_images[$img_id]) && !in_array($img_id, $product['skip_images'])) {
                        $image_to_load[$img_id] = $tmp_all_images[$img_id]['image'];
                    }
                }

                foreach ($product['tmp_move_images'] as $img_id => $source) {
                    if (isset($tmp_all_images[$img_id]) && !in_array($img_id, $product['skip_images'])) {
                        $image_to_load[$img_id] = $tmp_all_images[$img_id]['image'];
                    }
                }

                // if not thumb not checked, check first available image
                if (!$thumb_url && !empty($image_to_load)) {
                    $tmp_images = array_values($image_to_load);
                    $thumb_url = array_shift($tmp_images);
                }

                $result = array('thumb'=>$thumb_url, 'images'=>$image_to_load);
            }

            return $result;
        }

        public function add_product($product, $params = array()) {
            if (!A2W_Woocommerce::is_woocommerce_installed()) {
                return A2W_ResultBuilder::buildError("Woocommerce is not installed");
            }

            global $wpdb;

            $step = isset($params['step'])?$params['step']:false;
            $product_id = isset($params['product_id'])?$params['product_id']:false;

            $product_type = (isset($product['product_type']) && $product['product_type']) ? $product['product_type'] : a2w_get_setting('default_product_type', 'simple');
            $product_status = (isset($product['product_status']) && $product['product_status']) ? $product['product_status'] : a2w_get_setting('default_product_status', 'publish');

            $post_title = isset($product['title']) && $product['title'] ? $product['title'] : "Product " . $product['id'];

            $first_ava_var = false;
            $variations_active_cnt = 0;
            $total_quantity = 0;
            if (!empty($product['sku_products']['variations'])) {
                foreach ($product['sku_products']['variations'] as $variation) {
                    if (intval($variation['quantity']) > 0 && !in_array($variation['id'], $product['skip_vars'])) {
                        if (!$first_ava_var) {
                            $first_ava_var = $variation;
                        }
                        $variations_active_cnt++;
                        $total_quantity += intval($variation['quantity']);
                    }
                }
            }

            if($step === false || $step === 'init'){
                do_action('a2w_woocommerce_before_add_product', $product, $params);

                if ($product_type !== "external") {
                    $product_type = $variations_active_cnt > 1 ? 'variable' : 'simple';
                }

                $tax_input = array('product_type' => $product_type);
                $categories = $this->build_categories($product);
                if ($categories) {
                    $tax_input['product_cat'] = $categories;
                }

                $post = array(
                    'post_title' => $post_title,
                    'post_content' => '',
                    'post_status' => 'draft',
                    'post_name' => $post_title,
                    'post_type' => 'product',
                    'comment_status' => 'open',
                    'tax_input' => $tax_input,
                    'meta_input' => array('_stock_status' => 'instock',
                        '_sku' => empty($product['sku'])?$product['id']:$product['sku'],
                        '_visibility' => 'visible', // for old woocomerce
                        '_product_url' => $product['affiliate_url'],
                        '_a2w_import_type' => 'a2w',
                        '_a2w_external_id' => $product['id'],
                        '_a2w_import_id' => $product['import_id'],
                        '_a2w_product_url' => $product['affiliate_url'],
                        '_a2w_original_product_url' => $product['url'],
                        '_a2w_seller_url' => (!empty($product['seller_url']) ? $product['seller_url'] : ''),
                        '_a2w_seller_name' => (!empty($product['seller_name']) ? $product['seller_name'] : ''),
                        '_a2w_last_update' => time(),
                        '_a2w_skip_meta' => array('skip_vars' => $product['skip_vars'], 'skip_images' => $product['skip_images']),
                        '_a2w_disable_sync' => 0,
                        '_a2w_disable_var_price_change' => isset($product['disable_var_price_change']) && $product['disable_var_price_change'] ? 1 : 0,
                        '_a2w_disable_var_quantity_change' => isset($product['disable_var_quantity_change']) && $product['disable_var_quantity_change'] ? 1 : 0,
                        '_a2w_disable_add_new_variants' => isset($product['disable_add_new_variants']) && $product['disable_add_new_variants'] ? 1 : 0,
                        '_a2w_orders_count' => (!empty($product['ordersCount']) ? intval($product['ordersCount']) : 0),
                        '_a2w_video' => !empty($product['video']) ? $product['video'] : '',
                        '_a2w_import_lang' => !empty($product['import_lang']) ? $product['import_lang'] : A2W_AliexpressLocalizator::getInstance()->language
                    ),
                );

                $product_id = wp_insert_post($post);

                if ($first_ava_var) {
                    delete_post_meta($product_id, "_a2w_outofstock");
                } else {
                    update_post_meta($product_id, "_a2w_outofstock", true);
                }

                // update global price
                $this->update_price($product_id, $first_ava_var);
                
                // update global stock
                if (get_option('woocommerce_manage_stock', 'no') === 'yes') {
                    update_post_meta($product_id, '_manage_stock', 'yes');
                    update_post_meta($product_id, '_stock_status', $total_quantity ? 'instock' : 'outofstock');
                    update_post_meta($product_id, '_stock', $total_quantity);
                } else {
                    delete_post_meta($product_id, '_manage_stock');
                    delete_post_meta($product_id, '_stock_status');
                    delete_post_meta($product_id, '_stock');
                }

                if (isset($product['attribute']) && $product['attribute'] && !a2w_get_setting('not_import_attributes', false)) {
                    $this->set_attributes($product_id, $product['attribute']);
                }

                if (isset($product['tags']) && $product['tags']) {
                    wp_set_object_terms($product_id, array_map('sanitize_text_field', $product['tags']), 'product_tag');
                }

                $default_shipping_class = a2w_get_setting('default_shipping_class');
                if ($default_shipping_class) {
                    wp_set_object_terms($product_id, intval($default_shipping_class), 'product_shipping_class');
                }

                if($step !== false) return $result = A2W_ResultBuilder::buildOk(array('product_id'=>$product_id, 'step'=>$step));
            }
            
            if($step !== false && !$this->is_product_exist($product_id)){
                return A2W_ResultBuilder::buildError("Error! Processing processing product($product_id) not found");
            }

            if(substr($step, 0, strlen('preload_images')) === 'preload_images'){
                $images_to_preload = A2W_Utils::get_all_images_from_product($product, true);
                $cnt = 0;
                foreach($images_to_preload as $img_id=>$image){
                    $cnt++;
                    if($step === 'preload_images#'.$img_id){
                        $title = !empty($post_title) ? ($post_title . ' ' . $cnt) : null;
                        $this->attachment_model->create_attachment($product_id, $image['image'], array('inner_post_id' => $product_id, 'title' => $title, 'alt' => $title, 'check_duplicate' => true, 'edit_images' => !empty($product['tmp_edit_images']) ? $product['tmp_edit_images'] : array()));
                    }
                }
                return A2W_ResultBuilder::buildOk(array('product_id'=>$product_id, 'step'=>$step));
            }

            if($step === false || substr($step, 0, strlen('images')) === 'images'){
                $images_data = $this->prepare_product_images($product);
                
                if(!empty($images_data['thumb']) || !empty($images_data['images'])){
                    $this->set_images($product, $product_id, $images_data['thumb'], $images_data['images'], true, $post_title, $params);
                }

                if($step !== false) return $result = A2W_ResultBuilder::buildOk(array('product_id'=>$product_id, 'step'=>$step));
            }
            
            if($step === false || substr($step, 0, strlen('variations')) === 'variations'){
                if ($this->need_import_variations($product, $product_type)) {
                    foreach ($product['sku_products']['variations'] as &$var) {
                        $var['image'] = (!isset($var['image']) || in_array(md5($var['image']), $product['skip_images'])) ? '' : $var['image'];
                    }
                    $this->add_variation($product_id, $product, false, $params);
                }else if ($first_ava_var) {
                    $aliexpress_sku_props_id_arr = array();
                    foreach ($first_ava_var['attributes'] as $cur_var_attr) {
                        foreach ($product['sku_products']['attributes'] as $attr) {
                            if (isset($attr['value'][$cur_var_attr])) {
                                $aliexpress_sku_props_id_arr[] = isset($attr['value'][$cur_var_attr]['original_id']) ? $attr['value'][$cur_var_attr]['original_id'] : $attr['value'][$cur_var_attr]['id'];
                                break;
                            }
                        }
                    }
                    $aliexpress_sku_props_id = $aliexpress_sku_props_id_arr ? implode(";", $aliexpress_sku_props_id_arr) : "";
                    if ($aliexpress_sku_props_id) {
                        update_post_meta($product_id, '_aliexpress_sku_props', $aliexpress_sku_props_id);
                    }
                }
                if($step !== false) return A2W_ResultBuilder::buildOk(array('product_id'=>$product_id, 'step'=>$step));
            }

            if($step === false || $step === 'finishing'){
                $post_arr = array(
                    'ID' => $product_id, 
                    'post_status' => $product_status,
                    'post_content' => (isset($product['description']) ? $this->build_description($product_id, $product) : '')
                );

                if (a2w_get_setting('load_review')) {
                    $this->reviews_model->load($product_id);
                    //make sure that post comment status is 'open'
                    $post_arr['comment_status'] = 'open';
                }

                wp_update_post($post_arr);

                wc_delete_product_transients($product_id);

                delete_transient('wc_attribute_taxonomies');

                do_action('a2w_add_product', $product_id);

                A2W_Utils::update_post_terms_count($product_id);

                if($step !== false) return apply_filters('a2w_woocommerce_after_add_product', A2W_ResultBuilder::buildOk(array('product_id' => $product_id, 'step'=>$step)), $product_id, $product, $params);
            }
            return apply_filters('a2w_woocommerce_after_add_product', A2W_ResultBuilder::buildOk(array('product_id' => $product_id)), $product_id, $product, $params);
        }

        public function upd_product($product_id, $product, $params = array()) {
            do_action('a2w_woocommerce_upd_product', $product_id, $product, $params);

            // first, update some meta
            if (!empty($product['affiliate_url']) && !a2w_check_defined('A2W_DISABLE_UPDATE_AFFILIATE_URL')) {
                update_post_meta($product_id, '_product_url', $product['affiliate_url']);
                update_post_meta($product_id, '_a2w_product_url', $product['affiliate_url']);
            }

            if (!empty($product['url'])) {
                update_post_meta($product_id, '_a2w_original_product_url', $product['url']);
            }

            if (!empty($product['ordersCount'])) {
                update_post_meta($product_id, '_a2w_orders_count', intval($product['ordersCount']));
            }

            if (!get_post_meta($product_id, '_a2w_import_type', true)) {
                update_post_meta($product_id, '_a2w_import_type', 'a2w');
            }

            if (!empty($product['video'])) {
                update_post_meta($product_id, '_a2w_video', $product['video']);
            }

            $result = array("state" => "ok", "message" => "");
            
            $on_not_available_product = a2w_get_setting('on_not_available_product');
            $on_not_available_variation = a2w_get_setting('on_not_available_variation');

            $first_ava_var = false;
            $variations_active_cnt = 0;
            $total_quantity = 0;
            if (!empty($product['sku_products']['variations'])) {
                foreach ($product['sku_products']['variations'] as $variation) {
                    if (intval($variation['quantity']) > 0 && !in_array($variation['id'], $product['skip_vars'])) {
                        if (!$first_ava_var) {
                            $first_ava_var = $variation;
                        }
                        $variations_active_cnt++;
                        $total_quantity += intval($variation['quantity']);
                    }
                }
            }
            
            if ($first_ava_var) {
                $aliexpress_sku_props_id_arr = array();
                foreach ($first_ava_var['attributes'] as $cur_var_attr) {
                    foreach ($product['sku_products']['attributes'] as $attr) {
                        if (isset($attr['value'][$cur_var_attr])) {
                            $aliexpress_sku_props_id_arr[] = isset($attr['value'][$cur_var_attr]['original_id']) ? $attr['value'][$cur_var_attr]['original_id'] : $attr['value'][$cur_var_attr]['id'];
                            break;
                        }
                    }
                }
                $aliexpress_sku_props_id = $aliexpress_sku_props_id_arr ? implode(";", $aliexpress_sku_props_id_arr) : "";
                if ($aliexpress_sku_props_id) {
                    update_post_meta($product_id, '_aliexpress_sku_props', $aliexpress_sku_props_id);
                }
            }
            
            if ($first_ava_var) {
                delete_post_meta($product_id, "_a2w_outofstock");
            } else {
                update_post_meta($product_id, "_a2w_outofstock", true);
            }
            
            $wc_product = wc_get_product($product_id);
            
            // update variations
            if (!a2w_check_defined('A2W_DO_NOT_IMPORT_VARIATIONS') && !$wc_product->is_type('external') && !empty($product['sku_products']['variations']) && count($product['sku_products']['variations']) > 1) {
                foreach ($product['sku_products']['variations'] as &$var) {
                    $var['image'] = (!isset($var['image']) || in_array(md5($var['image']), $product['skip_images'])) ? '' : $var['image'];
                }
                $this->add_variation($product_id, $product, true);
            }
            
            // update global stock
            if (get_option('woocommerce_manage_stock', 'no') === 'yes') {
                if ($total_quantity>0 || in_array($on_not_available_product, array('zero', 'trash'))) {

                    $backorders = $wc_product->get_backorders();
                    $backorders = $backorders?$backorders:'no';

                    $wc_product->set_backorders($backorders);
                    $wc_product->set_manage_stock('yes');
                    $wc_product->set_stock_status($total_quantity ? 'instock' : 'outofstock');

                    if (!$product['disable_var_quantity_change']) {
                        $wc_product->set_stock_quantity($total_quantity);
                    }
                }
            } else if(!$wc_product->is_type('external')){
                $wc_product->set_manage_stock('no');
                $wc_product->set_stock_status('');
                $wc_product->set_stock_quantity('');
            }
            
            // update global price
            if (!$product['disable_var_price_change'] && ($first_ava_var || $on_not_available_product !== 'trash')) {
                $this->update_price($wc_product, $first_ava_var);
            }
            
            $product_type = $wc_product->get_type();
            $deleted = false;
            if ($first_ava_var) {
                if($wc_product->get_status() == "trash"){
                    wp_untrash_post($product_id);
                }
                // product available >>>
                if ($wc_product->is_type('external') && $first_ava_var) {
                    $init_status = get_post_meta($product_id, '_a2w_init_product_status', true);
                    if ($wc_product->get_status() !== $init_status) {
                        $wc_product->set_status($init_status);
                    }
                    delete_post_meta($product_id, '_a2w_init_product_status');
                }

                if (!$wc_product->is_type('external')) {
                    $product_type = $variations_active_cnt > 1 ? 'variable' : 'simple';
                }
            } else {
                // product not available >>>
                if ($on_not_available_product === 'trash') {
                    $wc_product->delete();
                    $deleted = true;
                } else if ($on_not_available_product === 'zero') {
                    $tmp_skip_meta = get_post_meta($product_id, "_a2w_skip_meta", true);

                    foreach ($wc_product->get_children() as $var_id) {
                        if($on_not_available_variation === "trash"){
                            $var = wc_get_product($var_id);
                            A2W_Utils::delete_post_images($var_id);
                            $var->delete(true);
                        } else if(!$product['disable_var_quantity_change'] && ($on_not_available_variation === "zero" || $on_not_available_variation === 'zero_and_disable')){
                            $var = wc_get_product($var_id);

                            $backorders = $var->get_backorders();
                            $backorders = $backorders?$backorders:'no';

                            $var->set_status($on_not_available_variation === 'zero_and_disable'?'private':$var->get_status());
                            $var->set_backorders($backorders);
                            $var->set_stock_quantity(0);
                            $var->set_stock_status('outofstock');
                            $var->save();
                        }
                    }

                    if ($wc_product->is_type('variable')) {
                        $product_type = 'simple';
                    }

                    $cur_status = $wc_product->get_status();
                    if ($wc_product->is_type('external') && $cur_status !== 'draft') {
                        update_post_meta($product_id, '_a2w_init_product_status', $wc_product->get_status());
                        $wc_product->set_status('draft');
                    }

                    update_post_meta($product_id, "_a2w_skip_meta", $tmp_skip_meta);
                }
            }
            
            if(!$deleted){
                $wc_product->save();
            }

            //A2W_FIX_RELOAD_IMAGES - special flag (for update only), if product images is disapear, reload it.
            if (a2w_check_defined('A2W_FIX_RELOAD_IMAGES') && isset($product['images'])) {
                $old_thumb_id = get_post_thumbnail_id($product_id);
                if ($old_thumb_id) {
                    A2W_Utils::delete_attachment($old_thumb_id, true);
                    delete_post_meta($product_id, '_thumbnail_id');
                }

                $old_image_gallery = get_post_meta($product_id, '_product_image_gallery', true);
                if ($old_image_gallery) {
                    $image_ids = explode(",", $old_image_gallery);
                    foreach ($image_ids as $image_id) {
                        A2W_Utils::delete_attachment($image_id, true);
                    }
                    delete_post_meta($product_id, '_product_image_gallery');
                }

                $thumb_url = '';
                $image_to_load = array();
                foreach ($product['images'] as $image) {
                    if (!in_array(md5($image), $product['skip_images'])) {
                        $image_to_load[] = $image;

                        if (!$thumb_url) {
                            // if not thumb not checked, check first available image
                            $thumb_url = $image;
                        }
                    }
                }

                $this->set_images($product, $product_id, $thumb_url, $image_to_load, true, isset($product['title']) && $product['title'] ? $product['title'] : "Product " . $product['id']);
            }

            if (isset($params['manual_update']) && $params['manual_update'] && a2w_check_defined('A2W_FIX_RELOAD_DESCRIPTION') && !a2w_get_setting('not_import_description')) {
                $post_arr = array('ID' => $product_id, 'post_content' => (isset($product['description']) ? $this->build_description($product_id, $product) : ''));
                wp_update_post($post_arr);
            }

            if ($product_type !== $wc_product->get_type()) {
                wp_set_object_terms($product_id, $product_type, 'product_type');
            }
            
            wc_delete_product_transients($product_id);

            if (empty($params['skip_last_update'])) {
                update_post_meta($product_id, '_a2w_last_update', time());
            }

            do_action('a2w_after_upd_product', $product_id, $product, $params);

            delete_transient('wc_attribute_taxonomies');
            

            return apply_filters('a2w_woocommerce_after_upd_product', $result, $product_id, $product, $params);
        }

        public function build_description($product_id, $product) {
            $html = $product['description'];

            if (function_exists('mb_convert_encoding')) {
                $html = trim(mb_convert_encoding($html, 'HTML-ENTITIES', 'UTF-8'));
            } else {
                $html = htmlspecialchars_decode(utf8_decode(htmlentities($html, ENT_COMPAT, 'UTF-8', false)));
            }

            if (function_exists('libxml_use_internal_errors')) {
                libxml_use_internal_errors(true);
            }
            if(class_exists('DOMDocument')){
                $dom = new DOMDocument();
                @$dom->loadHTML($html);
                $dom->formatOutput = true;

                $elements = $dom->getElementsByTagName('img');
                for ($i = $elements->length; --$i >= 0;) {
                    $e = $elements->item($i);

                    if (isset($product['tmp_move_images'])) {
                        foreach ($product['tmp_move_images'] as $img_id => $source) {
                            if (isset($tmp_all_images[$img_id]) && !in_array($img_id, $product['skip_images'])) {
                                $image_to_load[$img_id] = $tmp_all_images[$img_id]['image'];
                            }
                        }
                    }


                    $img_id = md5($e->getAttribute('src'));
                    if (in_array($img_id, $product['skip_images']) || isset($product['tmp_move_images'][$img_id])) {
                        $e->parentNode->removeChild($e);
                    } else if (!a2w_get_setting('use_external_image_urls')) {
                        $tmp_title = isset($product['title']) && $product['title'] ? $product['title'] : "Product " . $product['id'];

                        // if have edited image, than user initial url
                        $clear_image_url = !empty($product['tmp_edit_images'][$img_id]) ? $e->getAttribute('src') : A2W_Utils::clear_image_url($e->getAttribute('src'));

                        $attachment_id = $this->attachment_model->create_attachment($product_id, $clear_image_url, array('inner_post_id' => $product_id, 'title' => $tmp_title, 'alt' => $tmp_title, 'check_duplicate' => true, 'edit_images' => !empty($product['tmp_edit_images']) ? $product['tmp_edit_images'] : array()));
                        $attachment_url = wp_get_attachment_url($attachment_id);
                        $e->setAttribute('src', $attachment_url);
                    } else if (!empty($product['tmp_edit_images'][$img_id])) {
                        $e->setAttribute('src', $product['tmp_edit_images'][$img_id]['attachment_url']);
                    }
                }

                $html = $dom->saveHTML();
            }

            $html = preg_replace('~<(?:!DOCTYPE|/?(?:html|body))[^>]*>\s*~i', '', $html);

            return html_entity_decode(trim($html), ENT_COMPAT, 'UTF-8');
        }

        public function set_images($product, $product_id, $thumb_url, $images, $update, $title = '',$params = array()) {
            $step = isset($params['step'])?$params['step']:false;

            if ($thumb_url && $thumb_url != 'empty' && (!get_post_thumbnail_id($product_id) || $update)
               && ($step === false || $step === 'images#'.md5($thumb_url))) {
                try {
                    $tmp_title = !empty($title) ? $title : null;
                    $thumb_id = $this->attachment_model->create_attachment($product_id, $thumb_url, array('inner_post_id' => $product_id, 'title' => $tmp_title, 'alt' => $tmp_title, 'check_duplicate' => true, 'edit_images' => !empty($product['tmp_edit_images']) ? $product['tmp_edit_images'] : array()));
                    if (is_wp_error($thumb_id)) {
                        error_log("Can't download $thumb_url: ".print_r($thumb_id, true));
                    }else{
                        set_post_thumbnail($product_id, $thumb_id);
                    }
                } catch (Exception $e) {
                    error_log($e->getMessage());
                }
            }

            if ($images) {
                $cur_product_image_gallery = get_post_meta($product_id, '_product_image_gallery', true);
                $cur_product_image_gallery =  $cur_product_image_gallery?$cur_product_image_gallery:'';

                if (!$cur_product_image_gallery || $update) {
                    $image_gallery_ids = $step !== false ? $cur_product_image_gallery : '';
                    $cnt = 0;
                    foreach ($images as $image_url) {
                        $cnt++;
                        if($step === false || $step === 'images#'.md5($image_url)) {
                            if ($image_url == $thumb_url) {
                                continue;
                            }
                            try {
                                $tmp_title = !empty($title) ? ($title . ' ' . $cnt) : null;
                                $new_image_gallery_id = $this->attachment_model->create_attachment($product_id, $image_url, array('inner_post_id' => $product_id, 'title' => $tmp_title, 'alt' => $tmp_title, 'check_duplicate' => true, 'edit_images' => !empty($product['tmp_edit_images']) ? $product['tmp_edit_images'] : array()));
                                if (is_wp_error($new_image_gallery_id)) {
                                    error_log("Can't download $image_url".print_r($new_image_gallery_id, true));
                                }else{
                                    $image_gallery_ids .= $new_image_gallery_id . ',';
                                }
                            } catch (Exception $e) {
                                error_log($e->getMessage());
                            }
                        }
                    }
                    update_post_meta($product_id, '_product_image_gallery', $image_gallery_ids);
                }
            }
        }

        public function update_price(&$product, $variation, $rest_price = false) {
            if ($variation) {
                $price = isset($variation['price']) ? $variation['price'] : 0;
                $regular_price = isset($variation['regular_price']) ? $variation['regular_price'] : $price;

                if(is_a( $product, 'WC_Product' )){
                    update_post_meta($product->get_id(), '_aliexpress_regular_price', $regular_price);
                    update_post_meta($product->get_id(), '_aliexpress_price', $price);
                }else{
                    update_post_meta($product, '_aliexpress_regular_price', $regular_price);
                    update_post_meta($product, '_aliexpress_price', $price);
                }

                if (isset($variation['calc_price'])) {
                    $price = $variation['calc_price'];
                    $regular_price = isset($variation['calc_regular_price']) ? $variation['calc_regular_price'] : $price;
                }
                
                if(is_a( $product, 'WC_Product' )){
                    $product->set_regular_price($regular_price);
                    if (round(abs($regular_price - $price), 2) == 0) {
                        $product->set_price($regular_price);
                        $product->set_sale_price('');
                    } else {
                        $product->set_price($price);
                        $product->set_sale_price($price);
                    }
                }else{
                    update_post_meta($product, '_regular_price', $regular_price);
                    if (round(abs($regular_price - $price), 2) == 0) {
                        update_post_meta($product, '_price', $regular_price);
                        delete_post_meta($product, '_sale_price');
                    } else {
                        update_post_meta($product, '_price', $price);
                        update_post_meta($product, '_sale_price', $price);
                    }
                }
            } else if ($rest_price) {
                if(is_a( $product, 'WC_Product' )){
                    $product->set_regular_price(0);
                    $product->set_price(0);
                    $product->set_sale_price('');
                }else{
                    update_post_meta($product, '_price', 0);
                    update_post_meta($product, '_regular_price', 0);
                    delete_post_meta($product, '_sale_price');
                }
                
                delete_post_meta($product, '_aliexpress_regular_price');
                delete_post_meta($product, '_aliexpress_price');
            }
        }

        private function set_attributes($product_id, $attributes) {
            if (defined('A2W_IMPORT_EXTENDED_ATTRIBUTE')) {
                $extended_attribute = filter_var(A2W_IMPORT_EXTENDED_ATTRIBUTE, FILTER_VALIDATE_BOOLEAN);
            } else {
                $extended_attribute = a2w_get_setting('import_extended_attribute');
            }

            $attributes = apply_filters('a2w_set_product_attributes', $attributes);

            if ($extended_attribute) {
                $this->helper->set_woocommerce_attributes($attributes, $product_id);
            } else {
                $tmp_product_attr = array();
                foreach ($attributes as $attr) {
                    if (!isset($tmp_product_attr[$attr['name']])) {
                        $tmp_product_attr[$attr['name']] = is_array($attr['value']) ? $attr['value'] : array($attr['value']);
                    } else {
                        $tmp_product_attr[$attr['name']] = array_merge($tmp_product_attr[$attr['name']], is_array($attr['value']) ? $attr['value'] : array($attr['value']));
                    }
                }

                $product_attributes = array();
                foreach ($tmp_product_attr as $name => $value) {
                    $product_attributes[str_replace(' ', '-', $name)] = array(
                        'name' => $name,
                        'value' => implode(', ', $value),
                        'position' => count($product_attributes),
                        'is_visible' => 1,
                        'is_variation' => 0,
                        'is_taxonomy' => 0
                    );
                }

                update_post_meta($product_id, '_product_attributes', $product_attributes);
            }
        }

        private function build_categories($product) {
            if (isset($product['categories']) && $product['categories']) {
                return is_array($product['categories']) ? array_map('intval', $product['categories']) : array(intval($product['categories']));
            } else if (isset($product['category_name']) && $product['category_name']) {
                $category_name = sanitize_text_field($product['category_name']);
                if ($category_name) {
                    $cat = get_terms('product_cat', array('name' => $category_name, 'hide_empty' => false));
                    if (empty($cat)) {
                        $cat = wp_insert_term($category_name, 'product_cat');
                        $cat_id = $cat['term_id'];
                    } else {
                        $cat_id = $cat->term_id;
                    }
                    return array($cat_id);
                }
            }
            return array();
        }

        private function add_variation($product_id, $product, $is_update = false, $params=array()) {
            global $wpdb;

            $step = isset($params['step'])?$params['step']:false;

            $result = array('state' => 'ok', 'message' => '');
            $variations = $product['sku_products'];

            $disable_add_new_variants = get_post_meta($product_id, '_a2w_disable_add_new_variants', true);   
            $on_new_variation_appearance = $disable_add_new_variants ? "nothing" : a2w_get_setting('on_new_variation_appearance');

            $localCurrency = strtoupper(a2w_get_setting('local_currency'));

            $woocommerce_manage_stock = get_option('woocommerce_manage_stock', 'no');

            if ($localCurrency === 'USD') {
                $localCurrency = '';
            }

            if ($localCurrency) {
                $currency_conversion_factor = 1;
            } else {
                $currency_conversion_factor = floatval(a2w_get_setting('currency_conversion_factor'));
            }

            if (a2w_check_defined('A2W_FIX_RELOAD_VARIATIONS')) {
                delete_post_meta($product_id, '_a2w_original_variations_attributes');
            }

            $deleted_variations_attributes = get_post_meta($product_id, '_a2w_deleted_variations_attributes', true);
            $deleted_variations_attributes = $deleted_variations_attributes && is_array($deleted_variations_attributes) ? $deleted_variations_attributes : array();

            $original_variations_attributes = get_post_meta($product_id, '_a2w_original_variations_attributes', true);
            $original_variations_attributes = $original_variations_attributes && is_array($original_variations_attributes) ? $original_variations_attributes : array();

            $attributes = array();
            $used_variation_attributes = array();

            $tmp_attributes = get_post_meta($product_id, '_product_attributes', true);
            if (!$tmp_attributes) {
                $tmp_attributes = array();
            }


            $not_remove_variation_attr = a2w_check_defined('A2W_NOT_REMOVE_VARIATION_ATTR');
            foreach ($tmp_attributes as $attr) {
                if (!intval($attr['is_variation']) || $not_remove_variation_attr) {
                    $attributes[] = $attr;
                }
            }

            $old_swatch_type_options = get_post_meta($product_id, '_swatch_type_options', true);
            $old_swatch_type_options = $old_swatch_type_options ? $old_swatch_type_options : array();

            $swatch_type_options = array();

            //if names of variation attributes has been change, we need fix variation attribute names
            foreach ($variations['attributes'] as $key => $attr) {
                foreach ($original_variations_attributes as $ova_val) {
                    if (sanitize_title($attr['name']) === sanitize_title($ova_val['name']) && !empty($ova_val['current_name'])) {
                        if(!isset($variations['attributes'][$key]['original_name'])){
                            $variations['attributes'][$key]['original_name'] = $ova_val['name'];
                        }
                        $variations['attributes'][$key]['name'] = $ova_val['current_name'];

                        if (!empty($ova_val['values'])) {
                            foreach ($attr['value'] as $val_id => $val) {
                                foreach ($ova_val['values'] as $ova_val_key => $ova_val_val) {
                                    if ($val['id'] == $ova_val_val['oroginal_id']) {
                                        $variations['attributes'][$key]['value'][$val_id]['name'] = $ova_val_val['name'];
                                    }
                                }
                            }
                        }
                    }
                }
            }

            $old_vid_hash = array();

            foreach ($variations['attributes'] as $key => $attr) {
                $attribute_taxonomies = a2w_get_setting('import_extended_variation_attribute');

                if (!$attribute_taxonomies) {
                    $attribute_taxonomies = $wpdb->get_var("SELECT * FROM {$wpdb->prefix}woocommerce_attribute_taxonomies WHERE attribute_name = '" . esc_sql($this->helper->cleanTaxonomyName($attr['name'], false)) . "'");
                }

                $attr_tax = $this->helper->cleanTaxonomyName($attr['name'], $attribute_taxonomies);
                // $swatch_id = md5(sanitize_title(($attribute_taxonomies ? 'pa_' : '') . $attr['name']));
                $swatch_id = md5(sanitize_title($attr_tax));
                $variations['attributes'][$key]['tax'] = $attr_tax;
                $variations['attributes'][$key]['swatch_id'] = $swatch_id;
                $variations['attributes'][$key]['attribute_taxonomies'] = $attribute_taxonomies;

                $used_variation_attributes[$attr_tax] = array('original_attribute_id' => $attr['id'], 'attribute_taxonomies' => $attribute_taxonomies, 'values' => array());



                //added 03.02.2018 ---
                if (!empty($old_swatch_type_options) && isset($old_swatch_type_options[$swatch_id])) {
                    $swatch_type_options[$swatch_id] = $old_swatch_type_options[$swatch_id];
                } /* end added */ else {
                    $swatch_type_options[$swatch_id]['type'] = 'radio';
                    $swatch_type_options[$swatch_id]['layout'] = 'default';
                    $swatch_type_options[$swatch_id]['size'] = 'swatches_image_size';

                    $swatch_type_options[$swatch_id]['attributes'] = array();
                }


                $attr_values = array();
                foreach ($attr['value'] as &$val) {
                    $has_variation = false;
                    foreach ($variations['variations'] as $variation) {
                        $is_this_new_val = "none";
                        /* skip for now
                        if($is_update && $on_new_variation_appearance !== "add"){
                            // not need add attribute value if this update call and on_new_variation_appearance flag eq "nothing"
                            if(!$old_vid_hash[$variation['id']]){
                                $old_vid = $wpdb->get_var($wpdb->prepare("SELECT p.ID FROM $wpdb->posts p INNER JOIN $wpdb->postmeta pm ON (p.ID=pm.post_id AND pm.meta_key='external_variation_id' AND pm.meta_value=%s) WHERE post_parent = %d and post_type='product_variation' order by post_date desc LIMIT 1", $variation['id'], $product_id), ARRAY_A);
                                $old_vid_hash[$variation['id']] = $old_vid?"none":"new";
                            }
                            $is_this_new_val = $old_vid_hash[$variation['id']];
                        }*/

                        if ($is_this_new_val === "none" && !in_array($variation['id'], $product['skip_vars'])) {
                            foreach ($variation['attributes'] as $va) {
                                if ($va == $val['id']) {
                                    $has_variation = true;
                                }
                            }
                        }
                    }

                    if (!$has_variation && !a2w_check_defined('A2W_SKIP_REMOVED_VARIATIONS_CHECK')) {
                        continue;
                    }

                    $attr_values[] = $val['name'];

                    $attr_image = "";

                    if (a2w_get_setting('use_external_image_urls')){
                        if (isset($val['thumb']) && $val['thumb']) {
                            $attr_image = $val['thumb'];
                        } else if (isset($val['image']) && $val['image']) {
                            $attr_image = $val['image'];
                        }
                    } else{
                        if (isset($val['image']) && $val['image']) {
                            $attr_image = $val['image'];
                        } else if (isset($val['thumb']) && $val['thumb']) {
                            $attr_image = $val['thumb'];
                        }
                    }

                    $swatch_value_id = md5(sanitize_title(strtolower(htmlspecialchars($val['name'], ENT_NOQUOTES))));

                    $val['swatch_value_id'] = $swatch_value_id;

                    $RELOAD_ATTR_IMAGES = a2w_check_defined('A2W_FIX_RELOAD_IMAGES') || a2w_check_defined('A2W_FIX_RELOAD_ATTR_IMAGES');

                    //added 03.02.2018
                    if (isset($swatch_type_options[$swatch_id]['attributes'][$swatch_value_id]) && !$RELOAD_ATTR_IMAGES)
                        continue;
                    //end added 

                    if ($attr_image || !empty($val['color'])) {
                        $swatch_type_options[$swatch_id]['type'] = 'product_custom';
                    }

                    $swatch_type_options[$swatch_id]['attributes'][$swatch_value_id]['type'] = 'color';
                    $swatch_type_options[$swatch_id]['attributes'][$swatch_value_id]['color'] = empty($val['color']) ? '#FFFFFF' : $val['color'];
                    $swatch_type_options[$swatch_id]['attributes'][$swatch_value_id]['image'] = 0;


                    if (($step === false || $step === 'variations#attributes') && $attr_image) {
                        $swatch_type_options[$swatch_id]['attributes'][$swatch_value_id]['type'] = 'image';

                        $old_attachment_id = !empty($old_swatch_type_options[$swatch_id]['attributes'][$swatch_value_id]['image']) ? intval($old_swatch_type_options[$swatch_id]['attributes'][$swatch_value_id]['image']) : 0;

                        if ($is_update && $RELOAD_ATTR_IMAGES) {
                            if (intval($old_attachment_id) > 0) {
                                A2W_Utils::delete_attachment($old_attachment_id, true);
                            }
                            $attachment_id = $this->attachment_model->create_attachment($product_id, $attr_image, array('inner_post_id' => $product_id, 'title' => null, 'alt' => null, 'check_duplicate' => true, 'edit_images' => !empty($product['tmp_edit_images']) ? $product['tmp_edit_images'] : array()));
                        } else {
                            $attachment_id = $old_attachment_id ? $old_attachment_id : $this->attachment_model->create_attachment($product_id, $attr_image, array('inner_post_id' => $product_id, 'title' => null, 'alt' => null, 'check_duplicate' => true, 'edit_images' => !empty($product['tmp_edit_images']) ? $product['tmp_edit_images'] : array()));
                        }

                        if (!empty($attachment_id)) {
                            $swatch_type_options[$swatch_id]['attributes'][$swatch_value_id]['image'] = $attachment_id; //+    
                        } else if (!empty($old_attachment_id)) {
                            $swatch_type_options[$swatch_id]['attributes'][$swatch_value_id]['image'] = $old_attachment_id; //+    
                        }
                    }
                }

                // if this is deleted attr or attr in product skip_attr meta, then not load this attribute
                $is_deleted_attr = false;
                $tmp_attr_name = sanitize_title($attr['name']);
                if(!empty($product['skip_attr'])){
                    $skip_attr = array_map('sanitize_title', is_array($product['skip_attr'])?$product['skip_attr']:array($product['skip_attr']));
                    $is_deleted_attr = in_array($tmp_attr_name, $skip_attr);
                }
                foreach ($deleted_variations_attributes as $key_del_attr => $del_attr) {
                    if (sanitize_title($del_attr['name']) == $tmp_attr_name || $key_del_attr == $tmp_attr_name) {
                        $is_deleted_attr = true;
                    }
                }

                if (($step === false || $step === 'variations#attributes') &&  !$is_deleted_attr) {
                    if ($attribute_taxonomies) {
                        $attributes[$attr_tax] = array(
                            'name' => $attr_tax,
                            'value' => '',
                            'position' => count($attributes),
                            'is_visible' => isset($tmp_attributes[$attr_tax]['is_visible']) ? $tmp_attributes[$attr_tax]['is_visible'] : '0',
                            'is_variation' => '1',
                            'is_taxonomy' => '1'
                        );
                        $this->helper->add_attribute($product_id, $attr['name'], $attr_values);
                    } else {
                        $new_attr_values = array_unique($attr_values);
                        asort($new_attr_values);

                        $attributes[$attr_tax] = array(
                            'name' => $attr['name'],
                            'value' => implode("|", $new_attr_values),
                            'position' => count($attributes),
                            'is_visible' => isset($tmp_attributes[$attr_tax]['is_visible']) ? $tmp_attributes[$attr_tax]['is_visible'] : '0',
                            'is_variation' => '1',
                            'is_taxonomy' => '0'
                        );
                    }
                }
            }
            if ($step === false || $step === 'variations#attributes') {
                update_post_meta($product_id, '_product_attributes', $attributes);
            }

            if ($is_update && a2w_check_defined('A2W_FIX_RELOAD_VARIATIONS')) {
                $tmp_skip_meta = get_post_meta($product_id, "_a2w_skip_meta", true);

                $wc_product = wc_get_product($product_id);
                foreach ($wc_product->get_children() as $var_id) {
                    $var = wc_get_product($var_id);
                    A2W_Utils::delete_post_images($var_id);
                    $var->delete(true);
                }

                update_post_meta($product_id, "_a2w_skip_meta", $tmp_skip_meta);
            }

            $old_variations = $wpdb->get_col($wpdb->prepare("SELECT p.ID FROM $wpdb->posts p INNER JOIN $wpdb->postmeta pm ON (p.ID=pm.post_id AND pm.meta_key='external_variation_id') WHERE post_parent = %d and post_type='product_variation' GROUP BY p.ID ORDER BY p.post_date desc", $product_id));

            $variation_images = array();
            foreach ($variations['variations'] as $variation) { 
                $need_process = $step === false || $step === 'variations#variation#'.$variation['id'];

                if (in_array($variation['id'], $product['skip_vars'])) {
                    continue;
                }
                unset($variation_id);
                $old_vid = $wpdb->get_row($wpdb->prepare("SELECT p.ID, p.post_status FROM $wpdb->posts p INNER JOIN $wpdb->postmeta pm ON (p.ID=pm.post_id AND pm.meta_key='external_variation_id' AND pm.meta_value=%s) WHERE post_parent = %d and post_type='product_variation' order by post_date desc LIMIT 1", $variation['id'], $product_id), ARRAY_A);

                if (!$old_vid && (!$is_update || $on_new_variation_appearance === 'add')) { 
                    
                    $on_not_available_variation = a2w_get_setting('on_not_available_variation');
                    
                    if($need_process){
                        $tmp_variation = array(
                            'post_title' => 'Product #' . $product_id . ' Variation',
                            'post_content' => '',
                            'post_status' => in_array($variation['id'], $product['skip_vars']) && $on_not_available_variation === 'zero_and_disable' ? 'private' : 'publish',
                            'post_parent' => $product_id,
                            'post_type' => 'product_variation',
                            'meta_input' => array(
                                'external_variation_id' => $variation['id'],
                                '_sku' => $variation['sku'],
                            ),
                        );
    
                        $variation_id = wp_insert_post($tmp_variation);
                    }
                    

                    // build _aliexpress_sku_props -->
                    $aliexpress_sku_props_id_arr = array();
                    foreach ($variation['attributes'] as $cur_var_attr) {
                        foreach ($variations['attributes'] as $attr) {
                            if (isset($attr['value'][$cur_var_attr])) {
                                $aliexpress_sku_props_id_arr[] = isset($attr['value'][$cur_var_attr]['original_id']) ? $attr['value'][$cur_var_attr]['original_id'] : $attr['value'][$cur_var_attr]['id'];
                                break;
                            }
                        }
                    }
                    $aliexpress_sku_props_id = $aliexpress_sku_props_id_arr ? implode(";", $aliexpress_sku_props_id_arr) : "";
                    if ($need_process && $aliexpress_sku_props_id) {
                        update_post_meta($variation_id, '_aliexpress_sku_props', $aliexpress_sku_props_id);
                    }
                    // <-- build _aliexpress_sku_props


                    
                    $variation_attribute_list = array();
                    foreach ($variation['attributes'] as $va) {
                        $attr_tax = "";
                        $attr_value = "";
                        foreach ($variations['attributes'] as $attr_key => $attr) {
                            $tmp_name = sanitize_title($attr['name']);

                            foreach ($attr['value'] as $val) {
                                if ($val['id'] == $va) {
                                    $attr_tax = $attr['tax'];
                                    $attr_value = $attr['attribute_taxonomies'] ? sanitize_title($this->helper->cleanTaxonomyName(htmlspecialchars($val['name'], ENT_NOQUOTES), false, false)) : $val['name'];
                                    // build original variations attributes
                                    if (!isset($original_variations_attributes[$tmp_name])) {
                                        $original_variations_attributes[$tmp_name] = array('original_attribute_id' => $attr['id'], 'current_name' => $attr['name'], 'name' => !empty($attr['original_name']) ? $attr['original_name'] : $attr['name'], 'values' => array());
                                    }

                                    $original_variations_attributes[$tmp_name]['values'][$val['id']] = array(
                                        'id' => $val['id'],
                                        'name' => $val['name'],
                                        'oroginal_id' => isset($val['src_id']) ? $variations['attributes'][$attr_key]['value'][$val['src_id']]['id'] : $val['id'],
                                        'oroginal_name' => isset($val['src_id']) ? $variations['attributes'][$attr_key]['value'][$val['src_id']]['name'] : $val['name'],
                                    );

                                    break;
                                }
                            }
                            if ($attr_tax && $attr_value) {
                                break;
                            }
                        }

                        if ($attr_tax && $attr_value) {
                            $variation_attribute_list[] = array('key' => ('attribute_' . $attr_tax), 'value' => $attr_value);

                            // collect used variation attribute values
                            if (isset($used_variation_attributes[$attr_tax])) {
                                $used_variation_attributes[$attr_tax]['values'][] = $attr_value;
                            }
                        }
                    }

                    if($need_process){
                        foreach ($variation_attribute_list as $vai) {
                            update_post_meta($variation_id, sanitize_title($vai['key']), $vai['value']);
                        }
                    }
                    // upload set variation image
                    if ($need_process && isset($variation['image']) && $variation['image']) {
                        $thumb_id = $this->attachment_model->create_attachment($product_id, $variation['image'], array('inner_post_id' => $variation_id, 'title' => null, 'alt' => null, 'check_duplicate' => true, 'edit_images' => !empty($product['tmp_edit_images']) ? $product['tmp_edit_images'] : array()));
                        set_post_thumbnail($variation_id, $thumb_id);
                    }

                } else if ($old_vid) {
                    $variation_id = $old_vid['ID'];

                    if ($need_process && $old_vid['post_status'] === 'trash') {
                        wp_untrash_post($variation_id);
                    }

                    $aliexpress_sku_props_id = get_post_meta($variation_id, '_aliexpress_sku_props', true);
                    $aliexpress_sku_props_id_arr = $aliexpress_sku_props_id ? explode(";", $aliexpress_sku_props_id) : array();

                    foreach ($used_variation_attributes as $attr_tax => $v) {
                        $tmp_attr_name = 'attribute_' . sanitize_title($attr_tax);
                        if ($attr_value = get_post_meta($variation_id, $tmp_attr_name, true)) {
                            // collect used variation attribute values
                            $used_variation_attributes[$attr_tax]['values'][] = $attr_value;

                            // if user change variation atrributes values, then need update swatch(if new swatch not exist)
                            $curr_swatch_value_id = md5(sanitize_title(strtolower($attr_value)));
                            foreach ($aliexpress_sku_props_id_arr as $var_attr_id) {
                                foreach ($variations['attributes'] as $external_attr) {
                                    if ($external_attr['tax'] === $attr_tax && isset($external_attr['value'][$var_attr_id]) && isset($external_attr['value'][$var_attr_id]['swatch_value_id'])) {
                                        $swatch_id = $external_attr['swatch_id'];
                                        $swatch_value_id = $external_attr['value'][$var_attr_id]['swatch_value_id'];

                                        if ($curr_swatch_value_id != $swatch_value_id && !isset($swatch_type_options[$swatch_id]['attributes'][$curr_swatch_value_id])) {
                                            if (isset($old_swatch_type_options[$swatch_id]['attributes'][$curr_swatch_value_id])) {
                                                $swatch_type_options[$swatch_id]['attributes'][$curr_swatch_value_id] = $old_swatch_type_options[$swatch_id]['attributes'][$curr_swatch_value_id];
                                                unset($swatch_type_options[$swatch_id]['attributes'][$swatch_value_id]);
                                            } else if (isset($old_swatch_type_options[$swatch_id]['attributes'][$swatch_value_id])) {
                                                $swatch_type_options[$swatch_id]['attributes'][$curr_swatch_value_id] = $old_swatch_type_options[$swatch_id]['attributes'][$swatch_value_id];
                                                unset($swatch_type_options[$swatch_id]['attributes'][$swatch_value_id]);
                                            } else if (isset($swatch_type_options[$swatch_id]['attributes'][$swatch_value_id])) {
                                                $swatch_type_options[$swatch_id]['attributes'][$curr_swatch_value_id] = $swatch_type_options[$swatch_id]['attributes'][$swatch_value_id];
                                                unset($swatch_type_options[$swatch_id]['attributes'][$swatch_value_id]);
                                            }
                                        }
                                    }
                                }
                            }

                            
                            // connect current attr and attr value to original
                            $original_attr = false;
                            $tmp_ids = explode('-',$variation['id']);
                            foreach($variations['attributes'] as $orig_attr){
                                if($orig_attr['id'] == $v['original_attribute_id']){
                                    foreach($orig_attr['value'] as $oav){
                                        if(in_array($oav['id'], $tmp_ids)){
                                            $original_attr = array(
                                                'id'=>$oav['id'], 
                                                'name'=>$oav['name'], 
                                                'attr_name'=>$orig_attr['name'], 
                                                'attr_original_name'=>isset($orig_attr['original_name'])?$orig_attr['original_name']:$orig_attr['name']
                                            );
                                            break;
                                        }
                                    }
                                }
                            }


                            // build original variations attributes
                            $tmp_name = (strpos($tmp_attr_name, 'attribute_pa_') === 0) ? substr($tmp_attr_name, 13) : substr($tmp_attr_name, 10);
                            if (!isset($original_variations_attributes[$tmp_name])) {
                                $original_variations_attributes[$tmp_name] = array(
                                    'original_attribute_id' => $v['original_attribute_id'], 
                                    'current_name' => isset($original_attr['attr_name'])?$original_attr['attr_name']:urldecode($tmp_name), 
                                    'name' => isset($original_attr['attr_original_name'])?$original_attr['attr_original_name']:urldecode($tmp_name),
                                    'values' => array()
                                );
                            } else {
                                $original_variations_attributes[$tmp_name]['original_attribute_id'] = $v['original_attribute_id'];
                                if(isset($original_attr['attr_original_name'])){
                                    $original_variations_attributes[$tmp_name]['name'] = $original_attr['attr_original_name'];
                                }
                            }

                            if(!isset($original_variations_attributes[$tmp_name]['values'])){
                                $original_variations_attributes[$tmp_name]['values'] = array();
                            }

                            if($original_attr){
                                $original_variations_attributes[$tmp_name]['values'][$original_attr['id']] = array(
                                    'id' => $original_attr['id'],
                                    'name' => $attr_value,
                                    'oroginal_id' => $original_attr['id'],
                                    'oroginal_name' => $original_attr['name'],
                                );
                            }
                        } else {
                            // if attr not find in variation (for example user change Lang), then add new meta to connect attr to variation
                            foreach ($variation['attributes'] as $va) {
                                $attr_tax = "";
                                $attr_value = "";
                                foreach ($variations['attributes'] as $attr_key => $attr) {
                                    if ($attr['id'] == $v['original_attribute_id']) {
                                        $tmp_name = sanitize_title($attr['name']);

                                        foreach ($attr['value'] as $val) {
                                            if ($val['id'] == $va) {
                                                $attr_tax = $attr['tax'];
                                                $attr_value = $attr['attribute_taxonomies'] ? sanitize_title($this->helper->cleanTaxonomyName(htmlspecialchars($val['name'], ENT_NOQUOTES), false, false)) : $val['name'];
                                                // build original variations attributes
                                                if (!isset($original_variations_attributes[$tmp_name])) {
                                                    $original_variations_attributes[$tmp_name] = array('original_attribute_id' => $attr['id'], 'current_name' => $attr['name'], 'name' => !empty($attr['original_name']) ? $attr['original_name'] : $attr['name'], 'values' => array());
                                                }

                                                $original_variations_attributes[$tmp_name]['values'][$val['id']] = array(
                                                    'id' => $val['id'],
                                                    'name' => $val['name'],
                                                    'oroginal_id' => isset($val['src_id']) ? $variations['attributes'][$attr_key]['value'][$val['src_id']]['id'] : $val['id'],
                                                    'oroginal_name' => isset($val['src_id']) ? $variations['attributes'][$attr_key]['value'][$val['src_id']]['name'] : $val['name'],
                                                );
                                                break;
                                            }
                                        }
                                        if ($attr_tax && $attr_value) {
                                            break;
                                        }
                                    }
                                }

                                if ($attr_tax && $attr_value) {
                                    if($need_process){
                                        update_post_meta($variation_id, sanitize_title('attribute_' . $attr_tax), $attr_value);
                                    }

                                    // collect used variation attribute values
                                    if (isset($used_variation_attributes[$attr_tax])) {
                                        $used_variation_attributes[$attr_tax]['values'][] = $attr_value;
                                    }
                                }
                            }
                        }
                    }

                    // A2W_FIX_RELOAD_IMAGES(or A2W_FIX_RELOAD_ATTR_IMAGES) - special flag (for update only), if variation images is disapear, reload it.
                    if ($need_process && $is_update && 
                        (a2w_check_defined('A2W_FIX_RELOAD_IMAGES') || a2w_check_defined('A2W_FIX_RELOAD_ATTR_IMAGES')) && 
                        isset($variation['image']) && $variation['image']
                    ) {
                        $old_thumb_id = get_post_thumbnail_id($variation_id);
                        if ($old_thumb_id) {
                            A2W_Utils::delete_attachment($old_thumb_id, true);
                            delete_post_meta($variation_id, '_thumbnail_id');
                        }

                        $thumb_id = $this->attachment_model->create_attachment($product_id, $variation['image'], array('inner_post_id' => $variation_id, 'title' => null, 'alt' => null, 'check_duplicate' => true, 'edit_images' => !empty($product['tmp_edit_images']) ? $product['tmp_edit_images'] : array()));
                        set_post_thumbnail($variation_id, $thumb_id);
                    }
                }

                if (isset($variation_id)) {
                    foreach ($old_variations as $k => $id) {
                        if (intval($id) == intval($variation_id)) {
                            unset($old_variations[$k]);
                        }
                    }

                    if($need_process){
                        if (!empty($variation['country_code'])) {
                            update_post_meta($variation_id, '_a2w_country_code', $variation['country_code']);
                        }
                        
                        if($var_product = wc_get_product($variation_id)){
                            $quantity = intval($variation['quantity']);
                            $backorders = $var_product->get_backorders();
                            $backorders = $backorders?$backorders:'no';

                            $on_not_available_variation = a2w_get_setting('on_not_available_variation');
                            $var_product->set_status(!$quantity && $on_not_available_variation === 'zero_and_disable' ? 'private' : 'publish');

                            $var_product->set_backorders($backorders);
                            if ($woocommerce_manage_stock === 'yes' && (!$old_vid || !$product['disable_var_quantity_change'])) {
                                $var_product->set_stock_quantity($quantity);
                            }
                            $var_product->set_manage_stock($woocommerce_manage_stock);
                            $var_product->set_stock_status($quantity ? 'instock' : 'outofstock');
                            
                            if (!$old_vid || !$product['disable_var_price_change']) {
                                $this->update_price($var_product, $variation);
                            } 
                            
                            $var_product->save();
                        }
                    }
                }
            }

            if ($step === false || $step === 'variations#attributes') {
                // update priduct swatches
                update_post_meta($product_id, '_swatch_type_options', $swatch_type_options);
                update_post_meta($product_id, '_swatch_type', 'pickers'); 
                update_post_meta($product_id, '_swatch_size', 'swatches_image_size'); 
            }

            if ($step === false || $step === 'variations#sync') {
                update_post_meta($product_id, '_a2w_original_variations_attributes', $original_variations_attributes);

                // if this is new import, and product has skip_attr, then update woocomerce product skip meta
                if(!$is_update && !empty($product['skip_attr'])){
                    $tmp_original_variations_attributes_tmp = $original_variations_attributes;
                    $skip_attr = array_map('sanitize_title', is_array($product['skip_attr'])?$product['skip_attr']:array($product['skip_attr']));
                    foreach ($tmp_original_variations_attributes_tmp as $key => $values) {
                        if (!in_array($key, $skip_attr)) {
                            unset($tmp_original_variations_attributes_tmp[$key]);    
                        }
                    }                            
                    update_post_meta($product_id, '_a2w_deleted_variations_attributes', $tmp_original_variations_attributes_tmp);
                }

                // delete old variations
                $on_not_available_variation = a2w_get_setting('on_not_available_variation');
                foreach ($old_variations as $variation_id) {
                    if ($on_not_available_variation === 'trash') {
                        $GLOBALS['a2w_autodelete_variaton_lock'] = true;
                        wp_delete_post($variation_id);
                        unset( $GLOBALS['a2w_autodelete_variaton_lock']);
                    } else if ($on_not_available_variation === 'zero' || $on_not_available_variation === 'zero_and_disable') {
                        $var_product = wc_get_product($variation_id);

                        $backorders = $var_product->get_backorders();
                        $backorders = $backorders?$backorders:'no';

                        $var_product->set_status($on_not_available_variation === 'zero_and_disable'?'private':$var_product->get_status());
                        $var_product->set_backorders($backorders);
                        $var_product->set_manage_stock( $woocommerce_manage_stock === 'yes'? 'yes' : 'no');
                        $var_product->set_stock_status('outofstock');
                        $var_product->set_stock_quantity(0);
                        $var_product->save();
                    }
                }

                // for simple variations attributes, update atributes values (save only used values)
                $need_update = false;
                foreach ($used_variation_attributes as $attr_tax => $uva) {
                    if (!$uva['attribute_taxonomies'] && isset($attributes[$attr_tax])) {
                        $new_attr_values = array_unique($uva['values']);
                        asort($new_attr_values);
                        $attributes[$attr_tax]['value'] = implode("|", $new_attr_values);
                        if ($new_attr_values) {
                            $need_update = true;
                        }
                    }
                }
                if ($need_update) {
                    update_post_meta($product_id, '_product_attributes', $attributes);
                }

                WC_Product_Variable::sync($product_id);
            }

            return $result;
        }

        public function update_order($order_id, $data = array()) {
            $post = get_post($order_id);
            if ($post && $post->post_type === 'shop_order') {
                if (!empty($data['meta']) && is_array($data['meta'])) {
                    foreach ($data['meta'] as $key => $val) {
                        update_post_meta($order_id, $key, $val);
                    }
                }
            }
        }

        public function get_fulfilled_orders_data() {
            global $wpdb;
            $result = $wpdb->get_results("SELECT pm1.meta_value as ext_order_id, pm1.post_id as order_id FROM {$wpdb->postmeta} as pm1 LEFT JOIN {$wpdb->postmeta} as pm2 ON (pm2.meta_key = '_a2w_tracking_code' and pm1.post_id=pm2.post_id) WHERE pm1.meta_key = '_a2w_external_order_id' AND pm2.post_id is null AND pm1.meta_value <> ''");
            return $result;
        }

        public function get_fulfilled_orders_count() {
            global $wpdb;
            $result = $wpdb->get_var("SELECT COUNT(*) as count FROM {$wpdb->postmeta} as pm1 LEFT JOIN {$wpdb->postmeta} as pm2 ON (pm2.meta_key = '_a2w_tracking_code' and pm1.post_id=pm2.post_id) WHERE pm1.meta_key = '_a2w_external_order_id' AND pm2.post_id is null AND pm1.meta_value <> ''");
            return $result;
        }

        public function save_tracking_code($order_id, $tracking_codes) {
            $result = A2W_ResultBuilder::buildOk();

            try {
                $order = new WC_Order($order_id);
            } catch (Exception $e) {
                $order = false;
            }
            if (!$order) {
                $result = A2W_ResultBuilder::buildError(_x('Didn`t find the Woocommerce order №', 'Error text', 'ali2woo') . $order_id);
            } else {
                $curr_tracking_codes = get_post_meta($order_id, '_a2w_tracking_code');

                foreach ($tracking_codes as $code_value) {
                    $code_value = trim(preg_replace('/\s+/', '', $code_value));

                    if (!empty($curr_tracking_codes) && is_array($curr_tracking_codes)) {
                        if (array_search($code_value, $curr_tracking_codes) === false) {
                            add_post_meta($order_id, '_a2w_tracking_code', $code_value);
                            do_action('wcae_after_add_tracking_code', $order_id, $code_value);
                        }
                    } else {
                        add_post_meta($order_id, '_a2w_tracking_code', $code_value);
                        do_action('wcae_after_add_tracking_code', $order_id, $code_value);
                    }
                }
                $tracking_code_order_status = a2w_get_setting('tracking_code_order_status');
                if ($tracking_code_order_status) {
                    $order->update_status($tracking_code_order_status);
                }
            }

            return $result;
        }

        public function get_sorted_products_ids($sort_type, $ids_count, $compare = false) {
            $result = array();

            $ids0 = get_posts(array(
                'post_type' => 'product',
                'fields' => 'ids',
                'numberposts' => $ids_count,
                'meta_query' => array(
                    array(
                        'key' => '_a2w_import_type',
                        'value' => 'a2w'
                    ),
                    array(
                        'key' => $sort_type,
                        'compare' => 'NOT EXISTS'
                    )
                )
            ));

            foreach ($ids0 as $id) {
                $result[] = $id;
            }

            if (($ids_count - count($result)) > 0) {

                $meta_query = array(
                    array(
                        'key' => '_a2w_import_type',
                        'value' => 'a2w'
                    )
                );

                if ($compare) {
                    if (is_array($compare)) {
                        if (isset($compare['value']) && isset($compare['compare'])) {
                            $meta_query[] = array('key' => $sort_type, 'value' => $compare['value'], 'compare' => $compare['compare']);
                        }
                    } else {
                        $meta_query[] = array('key' => $sort_type, 'value' => $compare);
                    }
                }

                $res = get_posts(array(
                    'post_type' => 'product',
                    'fields' => 'ids',
                    'numberposts' => ($ids_count - count($result)),
                    'meta_query' => $meta_query,
                    'order' => 'ASC',
                    'orderby' => 'meta_value',
                    'meta_key' => $sort_type,
                    'suppress_filters' => false
                ));

                foreach ($res as $id) {
                    $result[] = $id;
                }
            }
            return $result;
        }

        public function get_products_ids($page, $products_per_page) {
            // global $wpdb;
            // $result = $wpdb->get_col("SELECT DISTINCT post_id from $wpdb->postmeta WHERE meta_key = '_a2w_import_type' and meta_value='a2w' ORDER BY post_id LIMIT ".(intval($page) * intval($products_per_page)).", ".intval($products_per_page));
            // $result = array_map('intval', $result);
            $ids0 = get_posts(array(
                'post_type' => 'product',
                'fields' => 'ids',
                'offset' => $page * $products_per_page,
                'posts_per_page' => $products_per_page,
                'meta_query' => array(
                    array(
                        'key' => '_a2w_import_type',
                        'value' => 'a2w'
                    )
                )
            ));
            foreach ($ids0 as $id) {
                $result[] = $id;
            }
            return $result;
        }

        public function get_products_count() {
            global $wpdb;
            return $wpdb->get_var("SELECT count(DISTINCT post_id) from $wpdb->postmeta WHERE meta_key = '_a2w_import_type' and meta_value='a2w'");
        }

        function get_product_external_id($post_id) {
            $external_id = '';
            $post = get_post($post_id);
            if ($post) {
                if ($post->post_type === 'product') {
                    $external_id = get_post_meta($post_id, "_a2w_external_id", true);
                } else if ($post->post_type === 'product_variation') {
                    $external_id = get_post_meta($post->post_parent, "_a2w_external_id", true);
                }
            }
            return $external_id;
        }

        function get_product_by_post_id($post_id, $with_vars = true) {
            global $wpdb;
            $product = array();

            $external_id = get_post_meta($post_id, "_a2w_external_id", true);
            if ($external_id) {
                $woocommerce_manage_stock = get_option('woocommerce_manage_stock', 'no');

                $product = array(
                    'id' => $external_id,
                    'post_id' => $post_id,
                    'url' => get_post_meta($post_id, "_a2w_original_product_url", true),
                    'affiliate_url' => get_post_meta($post_id, "_a2w_product_url", true),
                    'seller_url' => get_post_meta($post_id, "_a2w_seller_url", true),
                    'import_type' => get_post_meta($post_id, "_a2w_import_type", true),
                );

                $cats = wp_get_object_terms($post_id, 'product_cat');
                if (!is_wp_error($cats) && $cats) {
                    $product['category_id'] = $cats[0]->term_id;
                }

                $import_lang = get_post_meta($post_id, "_a2w_import_lang", true);
                $product['import_lang'] = $import_lang ? $import_lang : A2W_AliexpressLocalizator::getInstance()->language;

                $price = get_post_meta($post_id, "_aliexpress_price", true);
                $regular_price = get_post_meta($post_id, "_aliexpress_regular_price", true);

                $price = $price ? $price : 0;
                $regular_price = $regular_price ? $regular_price : 0;
                
                $product['price'] = $price ? $price : $regular_price;
                $product['regular_price'] = $regular_price ? $regular_price : $price;
                $product['discount'] = $product['regular_price'] ? 100 - round($product['price'] * 100 / $product['regular_price']) : 0;
            
                $price = get_post_meta($post_id, "_price", true);
                $regular_price = get_post_meta($post_id, "_regular_price", true);

                $price = $price ? $price : 0;
                $regular_price = $regular_price ? $regular_price : 0;

                $product['calc_price'] = $price ? $price : $regular_price;
                $product['calc_regular_price'] = $regular_price ? $regular_price : $price;

                if ($woocommerce_manage_stock === 'yes') {
                    $product['quantity'] = get_post_meta($post_id, "_stock", true);
                } else {
                    $product['quantity'] = get_post_meta($post_id, '_stock_status', true) === 'outofstock' ? 0 : 1;
                }

                $original_product_url = get_post_meta($post_id, "_a2w_original_product_url", true);
                $product['original_product_url'] = $original_product_url ? $original_product_url : 'www.aliexpress.com/item//' . $product['id'] . '.html';

                $availability_meta = get_post_meta($post_id, "_a2w_availability", true);
                $product['availability'] = $availability_meta ? filter_var($availability_meta, FILTER_VALIDATE_BOOLEAN) : true;

                $a2w_skip_meta = get_post_meta($post_id, "_a2w_skip_meta", true);

                $product['skip_vars'] = $a2w_skip_meta && !empty($a2w_skip_meta['skip_vars']) ? $a2w_skip_meta['skip_vars'] : array();
                $product['skip_images'] = $a2w_skip_meta && !empty($a2w_skip_meta['skip_images']) ? $a2w_skip_meta['skip_images'] : array();

                $product['disable_sync'] = get_post_meta($post_id, "_a2w_disable_sync", true);
                $product['disable_var_price_change'] = get_post_meta($post_id, "_a2w_disable_var_price_change", true);
                $product['disable_var_quantity_change'] = get_post_meta($post_id, "_a2w_disable_var_quantity_change", true);
                $product['disable_add_new_variants'] = get_post_meta($post_id, "_a2w_disable_add_new_variants", true);

                $product['sku_products']['attributes'] = array();
                $product['sku_products']['variations'] = array();
                if ($with_vars) {
                    $variations = $wpdb->get_col($wpdb->prepare("SELECT ID FROM $wpdb->posts WHERE post_parent = %d and post_type='product_variation'", $post_id));
                    if ($variations) {
                        foreach ($variations as $variation_id) {
                            $var = array('id' => get_post_meta($variation_id, "external_variation_id", true), 'attributes' => array());

                            $price = get_post_meta($variation_id, "_aliexpress_price", true);
                            $regular_price = get_post_meta($variation_id, "_aliexpress_regular_price", true);

                            $price = $price ? $price : 0;
                            $regular_price = $regular_price ? $regular_price : 0;
                            
                            $var['price'] = $price ? $price : $regular_price;
                            $var['regular_price'] = $regular_price ? $regular_price : $price;
                            $var['discount'] = $var['regular_price']?100 - round($var['price'] * 100 / $var['regular_price']):0;

                            $price = get_post_meta($variation_id, "_price", true);
                            $regular_price = get_post_meta($variation_id, "_regular_price", true);

                            $price = $price ? $price : 0;
                            $regular_price = $regular_price ? $regular_price : 0;

                            $var['calc_price'] = $price ? $price : $regular_price;
                            $var['calc_regular_price'] = $regular_price ? $regular_price : $price;
                            
                            if ($woocommerce_manage_stock === 'yes') {
                                $var['quantity'] = get_post_meta($variation_id, "_stock", true);
                            } else {
                                $var['quantity'] = get_post_meta($variation_id, '_stock_status', true) === 'outofstock' ? 0 : 1;
                            }

                            $product['sku_products']['variations'][] = $var;
                        }
                    } else {
                        $var = array('id' => $external_id . "-1", 'attributes' => array());
                        if (isset($product['price'])) {
                            $var['price'] = $product['price'];
                        }
                        if (isset($product['regular_price'])) {
                            $var['regular_price'] = $product['regular_price'];
                        }
                        if (isset($product['discount'])) {
                            $var['discount'] = $product['discount'];
                        }
                        if (isset($product['calc_price'])) {
                            $var['calc_price'] = $product['calc_price'];
                        }
                        if (isset($product['calc_regular_price'])) {
                            $var['calc_regular_price'] = $product['calc_regular_price'];
                        }
                        if (isset($product['quantity'])) {
                            $var['quantity'] = $product['quantity'];
                        }

                        $product['sku_products']['variations'][] = $var;
                    }
                }
            }

            return $product;
        }

        public function get_product_id_by_external_id($external_id) {
            global $wpdb;
            return $wpdb->get_var($wpdb->prepare("SELECT post_id FROM $wpdb->postmeta WHERE meta_key='_a2w_external_id' AND meta_value='%s' LIMIT 1", $external_id));
        }

        public function get_product_id_by_import_id($import_id) {
            global $wpdb;
            return $wpdb->get_var($wpdb->prepare("SELECT post_id FROM $wpdb->postmeta WHERE meta_key='_a2w_import_id' AND meta_value='%s' LIMIT 1", $import_id));
        }

        public function get_product_tags($search = '') {
            $tags = get_terms('product_tag', array('search' => $search, 'hide_empty' => false));
            if (is_wp_error($tags)) {
                return array();
            } else {
                $result_tags = array();
                foreach ($tags as $tag) {
                    $result_tags[] = $tag->name;
                }
                return $result_tags;
            }
        }

        public function get_categories() {
            $categories = get_terms("product_cat", array('hide_empty' => 0, 'hierarchical' => true));
            if (is_wp_error($categories)) {
                return array();
            } else {
                $categories = json_decode(json_encode($categories), TRUE);
                $categories = $this->build_categories_tree($categories, 0);
                return $categories;
            }
        }

        private function build_categories_tree($all_cats, $parent_cat, $level = 1) {
            $res = array();
            foreach ($all_cats as $c) {
                if ($c['parent'] == $parent_cat) {
                    $c['level'] = $level;
                    $res[] = $c;
                    $child_cats = $this->build_categories_tree($all_cats, $c['term_id'], $level + 1);
                    if ($child_cats) {
                        $res = array_merge($res, $child_cats);
                    }
                }
            }
            return $res;
        }

    }

}
