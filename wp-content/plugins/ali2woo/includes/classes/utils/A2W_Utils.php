<?php

/**
 * Description of A2W_Utils
 *
 * @author Andrey
 */
if (!class_exists('A2W_Utils')) {

    class A2W_Utils {

        public static function wcae_strack_active() {
            return is_plugin_active('woocommerce_aliexpress_shipment_tracking/index.php');
        }

        public static function update_post_terms_count($post_id) {
            global $wpdb;
            $update_taxonomies = array();
            $res = $wpdb->get_results("SELECT tt.taxonomy,tt.term_taxonomy_id,tt.term_id FROM {$wpdb->term_relationships} tr LEFT JOIN {$wpdb->term_taxonomy} tt ON (tr.term_taxonomy_id=tt.term_taxonomy_id) WHERE tr.object_id=".absint($post_id), ARRAY_A);
            foreach($res as $row){
                if(!isset($update_taxonomies[$row['taxonomy']])){
                    $update_taxonomies[$row['taxonomy']] = array();
                }
                $update_taxonomies[$row['taxonomy']][] = in_array($row['taxonomy'],array('product_cat','product_tag'))?$row['term_taxonomy_id']:$row['term_id'];
            }

            foreach($update_taxonomies as $taxonomy=>$terms){
                wp_update_term_count_now($terms, $taxonomy);
            }
        }

        public static function clear_url($url) {
            if ($url) {
                $parts = parse_url($url);
                $res = '';
                if (isset($parts['scheme'])) {
                    $res .= $parts['scheme'] . '://';
                }
                if (isset($parts['host'])) {
                    $res .= $parts['host'];
                }
                if (isset($parts['path'])) {
                    $res .= $parts['path'];
                }
                return $res;
            }
            return '';
        }

        /**
         * Get size information for all currently-registered image sizes.
         * List available image sizes with width and height following
         *
         * @global $_wp_additional_image_sizes
         * @uses   get_intermediate_image_sizes()
         * @return array $sizes Data for all currently-registered image sizes.
         */
        public function get_image_sizes() {
            global $_wp_additional_image_sizes;

            $sizes = array();

            foreach (get_intermediate_image_sizes() as $_size) {
                if (in_array($_size, array('thumbnail', 'medium', 'medium_large', 'large'))) {
                    $sizes[$_size]['width'] = get_option("{$_size}_size_w");
                    $sizes[$_size]['height'] = get_option("{$_size}_size_h");
                    $sizes[$_size]['crop'] = (bool) get_option("{$_size}_crop");
                } elseif (isset($_wp_additional_image_sizes[$_size])) {
                    $sizes[$_size] = array(
                        'width' => $_wp_additional_image_sizes[$_size]['width'],
                        'height' => $_wp_additional_image_sizes[$_size]['height'],
                        'crop' => $_wp_additional_image_sizes[$_size]['crop'],
                    );
                }
            }

            return $sizes;
        }

        /**
         * Get size information for a specific image size.
         *
         * @uses   get_image_sizes()
         * @param  string $size The image size for which to retrieve data.
         * @return bool|array $size Size data about an image size or false if the size doesn't exist.
         */
        public function get_image_size($size) {
            $sizes = get_image_sizes();

            if (isset($sizes[$size])) {
                return $sizes[$size];
            }

            return false;
        }

        /**
         * Get the width of a specific image size.
         *
         * @uses   get_image_size()
         * @param  string $size The image size for which to retrieve data.
         * @return bool|string $size Width of an image size or false if the size doesn't exist.
         */
        public function get_image_width($size) {
            if (!$size = get_image_size($size)) {
                return false;
            }

            if (isset($size['width'])) {
                return $size['width'];
            }

            return false;
        }

        /**
         * Get the height of a specific image size.
         *
         * @uses   get_image_size()
         * @param  string $size The image size for which to retrieve data.
         * @return bool|string $size Height of an image size or false if the size doesn't exist.
         */
        public function get_image_height($size) {
            if (!$size = get_image_size($size)) {
                return false;
            }

            if (isset($size['height'])) {
                return $size['height'];
            }

            return false;
        }

        public static function delete_post_images($post_id) {
            global $wpdb;
            $external_id = get_post_meta($post_id, '_a2w_external_id', true);
            $post_type = get_post_type($post_id);
            if ($external_id || $post_type == 'product_variation') {
                $childrens = $wpdb->get_col($wpdb->prepare("SELECT ID FROM $wpdb->posts WHERE post_parent = %d and post_type='attachment'", $post_id));
                if ($childrens) {
                    foreach ($childrens as $attachment_id) {
                        A2W_Utils::delete_attachment($attachment_id, true);
                    }
                }
                $thumbnail_id = get_post_meta($post_id, '_thumbnail_id', true);
                if ($thumbnail_id && $post_type != 'product_variation') {
                    A2W_Utils::delete_attachment($attachment_id, true);
                }
            }
        }

        // cloned wp_delete_attachment function, use for overload sites
        public static function delete_attachment( $post_id, $force_delete = false ) {
            if (a2w_check_defined('A2W_USE_DEFAULT_DELETE_ATTACHMENT')) {
                return wp_delete_attachment($post_id, $force_delete);
            }
            
            global $wpdb;
            
            $post = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $wpdb->posts WHERE ID = %d", $post_id ) );

            if ( ! $post ) {
                    return $post;
            }

            $post = get_post( $post );
            
            

            if ( 'attachment' !== $post->post_type ) {
                    return false;
            }

            if ( ! $force_delete && EMPTY_TRASH_DAYS && MEDIA_TRASH && 'trash' !== $post->post_status ) {
                    return wp_trash_post( $post_id );
            }

            delete_post_meta($post_id, '_wp_trash_meta_status');
            delete_post_meta($post_id, '_wp_trash_meta_time');
            
            $meta = wp_get_attachment_metadata( $post_id );
            $backup_sizes = get_post_meta( $post->ID, '_wp_attachment_backup_sizes', true );
            $file = get_attached_file( $post_id );

            if ( is_multisite() )
                    delete_transient( 'dirsize_cache' );
            /**
             * Fires before an attachment is deleted, at the start of wp_delete_attachment().
             *
             * @since 2.0.0
             *
             * @param int $post_id Attachment ID.
             */
            do_action( 'delete_attachment', $post_id );
            
            wp_delete_object_term_relationships($post_id, array('category', 'post_tag'));
            wp_delete_object_term_relationships($post_id, get_object_taxonomies($post->post_type));

            // Delete all for any posts.
            // A2W disable this call!
            // delete_metadata( 'post', null, '_thumbnail_id', $post_id, true );

            wp_defer_comment_counting( true );

            $comment_ids = $wpdb->get_col( $wpdb->prepare( "SELECT comment_ID FROM $wpdb->comments WHERE comment_post_ID = %d", $post_id ));
            foreach ( $comment_ids as $comment_id ) {
                    wp_delete_comment( $comment_id, true );
            }

            wp_defer_comment_counting( false );

            $post_meta_ids = $wpdb->get_col( $wpdb->prepare( "SELECT meta_id FROM $wpdb->postmeta WHERE post_id = %d ", $post_id ));
            foreach ( $post_meta_ids as $mid )
                    delete_metadata_by_mid( 'post', $mid );

            /** This action is documented in wp-includes/post.php */
            do_action( 'delete_post', $post_id );
            $result = $wpdb->delete( $wpdb->posts, array( 'ID' => $post_id ) );
            if ( ! $result ) {
                    return false;
            }
            /** This action is documented in wp-includes/post.php */
            do_action( 'deleted_post', $post_id );

            wp_delete_attachment_files( $post_id, $meta, $backup_sizes, $file );

            clean_post_cache( $post );
            
            return $post;
        }

        public static function clear_image_url($img_url, $param_str = '') {
            //$img_url = str_replace("http:", "https:", $img_url);
            if (substr($img_url, 0, 4) !== "http") {
                $new_src = "https:" . $img_url;
            } else {
                $new_src = $img_url;
            }

            $parsed_url = parse_url($img_url);

            if (!empty($parsed_url['scheme']) && !empty($parsed_url['host']) && !empty($parsed_url['path'])) {
                $new_src = $parsed_url['scheme'] . '://' . $parsed_url['host'] . $parsed_url['path'] . $param_str;
            } else if (empty($parsed_url['scheme']) && empty($parsed_url['host']) && !empty($parsed_url['path'])) {
                $new_src = $parsed_url['path'] . $param_str;
            }
            return $new_src;
        }

        public static function get_all_images_from_product($product, $skip = false) {
            $tmp_all_images = array();

            foreach ($product['images'] as $img) {
                $img_id = md5($img);
                if (!isset($tmp_all_images[$img_id])) {
                    $tmp_all_images[$img_id] = array('image' => $img, 'type' => 'gallery');
                }
            }

            if (!empty($product['sku_products']['attributes'])) {
                foreach ($product['sku_products']['attributes'] as $attribute) {
                    foreach ($attribute['value'] as $attr_value) {
                        $has_variation = false;
                        foreach ($product['sku_products']['variations'] as $variation) {
                            if (!empty($product['skip_vars']) && !in_array($variation['id'], $product['skip_vars'])) {
                                foreach ($variation['attributes'] as $va) {
                                    if ($va == $attr_value['id']) {
                                        $has_variation = true;
                                    }
                                }
                            }
                        }

                        if ($has_variation) {
                            if (a2w_get_setting('use_external_image_urls')){
                                if (!empty($attr_value['thumb'])) {
                                    $img_id = md5($attr_value['thumb']);
                                    if (!isset($tmp_all_images[$img_id])) {
                                        $tmp_all_images[$img_id] = array('image' => $attr_value['thumb'], 'type' => 'attribute');
                                    }
                                } else if (!empty($attr_value['image'])) { 
                                    $img_id = md5($attr_value['image']);
                                    if (!isset($tmp_all_images[$img_id])) {
                                        $tmp_all_images[$img_id] = array('image' => $attr_value['image'], 'type' => 'attribute');
                                    }
                                } 
                            } else{
                                if (!empty($attr_value['image'])) { 
                                    $img_id = md5($attr_value['image']);
                                    if (!isset($tmp_all_images[$img_id])) {
                                        $tmp_all_images[$img_id] = array('image' => $attr_value['image'], 'type' => 'attribute');
                                    }
                                } else if (!empty($attr_value['thumb'])) {
                                    $img_id = md5($attr_value['thumb']);
                                    if (!isset($tmp_all_images[$img_id])) {
                                        $tmp_all_images[$img_id] = array('image' => $attr_value['thumb'], 'type' => 'attribute');
                                    }
                                }
                            }
                        }
                    }
                }
            }

            if (!empty($product['sku_products']['variations'])) {
                foreach ($product['sku_products']['variations'] as $var) {
                    if (isset($var['image'])) {
                        $img_id = md5($var['image']);
                        if (!isset($tmp_all_images[$img_id])) {
                            $tmp_all_images[$img_id] = array('image' => $var['image'], 'type' => 'variant');
                        }
                    }
                }
            }

            if (!empty($product['description'])) {
                $desc_images = A2W_Utils::get_images_from_description($product['description']);
                foreach ($desc_images as $img_id => $img) {
                    if (!isset($tmp_all_images[$img_id])) {
                        $tmp_all_images[$img_id] = array('image' => $img, 'type' => 'description');
                    } 
                }
            }

            if($skip){
                foreach($product['skip_images'] as $img_id){
                    unset($tmp_all_images[$img_id]);
                }
            }

            return $tmp_all_images;
        }

        public static function get_images_from_description($description) {
            $src_result = array();

            if(class_exists('DOMDocument')) {
                $description = htmlspecialchars_decode(utf8_decode(htmlentities($description, ENT_COMPAT, 'UTF-8', false)));

                if (function_exists('libxml_use_internal_errors')) {
                    libxml_use_internal_errors(true);
                }
                $dom = new DOMDocument();
                @$dom->loadHTML($description);
                $dom->formatOutput = true;
                $tags = $dom->getElementsByTagName('img');

                
                foreach ($tags as $tag) {
                    $src_result[md5($tag->getAttribute('src'))] = $tag->getAttribute('src');
                }
            }

            return $src_result;
        }

        public static function normalizeChars($s) {
            $replace = array(
                'ъ' => '-', 'Ь' => '-', 'Ъ' => '-', 'ь' => '-',
                'Ă' => 'A', 'Ą' => 'A', 'À' => 'A', 'Ã' => 'A', 'Á' => 'A', 'Æ' => 'A', 'Â' => 'A', 'Å' => 'A', 'Ä' => 'Ae',
                'Þ' => 'B',
                'Ć' => 'C', 'ץ' => 'C', 'Ç' => 'C',
                'È' => 'E', 'Ę' => 'E', 'É' => 'E', 'Ë' => 'E', 'Ê' => 'E',
                'Ğ' => 'G',
                'İ' => 'I', 'Ï' => 'I', 'Î' => 'I', 'Í' => 'I', 'Ì' => 'I',
                'Ł' => 'L',
                'Ñ' => 'N', 'Ń' => 'N',
                'Ø' => 'O', 'Ó' => 'O', 'Ò' => 'O', 'Ô' => 'O', 'Õ' => 'O', 'Ö' => 'Oe',
                'Ş' => 'S', 'Ś' => 'S', 'Ș' => 'S', 'Š' => 'S',
                'Ț' => 'T',
                'Ù' => 'U', 'Û' => 'U', 'Ú' => 'U', 'Ü' => 'Ue',
                'Ý' => 'Y',
                'Ź' => 'Z', 'Ž' => 'Z', 'Ż' => 'Z',
                'â' => 'a', 'ǎ' => 'a', 'ą' => 'a', 'á' => 'a', 'ă' => 'a', 'ã' => 'a', 'Ǎ' => 'a', 'а' => 'a', 'А' => 'a', 'å' => 'a', 'à' => 'a', 'א' => 'a', 'Ǻ' => 'a', 'Ā' => 'a', 'ǻ' => 'a', 'ā' => 'a', 'ä' => 'ae', 'æ' => 'ae', 'Ǽ' => 'ae', 'ǽ' => 'ae',
                'б' => 'b', 'ב' => 'b', 'Б' => 'b', 'þ' => 'b',
                'ĉ' => 'c', 'Ĉ' => 'c', 'Ċ' => 'c', 'ć' => 'c', 'ç' => 'c', 'ц' => 'c', 'צ' => 'c', 'ċ' => 'c', 'Ц' => 'c', 'Č' => 'c', 'č' => 'c', 'Ч' => 'ch', 'ч' => 'ch',
                'ד' => 'd', 'ď' => 'd', 'Đ' => 'd', 'Ď' => 'd', 'đ' => 'd', 'д' => 'd', 'Д' => 'D', 'ð' => 'd',
                'є' => 'e', 'ע' => 'e', 'е' => 'e', 'Е' => 'e', 'Ə' => 'e', 'ę' => 'e', 'ĕ' => 'e', 'ē' => 'e', 'Ē' => 'e', 'Ė' => 'e', 'ė' => 'e', 'ě' => 'e', 'Ě' => 'e', 'Є' => 'e', 'Ĕ' => 'e', 'ê' => 'e', 'ə' => 'e', 'è' => 'e', 'ë' => 'e', 'é' => 'e',
                'ф' => 'f', 'ƒ' => 'f', 'Ф' => 'f',
                'ġ' => 'g', 'Ģ' => 'g', 'Ġ' => 'g', 'Ĝ' => 'g', 'Г' => 'g', 'г' => 'g', 'ĝ' => 'g', 'ğ' => 'g', 'ג' => 'g', 'Ґ' => 'g', 'ґ' => 'g', 'ģ' => 'g',
                'ח' => 'h', 'ħ' => 'h', 'Х' => 'h', 'Ħ' => 'h', 'Ĥ' => 'h', 'ĥ' => 'h', 'х' => 'h', 'ה' => 'h',
                'î' => 'i', 'ï' => 'i', 'í' => 'i', 'ì' => 'i', 'į' => 'i', 'ĭ' => 'i', 'ı' => 'i', 'Ĭ' => 'i', 'И' => 'i', 'ĩ' => 'i', 'ǐ' => 'i', 'Ĩ' => 'i', 'Ǐ' => 'i', 'и' => 'i', 'Į' => 'i', 'י' => 'i', 'Ї' => 'i', 'Ī' => 'i', 'І' => 'i', 'ї' => 'i', 'і' => 'i', 'ī' => 'i', 'ĳ' => 'ij', 'Ĳ' => 'ij',
                'й' => 'j', 'Й' => 'j', 'Ĵ' => 'j', 'ĵ' => 'j', 'я' => 'ja', 'Я' => 'ja', 'Э' => 'je', 'э' => 'je', 'ё' => 'jo', 'Ё' => 'jo', 'ю' => 'ju', 'Ю' => 'ju',
                'ĸ' => 'k', 'כ' => 'k', 'Ķ' => 'k', 'К' => 'k', 'к' => 'k', 'ķ' => 'k', 'ך' => 'k',
                'Ŀ' => 'l', 'ŀ' => 'l', 'Л' => 'l', 'ł' => 'l', 'ļ' => 'l', 'ĺ' => 'l', 'Ĺ' => 'l', 'Ļ' => 'l', 'л' => 'l', 'Ľ' => 'l', 'ľ' => 'l', 'ל' => 'l',
                'מ' => 'm', 'М' => 'm', 'ם' => 'm', 'м' => 'm',
                'ñ' => 'n', 'н' => 'n', 'Ņ' => 'n', 'ן' => 'n', 'ŋ' => 'n', 'נ' => 'n', 'Н' => 'n', 'ń' => 'n', 'Ŋ' => 'n', 'ņ' => 'n', 'ŉ' => 'n', 'Ň' => 'n', 'ň' => 'n',
                'о' => 'o', 'О' => 'o', 'ő' => 'o', 'õ' => 'o', 'ô' => 'o', 'Ő' => 'o', 'ŏ' => 'o', 'Ŏ' => 'o', 'Ō' => 'o', 'ō' => 'o', 'ø' => 'o', 'ǿ' => 'o', 'ǒ' => 'o', 'ò' => 'o', 'Ǿ' => 'o', 'Ǒ' => 'o', 'ơ' => 'o', 'ó' => 'o', 'Ơ' => 'o', 'œ' => 'oe', 'Œ' => 'oe', 'ö' => 'oe',
                'פ' => 'p', 'ף' => 'p', 'п' => 'p', 'П' => 'p',
                'ק' => 'q',
                'ŕ' => 'r', 'ř' => 'r', 'Ř' => 'r', 'ŗ' => 'r', 'Ŗ' => 'r', 'ר' => 'r', 'Ŕ' => 'r', 'Р' => 'r', 'р' => 'r',
                'ș' => 's', 'с' => 's', 'Ŝ' => 's', 'š' => 's', 'ś' => 's', 'ס' => 's', 'ş' => 's', 'С' => 's', 'ŝ' => 's', 'Щ' => 'sch', 'щ' => 'sch', 'ш' => 'sh', 'Ш' => 'sh', 'ß' => 'ss',
                'т' => 't', 'ט' => 't', 'ŧ' => 't', 'ת' => 't', 'ť' => 't', 'ţ' => 't', 'Ţ' => 't', 'Т' => 't', 'ț' => 't', 'Ŧ' => 't', 'Ť' => 't', '™' => 'tm',
                'ū' => 'u', 'у' => 'u', 'Ũ' => 'u', 'ũ' => 'u', 'Ư' => 'u', 'ư' => 'u', 'Ū' => 'u', 'Ǔ' => 'u', 'ų' => 'u', 'Ų' => 'u', 'ŭ' => 'u', 'Ŭ' => 'u', 'Ů' => 'u', 'ů' => 'u', 'ű' => 'u', 'Ű' => 'u', 'Ǖ' => 'u', 'ǔ' => 'u', 'Ǜ' => 'u', 'ù' => 'u', 'ú' => 'u', 'û' => 'u', 'У' => 'u', 'ǚ' => 'u', 'ǜ' => 'u', 'Ǚ' => 'u', 'Ǘ' => 'u', 'ǖ' => 'u', 'ǘ' => 'u', 'ü' => 'ue',
                'в' => 'v', 'ו' => 'v', 'В' => 'v',
                'ש' => 'w', 'ŵ' => 'w', 'Ŵ' => 'w',
                'ы' => 'y', 'ŷ' => 'y', 'ý' => 'y', 'ÿ' => 'y', 'Ÿ' => 'y', 'Ŷ' => 'y',
                'Ы' => 'y', 'ž' => 'z', 'З' => 'z', 'з' => 'z', 'ź' => 'z', 'ז' => 'z', 'ż' => 'z', 'ſ' => 'z', 'Ж' => 'zh', 'ж' => 'zh'
            );
            return strtr($s, $replace);
        }
              
        public static function safeTransliterate ($text) {
            
            //for cyrilic first:
            $cyr = array(
                'ж',  'ч',  'щ',   'ш',  'ю',  'а', 'б', 'в', 'г', 'д', 'е', 'з', 'и', 'й', 'к', 'л', 'м', 'н', 'о', 'п', 'р', 'с', 'т', 'у', 'ф', 'х', 'ц', 'ъ', 'ь', 'я',
                'Ж',  'Ч',  'Щ',   'Ш',  'Ю',  'А', 'Б', 'В', 'Г', 'Д', 'Е', 'З', 'И', 'Й', 'К', 'Л', 'М', 'Н', 'О', 'П', 'Р', 'С', 'Т', 'У', 'Ф', 'Х', 'Ц', 'Ъ', 'Ь', 'Я');
                $lat = array(
                'zh', 'ch', 'sht', 'sh', 'yu', 'a', 'b', 'v', 'g', 'd', 'e', 'z', 'i', 'j', 'k', 'l', 'm', 'n', 'o', 'p', 'r', 's', 't', 'u', 'f', 'h', 'c', 'y', 'x', 'q',
                'Zh', 'Ch', 'Sht', 'Sh', 'Yu', 'A', 'B', 'V', 'G', 'D', 'E', 'Z', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'R', 'S', 'T', 'U', 'F', 'H', 'c', 'Y', 'X', 'Q');
               $text = str_replace($cyr, $lat, $text);
               
               //for Brasilian and Arabic languages:
       
            //if available, this function uses PHP5.4's transliterate, which is capable of converting arabic, hebrew, greek,
            //chinese, japanese and more into ASCII! however, we use our manual (and crude) fallback *first* instead because
            //we will take the liberty of transliterating some things into more readable ASCII-friendly forms,
            //e.g. "100℃" > "100degc" instead of "100oc"
            
            /* manual transliteration list:
               -------------------------------------------------------------------------------------------------------------- */
            /* this list is supposed to be practical, not comprehensive, representing:
               1. the most common accents and special letters that get typed, and
               2. the most practical transliterations for readability;
               
               this data was produced with the help of:
               http://www.unicode.org/charts/normalization/
               http://www.yuiblog.com/sandbox/yui/3.3.0pr3/api/text-data-accentfold.js.html
               http://www.utf8-chartable.de/
            */
            static $translit = array (
                'a'    => '/[ÀÁÂẦẤẪẨÃĀĂẰẮẴȦẲǠẢÅÅǺǍȀȂẠẬẶḀĄẚàáâầấẫẩãāăằắẵẳȧǡảåǻǎȁȃạậặḁą]/u',
                'b'    => '/[ḂḄḆḃḅḇ]/u',            'c'    => '/[ÇĆĈĊČḈçćĉċčḉ]/u',
                'd'    => '/[ÐĎḊḌḎḐḒďḋḍḏḑḓð]/u',
                'e'    => '/[ÈËĒĔĖĘĚȄȆȨḔḖḘḚḜẸẺẼẾỀỂỄỆèëēĕėęěȅȇȩḕḗḙḛḝẹẻẽếềểễệ]/u',
                'f'    => '/[Ḟḟ]/u',                'g'    => '/[ĜĞĠĢǦǴḠĝğġģǧǵḡ]/u',
                'h'    => '/[ĤȞḢḤḦḨḪĥȟḣḥḧḩḫẖ]/u',        'i'    => '/[ÌÏĨĪĬĮİǏȈȊḬḮỈỊiìïĩīĭįǐȉȋḭḯỉị]/u',
                'j'    => '/[Ĵĵǰ]/u',                'k'    => '/[ĶǨḰḲḴKķǩḱḳḵ]/u',
                'l'    => '/[ĹĻĽĿḶḸḺḼĺļľŀḷḹḻḽ]/u',        'm'    => '/[ḾṀṂḿṁṃ]/u',
                'n'    => '/[ÑŃŅŇǸṄṆṈṊñńņňǹṅṇṉṋ]/u',
                'o'    => '/[ÒÖŌŎŐƠǑǪǬȌȎȪȬȮȰṌṎṐṒỌỎỐỒỔỖỘỚỜỞỠỢØǾòöōŏőơǒǫǭȍȏȫȭȯȱṍṏṑṓọỏốồổỗộớờởỡợøǿ]/u',
                'p'    => '/[ṔṖṕṗ]/u',                'r'    => '/[ŔŖŘȐȒṘṚṜṞŕŗřȑȓṙṛṝṟ]/u',
                's'    => '/[ŚŜŞŠȘṠṢṤṦṨſśŝşšșṡṣṥṧṩ]/u',    'ss'    => '/[ß]/u',
                't'    => '/[ŢŤȚṪṬṮṰţťțṫṭṯṱẗ]/u',        'th'    => '/[Þþ]/u',
                'u'    => '/[ÙŨŪŬŮŰŲƯǓȔȖṲṴṶṸṺỤỦỨỪỬỮỰùũūŭůűųưǔȕȗṳṵṷṹṻụủứừửữựµ]/u',
                'v'    => '/[ṼṾṽṿ]/u',                'w'    => '/[ŴẀẂẄẆẈŵẁẃẅẇẉẘ]/u',
                'x'    => '/[ẊẌẋẍ×]/u',            'y'    => '/[ÝŶŸȲẎỲỴỶỸýÿŷȳẏẙỳỵỷỹ]/u',
                'z'    => '/[ŹŻŽẐẒẔźżžẑẓẕ]/u',                
                //combined letters and ligatures:
                'ae'    => '/[ÄǞÆǼǢäǟæǽǣ]/u',            'oe'    => '/[Œœ]/u',
                'dz'    => '/[ǄǅǱǲǆǳ]/u',
                'ff'    => '/[ﬀ]/u',    'fi'    => '/[ﬃﬁ]/u',    'ffl'    => '/[ﬄﬂ]/u',
                'ij'    => '/[Ĳĳ]/u',    'lj'    => '/[Ǉǈǉ]/u',    'nj'    => '/[Ǌǋǌ]/u',
                'st'    => '/[ﬅﬆ]/u',    'ue'    => '/[ÜǕǗǙǛüǖǘǚǜ]/u',
                //currencies:
                'eur'   => '/[€]/u',    'cents'    => '/[¢]/u',    'lira'    => '/[₤]/u',    'dollars' => '/[$]/u',
                'won'    => '/[₩]/u',    'rs'    => '/[₨]/u',    'yen'    => '/[¥]/u',    'pounds'  => '/[£]/u',
                'pts'    => '/[₧]/u',
                //misc:
                'degc'    => '/[℃]/u',    'degf'  => '/[℉]/u',
                'no'    => '/[№]/u',    'tm'    => '/[™]/u'
            );
            //do the manual transliteration first
            $text = preg_replace (array_values ($translit), array_keys ($translit), $text);
            
            //flatten the text down to just a-z0-9 and dash, with underscores instead of spaces
            $text = preg_replace (
                //remove punctuation    //replace non a-z    //deduplicate    //trim underscores from start & end
                array (/*'/\p{P}/u',*/    /*'/[^_a-z0-9-]/i',*/     '/_{2,}/',    '/^_|_$/'),
                array (/*'',*/      /*  '_', */          '_',        ''),
                
                //attempt transliteration with PHP5.4's transliteration engine (best):
                //(this method can handle near anything, including converting chinese and arabic letters to ASCII.
                // requires the 'intl' extension to be enabled)
                function_exists ('transliterator_transliterate') ? transliterator_transliterate (
                    //split unicode accents and symbols, e.g. "Å" > "A°":
                    'NFKD; '.
                    //convert everything to the Latin charset e.g. "ま" > "ma":
                    //(splitting the unicode before transliterating catches some complex cases,
                    // such as: "㏳" >NFKD> "20日" >Latin> "20ri")
                    'Latin; '.
                    //because the Latin unicode table still contains a large number of non-pure-A-Z glyphs (e.g. "œ"),
                    //convert what remains to an even stricter set of characters, the US-ASCII set:
                    //(we must do this because "Latin/US-ASCII" alone is not able to transliterate non-Latin characters
                    // such as "ま". this two-stage method also means we catch awkward characters such as:
                    // "㏀" >Latin> "kΩ" >Latin/US-ASCII> "kO")
                    'Latin/US-ASCII; '.
                    //remove the now stand-alone diacritics from the string
                    '[:Nonspacing Mark:] Remove; ',
                $text)
                
                //attempt transliteration with iconv: <php.net/manual/en/function.iconv.php>
                : (function_exists ('iconv') ? str_replace (array ("'", '"', '`', '^', '~'), '', 
                    //note: results of this are different depending on iconv version,
                    //      sometimes the diacritics are written to the side e.g. "ñ" = "~n", which are removed
                    iconv ('UTF-8', 'US-ASCII//IGNORE//TRANSLIT', $text)
                ) : $text)
            );
            
            //old iconv versions and certain inputs may cause a nullstring. don't allow a blank response
            return !$text ? '_' : $text;
        }

    }

}
