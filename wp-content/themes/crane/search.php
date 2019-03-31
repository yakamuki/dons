<?php defined( 'ABSPATH' ) OR die( 'This script cannot be accessed directly.' );
/**
 * The template for displaying search results pages.
 *
 * @link https://codex.wordpress.org/Template_Hierarchy
 * @package crane
 */

get_header();

$current_page_options = crane_get_options_for_current_page();

$sidebar      = $current_page_options['has-sidebar'];
$sidebar_name = $current_page_options['sidebar'];

?>
    <div class="crane-container">
        <div class="crane-row-flex">
            <?php if ( $sidebar && 'at-left' === $sidebar ) {
                crane_generate_dynamic_sidebar( $sidebar_name, 'aside' );
            } ?>
            <section class="crane-content-inner blog-inner">
                <?php if ( have_posts() ) { ?>
                    <h1><?php echo esc_html__( 'Search results:', 'crane' ) . ' ' . esc_html( get_search_query( false ) ); ?></h1>
                <?php } ?>
                <div id="content">
                    <?php if ( have_posts() ) {

                        while ( have_posts() ) {
                            the_post();

                            /**
                             * Run the loop for the search to output the results.
                             * If you want to overload this in a child theme then include a file
                             * called content-search.php and that will be used instead.
                             */
                            get_template_part( 'template-parts/content', 'search' );
                        }

                    } else {

                        get_template_part( 'template-parts/content', 'none' );

                    } ?>
                </div>
                <?php crane_the_posts_pagination( 'search' ); ?>
            </section>
            <?php if ( $sidebar && 'at-right' === $sidebar ) {
                crane_generate_dynamic_sidebar( $sidebar_name, 'aside' );
            } ?>
        </div>
    </div>

<?php

get_footer();
