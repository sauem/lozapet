<?php
/**
 * The header for our theme.
 *
 * Displays all of the <head> section and everything up till <div id="content">
 *
 * @package storefront
 */

?><!doctype html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=2.0">
    <link rel="profile" href="http://gmpg.org/xfn/11">
    <link rel="pingback" href="<?php bloginfo('pingback_url'); ?>">

    <?php wp_head(); ?>

    <link rel="shortcut icon" type="image/x-icon" href="<?= get_site_icon_url() ?>">

    <!-- All css here -->
    <link rel="stylesheet" href="<?= ASSET ?>/css/bootstrap.min.css">
    <link rel="stylesheet" href="<?= ASSET ?>/css/font-awesome.min.css">
    <link rel="stylesheet" href="<?= ASSET ?>/css/ie7.css">
    <link rel="stylesheet" href="<?= ASSET ?>/css/plugins.css">
    <link rel="stylesheet" href="<?= ASSET ?>/css/style.css">
    <link rel="stylesheet" href="<?= ASSET ?>/css/custome.css?v=1.<?= time() ?>">
    <script src="<?= ASSET ?>/js/vendor/modernizr-3.5.0.min.js"></script>
</head>

<body <?php body_class(); ?>>

<?php wp_body_open(); ?>
<?php get_template_part('parts/nav') ?>
<?php
