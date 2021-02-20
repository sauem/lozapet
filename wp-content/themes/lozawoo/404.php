<?php
/**
 * The template for displaying 404 pages (not found).
 *
 * @package storefront
 */

get_header(); ?>

    <div class="breadcrumb-area bg-12 text-center">
        <div class="container">
            <h1>404 Error</h1>
            <nav aria-label="breadcrumb">
                <ul class="breadcrumb">
                    <li class="breadcrumb-item"><a href="index.html">Home</a></li>
                    <li class="breadcrumb-item active" aria-current="page">404 Error</li>
                </ul>
            </nav>
        </div>
    </div>
    <div class="error-text-area pt-110 pb-95">
        <div class="container text-center">
            <div class="error-text">
                <h2>OOPS! PAGE NOT BE FOUND</h2>
                <h4>Sorry but the page you are looking for does not exist, have been removed, name changed or is temporarity unavailable.</h4>
                <form action="#" method="post">
                    <input type="text" placeholder="Search...">
                    <button type="button"><i class="fa fa-search"></i></button>
                </form>
                <a href="index.html" class="default-btn">Back to home page</a>
            </div>
        </div>
    </div>
<?php
get_footer();
