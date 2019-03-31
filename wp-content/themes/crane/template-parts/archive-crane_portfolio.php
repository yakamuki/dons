<?php defined( 'ABSPATH' ) OR die( 'This script cannot be accessed directly.' );
/**
 * The template for displaying portfolio list pages
 *
 * @link https://codex.wordpress.org/Template_Hierarchy
 * @package crane
 */

get_header();

global $crane_options;

$portfolio_name = isset( $crane_options['portfolio-name'] ) ? $crane_options['portfolio-name'] : esc_html__( 'Portfolio', 'crane' );

$current_page_options = crane_get_options_for_current_page();
$category_options     = [ ];

$sidebar      = $current_page_options['has-sidebar'];
$sidebar_name = $current_page_options['sidebar'];

$term = get_queried_object();

$cat_slug = $tag_slug = $title = '';

if ( isset( $term->term_id ) ) {
	if ( 'crane_portfolio_cats' === $term->taxonomy ) {
		$cat_slug = isset( $term->slug ) ? $term->slug : '';
		$title    = $portfolio_name . ' ' . esc_html__( 'category:', 'crane' ) . ' ' . $term->name;
	} elseif ( 'crane_portfolio_tags' === $term->taxonomy ) {
		$tag_slug = isset( $term->slug ) ? $term->slug : '';
		$title    = $portfolio_name . ' ' . esc_html__( 'tag:', 'crane' ) . ' ' . $term->name;
	}
	$category_options = crane_get_current_category_options( $term->term_id );
} else {
	// portfolio taxonomy archive
	$title = $portfolio_name; // $term->label;
}

$author = isset( $crane_options['portfolio-archive-author'] ) ? $crane_options['portfolio-archive-author'] : '';
if ( is_array( $author ) ) {
	$author = implode( ',', $author );
} else {
	$author = '';
}

$custom_order = isset( $crane_options['portfolio-archive-custom_order'] ) ? $crane_options['portfolio-archive-custom_order'] : '';

if ( class_exists( 'CT_Vc_Portfolio_Config' ) ) {
	$portfolio_shortcode_config = new CT_Vc_Portfolio_Config();
	$shortcode_tagname          = $portfolio_shortcode_config->get_data( 'tag' );
	$config                     = $portfolio_shortcode_config::fields();
} else {
	$shortcode_tagname = 'ct_vc_portfolio';
	$config            = [ ];
}


$shortcode = '[' . $shortcode_tagname;
foreach ( $config as $params ) {

	if ( isset( $crane_options[ 'portfolio-archive-' . $params['param_name'] ] ) ) {
		$param_val = $crane_options[ 'portfolio-archive-' . $params['param_name'] ];
	} elseif ( isset( $category_options[ $params['param_name'] ] ) ) {
		$param_val = $category_options[ $params['param_name'] ];
	} else {
		$param_val = '';
	}

	if ( 'author' === $params['param_name'] ) {
		$param_val = $author;
	}
	if ( 'category' === $params['param_name'] ) {
		$param_val = $cat_slug;
	}
	if ( 'tag' === $params['param_name'] ) {
		$param_val = $tag_slug;
	}
	if ( 'direction_aware_color' === $params['param_name'] ) {
		$param_val = $param_val['rgba'];
	}
	if ( 'show_custom_order' === $params['param_name'] ) {
		$param_val = $custom_order;
	}

	if ( ! $param_val ) {
		$param_val = '';
	}

	$shortcode .= ' ' . $params['param_name'] . '="' . $param_val . '"';
}
$shortcode .= ']';


crane_breadcrumbs( $current_page_options['breadcrumbs'] );

$portfolio_shortcode_html = do_shortcode( $shortcode );

?>
	<div class="crane-container">
		<div class="crane-row-flex">
			<?php if ( $sidebar && 'at-left' === $sidebar ) {
				crane_generate_dynamic_sidebar( $sidebar_name, 'aside' );
			} ?>
			<div class="crane-content-inner portfolio-archive-inner">
				<?php
				if ( $portfolio_shortcode_html ) {
					echo '<h3 class="crane-portfolio-category-title">' . esc_html( $title ) . '</h3>';
					echo crane_clear_echo( $portfolio_shortcode_html );
				} else {
					get_template_part( 'template-parts/content', 'none' );
				}
				?>
			</div>
			<?php if ( $sidebar && 'at-right' === $sidebar ) {
				crane_generate_dynamic_sidebar( $sidebar_name, 'aside' );
			} ?>
		</div>
	</div>
<?php get_footer();
