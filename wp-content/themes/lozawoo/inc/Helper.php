<?php
include "hooks.php";

function dd($data)
{
    echo "<pre>";
    var_dump($data);
    echo "</pre>";
    die;
}

function get_logo()
{
    $custom_logo_id = get_theme_mod('custom_logo');
    $image = wp_get_attachment_image_src($custom_logo_id, 'full');
    echo $image[0];
}

function image($width = 200, $height = 200)
{
    return resize_image(get_the_post_thumbnail_url(), [$width, $height]);
}

function resize_image($url, $size = [])
{
    $media_url = str_replace(home_url('wp-content/uploads') . '/', '', $url);
    if (!isset($size[0]) || !isset($size[1])) {
        return home_url('wp-content/uploads/') . $media_url;
    }
    $img_url = home_url('wp-content/uploads/vthumb.php?src=' . $media_url . '&size=' . $size[0] . 'x' . $size[1] . '&zoom=1&q=90');
    return $img_url;
}

function getPaginate($qr)
{
    $current_page = max(1, get_query_var('paged'));
    $pagin = paginate_links([
        'total' => $qr->max_num_pages,
        'format' => '?paged=%#%',
        'type' => 'array',
        'prev_text' => "<i class='bx bx-chevron-left'></i>",
        'next_text' => "<i class='bx bx-chevron-right'></i>",
        'current' => $current_page,
        'show_all' => true
    ]);
    $list = $pagin;
    $html = '';

    if ($qr->max_num_pages > 1) {
        $html .= '<div class="pagination-area">';
        $html .= '<ul class="pagination">';
        if ($current_page > 1) {
            $html .= $list[0];
        }


        if ($current_page == $qr->max_num_pages) {
            array_shift($pagin);
        } elseif ($current_page == 1) {
            array_pop($pagin);
        } else {
            array_pop($pagin);
            array_shift($pagin);
        }


        foreach ($pagin as $k => $p) {

            $link = new SimpleXMLElement($p);
            $checked = ($current_page == $k + 1) ? "active" : "";
            $html .= "<li class='" . $checked . "'>";
            $html .= $p;
            $html .= "</li>";
        }
        $html .= '</ul>';

        if ($current_page < $qr->max_num_pages) {
            //$html.= end($list);
        }
        $html .= '</div>';
    }
    echo $html;
}

show_admin_bar(false);

add_filter('walker_nav_menu_start_el', 'add_arrow', 10, 4);
function add_arrow($output, $item, $depth, $args)
{
    if (in_array("menu-item-has-children", $item->classes)) {
        $output = str_replace("</a>", " <i class=\"fa fa-angle-down\"></i>", $output);
    }
    return $output;
}

function strlimit($str = '')
{
    return mb_strimwidth($str, 0, 25, '...');
}


function mwc_get_gallery_image_html($attachment_id, $main_image = false)
{
    $flexslider = (bool)apply_filters('woocommerce_single_product_flexslider_enabled', get_theme_support('wc-product-gallery-slider'));
    $gallery_thumbnail = wc_get_image_size('gallery_thumbnail');
    $thumbnail_size = apply_filters('woocommerce_gallery_thumbnail_size', array($gallery_thumbnail['width'], $gallery_thumbnail['height']));
    $image_size = apply_filters('woocommerce_gallery_image_size', $flexslider || $main_image ? 'woocommerce_single' : $thumbnail_size);
    $full_size = apply_filters('woocommerce_gallery_full_size', apply_filters('woocommerce_product_thumbnails_large_size', 'full'));
    $thumbnail_src = wp_get_attachment_image_src($attachment_id, $thumbnail_size);
    $full_src = wp_get_attachment_image_src($attachment_id, $full_size);
    $alt_text = trim(wp_strip_all_tags(get_post_meta($attachment_id, '_wp_attachment_image_alt', true)));
    $image = wp_get_attachment_image(
        $attachment_id,
        $image_size,
        false,
        apply_filters(
            'woocommerce_gallery_image_html_attachment_image_params',
            array(
                'title' => _wp_specialchars(get_post_field('post_title', $attachment_id), ENT_QUOTES, 'UTF-8', true),
                'data-caption' => _wp_specialchars(get_post_field('post_excerpt', $attachment_id), ENT_QUOTES, 'UTF-8', true),
                'data-src' => esc_url($full_src[0]),
                'data-large_image' => esc_url($full_src[0]),
                'data-large_image_width' => esc_attr($full_src[1]),
                'data-large_image_height' => esc_attr($full_src[2]),
                'class' => esc_attr($main_image ? 'wp-post-image img-fluid' : 'img-fluid'),
            ),
            $attachment_id,
            $image_size,
            $main_image
        )
    );

    return '<div data-thumb="' . esc_url($thumbnail_src[0]) . '" data-thumb-alt="' . esc_attr($alt_text) . '" class="woocommerce-product-gallery__image"><a href="' . esc_url($full_src[0]) . '">' . $image . '</a></div>';
}

function isEmpty($val)
{
    return empty($val) || $val == null || $val == '' || count($val) <= 0;
}

