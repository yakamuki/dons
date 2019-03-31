<?php defined( 'ABSPATH' ) OR die( 'This script cannot be accessed directly.' );
/**
 * Helper functions.
 *
 * @package crane
 */

if ( ! function_exists( 'crane_get_meta_data' ) ) {
	/**
	 * Get meta data for current page
	 */
	function crane_get_meta_data() {

		static $Crane_Meta_Data = null;

		if ( ! empty( $Crane_Meta_Data ) ) {
			return $Crane_Meta_Data;
		}

		if ( class_exists( 'Crane_Meta_Data' ) ) {
			$Crane_Meta_Data = new Crane_Meta_Data();
			$Crane_Meta_Data->init();
		}

		return $Crane_Meta_Data;
	}
}

if ( ! function_exists( 'crane_is_theme_preview' ) ) {
	/**
	 * Check is theme preview mode
	 *
	 * @param bool|mixed $change_to
	 *
	 * @return bool|mixed
	 */
	function crane_is_theme_preview( $change_to = null ) {

		static $is_theme_preview = null;

		if ( $change_to !== null ) {
			$is_theme_preview = $change_to;
		}

		return $is_theme_preview;
	}
}


if ( ! function_exists( 'crane_breadcrumbs' ) ) {

	/**
	 * Crane theme breadcrumb and title
	 *
	 * @param string $breadcrumbs_type type from theme options. Inspect 'none', 'title', 'breadcrumbs', 'both_before', 'both_within', 'both_after'
	 */
	function crane_breadcrumbs( $breadcrumbs_type = 'both_within' ) {
		global $crane_options;
		$Crane_Meta_Data    = crane_get_meta_data();
		$post_id            = get_the_ID();
		$breadcrumbs_output = '';

		$current_page_options = crane_get_options_for_current_page();
		$breadcrumbs_view     = esc_attr( $current_page_options['breadcrumbs_view'] );
		if ( empty( $breadcrumbs_type ) ) {
			$breadcrumbs_type = esc_attr( $current_page_options['breadcrumbs'] );
		}

		if ( 'none' === $breadcrumbs_type ) {
			return;
		}

		$text_title       = the_title( '', '', false );
		$breadcrumbs_text = isset( $crane_options['breadcrumbs-text'] ) ? wp_kses_post( $crane_options['breadcrumbs-text'] ) : '';
		if ( is_single() || is_page() ) {
			if ( in_array( $breadcrumbs_type, [ 'both_within', 'both_before', 'both_after', 'title' ] ) ) {
				if ( $Crane_Meta_Data->get( 'override_global', $post_id ) ) {
					$text_title = wp_kses_post( trim( $Crane_Meta_Data->get( 'title', $post_id ) ) );
					if ( empty( $text_title ) || ! $text_title ) {
						$text_title = crane_get_current_archive_title();
					}
				}
			}
		} else {
			$text_title = crane_get_current_archive_title();
		}


		if ( empty( $text_title ) ) {
			$text_title = get_the_date( '', $post_id );
		}

		$text['home']     = esc_html__( 'Home', 'crane' ); // text for the 'Home' link
		$text['category'] = '%s'; // text for a category page
		$text['tax']      = '%s'; // text for a taxonomy page
		$text['search']   = esc_html__( 'Search Results for "%s" Query', 'crane' ); // text for a search results page
		$text['tag']      = esc_html__( 'Posts Tagged "%s"', 'crane' ); // text for a tag page
		$text['author']   = esc_html__( 'Articles Posted by %s', 'crane' ); // text for an author page
		$text['404']      = esc_html__( 'Error 404', 'crane' ); // text for the 404 page

		$show_current = 1; // 1 - show current post/page title in breadcrumbs, 0 - don't show
		$show_on_home = 1; // 1 - show breadcrumbs on the homepage, 0 - don't show
		$delimiter    = ''; // delimiter between crumbs
		$before       = '<li class="crane-breadcrumb-nav__item">'; // tag before the current crumb
		$after        = '</li>'; // tag after the current crumb

		global $post;
		$home_link   = home_url() . '/';
		$link_before = '<li class="crane-breadcrumb-nav__item">';
		$link_after  = '</li>';
		$link_attr   = ' class="crane-breadcrumb-nav__link"';
		$link        = $link_before . '<a' . $link_attr . ' href="%1$s">%2$s</a>' . $link_after;
		$link_clean  = '<a' . $link_attr . ' href="%1$s">%2$s</a>';

		if ( in_array( $breadcrumbs_type, [ 'both_within', 'both_before', 'both_after', 'breadcrumbs' ] ) ) {
			$breadcrumbs_output .= '<div class="crane-breadcrumb">';
			$breadcrumbs_output .= '	<div class="crane-container">';
			$breadcrumbs_output .= '		<div class="crane-row-flex">';
			if ( ! empty( $breadcrumbs_text ) && 'both_within' !== $breadcrumbs_type ) {
				$breadcrumbs_output .= '			<div class="crane-breadcrumb-title">' . $breadcrumbs_text . '</div>';
			}
			$breadcrumbs_output .= '			<ul class="crane-breadcrumb-nav">';
			if ( is_home() || is_front_page() ) {

				if ( $show_on_home === 1 ) {
					$breadcrumbs_output .= $link_before .'<a href="' . $home_link . '" ' . $link_attr . '>' . $text['home'] . '</a>' . $link_after;
				}

				if ( is_home() && ! is_front_page() ) {
					$blog_page_id = get_option( 'page_for_posts' );
					if ( $blog_page_id ) {
						$post_title = get_the_title( $blog_page_id ) ? : esc_html_x( 'Blog', 'For breadcrumbs archive link. Use if blog page title is empty.', 'crane' );
						$breadcrumbs_output .= $link_before . $post_title . $link_after;
					}
				}

			} else {

				$breadcrumbs_output .= sprintf( $link, $home_link, $text['home'] ) . $delimiter;

				$breadcrumbs_output .= crane_breadcrumbs_middle_blog_link( $link );

				if ( class_exists( 'WooCommerce' ) && class_exists( 'WC_Breadcrumb' ) && ( is_woocommerce() || is_shop() ) ) {

					$shop_home_link = get_post_type_archive_link( 'product' );
					$shop_home_name = wc_get_page_id( 'shop' ) ? get_the_title( wc_get_page_id( 'shop' ) ) : '';
					$home_page_id   = get_option( 'page_on_front' );
					$shop_page_id   = get_option( 'woocommerce_shop_page_id' );


					if ( crane_is_shop_search() ) {
						if ( $home_page_id !== $shop_page_id ) {
							$breadcrumbs_output .= sprintf( $link, $shop_home_link, $shop_home_name );
						}
						$breadcrumbs_output .= $before . sprintf( esc_html__( 'Search results for &ldquo;%s&rdquo;', 'crane' ), get_search_query() ) . $after;
					} else {

						if ( is_shop() ) {
							$breadcrumbs_output .= $before . $shop_home_name . $after;
						} else {

							if ( $home_page_id !== $shop_page_id ) {
								$breadcrumbs_output .= sprintf( $link, $shop_home_link, $shop_home_name );
							}

							$wc_br_list = ( new WC_Breadcrumb() )->generate();

							if ( is_array( $wc_br_list ) ) {
								if ( 'with_category' === $breadcrumbs_view ) {
									$bc_count = count( $wc_br_list ) - 1;
									foreach ( $wc_br_list as $index => $bc ) {
										if ( ! is_shop() && ! empty( $bc[1] ) && $bc[1] === $shop_home_link ) {
											continue;
										}

										if ( ! empty( $bc[1] ) && $bc_count !== $index ) {
											$breadcrumbs_output .= sprintf( $link, $bc[1], $bc[0] );
										} else {
											$breadcrumbs_output .= $before . $bc[0] . $after;
										}
									}
								} else {
									$breadcrumbs_output .= $before . end( $wc_br_list )[0] . $after;
								}
							}

						}
					}

				} elseif ( is_category() ) {
					$thisCat = get_category( get_query_var( 'cat' ), false );
					if ( $thisCat->parent != 0 ) {
						$cats = get_category_parents( $thisCat->parent, true, $delimiter );
						$cats = str_replace( '<a', $link_before . '<a' . $link_attr, $cats );
						$cats = str_replace( '</a>', '</a>' . $link_after, $cats );
						$breadcrumbs_output .= $cats;
					}

					$breadcrumbs_output .= $before . sprintf( $text['category'], single_cat_title( '', false ) ) . $after;

				} elseif ( is_tax() ) {
					$thisCat = get_category( get_query_var( 'cat' ), false );
					if ( isset( $thisCat->parent ) && $thisCat->parent != 0 ) {
						$cats = get_category_parents( $thisCat->parent, true, $delimiter );
						$cats = str_replace( '<a', $link_before . '<a' . $link_attr, $cats );
						$cats = str_replace( '</a>', '</a>' . $link_after, $cats );
						$breadcrumbs_output .= $cats;
					}

					$post_type      = get_post_type_object( get_post_type() );
					if ( ! empty( $post_type->rewrite ) && ! empty( $post_type->name ) && 'crane_portfolio' === $post_type->name ) {
						$post_type_slug  = $post_type->rewrite;
						$post_type_title = isset( $crane_options['portfolio-name'] ) ? $crane_options['portfolio-name'] : esc_html__( 'Portfolio', 'crane' );
						$breadcrumbs_output .= sprintf( $link, $home_link . $post_type_slug['slug'] . '/', $post_type_title );
					}

					$breadcrumbs_output .= $before . sprintf( $text['tax'], single_cat_title( '', false ) ) . $after;

				} elseif ( is_search() || crane_is_shop_search() ) {
					$breadcrumbs_output .= $before . sprintf( $text['search'], get_search_query() ) . $after;

				} elseif ( is_day() ) {
					$breadcrumbs_output .= sprintf( $link, get_year_link( get_the_time( 'Y' ) ), get_the_time( 'Y' ) ) . $delimiter;
					$breadcrumbs_output .= sprintf( $link, get_month_link( get_the_time( 'Y' ), get_the_time( 'm' ) ),
							get_the_time( 'F' ) ) . $delimiter;
					$breadcrumbs_output .= $before . get_the_time( 'd' ) . $after;

				} elseif ( is_month() ) {
					$breadcrumbs_output .= sprintf( $link, get_year_link( get_the_time( 'Y' ) ), get_the_time( 'Y' ) ) . $delimiter;
					$breadcrumbs_output .= $before . get_the_time( 'F' ) . $after;

				} elseif ( is_year() ) {
					$breadcrumbs_output .= $before . get_the_time( 'Y' ) . $after;

				} elseif ( is_single() && ! is_attachment() ) {
					if ( get_post_type() != 'post' ) {
						$post_type = get_post_type_object( get_post_type() );
						if ( ! empty( $post_type ) && isset( $post_type->name ) ) {

							$post_type_title = '';
							$post_type_slug  = $post_type->rewrite;

							switch ( $post_type->name ) {

								case 'crane_portfolio' :
									if ( in_array( $breadcrumbs_view, array( 'with_category', 'with_categories' ) ) ) {
										$post_type_title = isset( $crane_options['portfolio-name'] ) ? $crane_options['portfolio-name'] : esc_html__( 'Portfolio', 'crane' );
										$slug            = $post_type->rewrite;
										$breadcrumbs_output .= sprintf( $link, $home_link . $post_type_slug['slug'] . '/', $post_type_title );
										$portfolio_terms = get_the_terms( get_the_ID(), 'crane_portfolio_cats' );

										if ( ! empty( $portfolio_terms ) && ! is_wp_error( $portfolio_terms ) ) {
											$portfolio_terms_list = array();
											foreach ( $portfolio_terms as $portfolio_term ) {
												$term_link = get_term_link( $portfolio_term, 'crane_portfolio_cats' );
												if ( is_wp_error( $term_link ) ) {
													continue;
												}
												$portfolio_terms_list[] = sprintf( $link_clean, $term_link, $portfolio_term->name );
											}

											if ( empty( $portfolio_terms_list ) ) {
												break;
											}

											/**
											 * Filters the term links for a given taxonomy.
											 *
											 * The dynamic portion of the filter name, `$taxonomy`, refers
											 * to the taxonomy slug.
											 *
											 * @param array $links An array of term links.
											 */
											$term_links = apply_filters( "term_links-crane_portfolio_cats", $portfolio_terms_list );

											if ( ! empty( $term_links ) ) {
												if ( 'with_category' === $breadcrumbs_view ) {
													$breadcrumbs_output .= $before . $term_links[0] . $after;
												} else {
													$breadcrumbs_output .= $before . join( ', ', $term_links ) . $after;
												}
											}
										}

									}
									break;

								default :
									$post_type_title = $post_type->labels->singular_name;
									$breadcrumbs_output .= sprintf( $link, $home_link . $post_type_slug['slug'] . '/', $post_type_title );
									break;

							}

						}
						if ( $show_current === 1 ) {
							$breadcrumbs_output .= $delimiter . $before . $text_title . $after;
						}

					} else {

						if ( in_array( $breadcrumbs_view, array( 'with_category', 'with_categories' ) ) ) {
							$cat = get_the_category();

							if ( ! empty( $cat ) ) {

								$cats = array();

								foreach ( $cat as $one_cat ) {
									$cats_link = get_category_parents( $one_cat, true, $delimiter );
									$cats_link = str_replace( '<a', '<a' . $link_attr, $cats_link );
									$cats[]    = $cats_link;
								}

								if ( $show_current === 0 && ! empty( $cats[0] ) ) {
									$cats = array( preg_replace( "#^(.+)$delimiter$#", "$1", $cats[0] ) );
								}
								if ( ! empty( $cats ) ) {
									if ( 'with_category' === $breadcrumbs_view ) {
										$breadcrumbs_output .= $link_before . $cats[0] . $link_after;
									} else {
										$breadcrumbs_output .= $link_before . join( ', ', $cats ) . $link_after;
									}
								}
							}
						}

						if ( $show_current === 1 ) {
							$breadcrumbs_output .= $before . $text_title . $after;
						}

					}

				} elseif ( is_archive() && is_post_type_archive() ) {
					$post_type = get_post_type_object( get_post_type() );
					if ( ! empty( $post_type ) && isset( $post_type->name ) ) {
						switch ( $post_type->name ) {
							case 'crane_portfolio' :
								$text_title = isset( $crane_options['portfolio-name'] ) ? $crane_options['portfolio-name'] : esc_html__( 'Portfolio', 'crane' );
								break;
							default :
								$text_title = $post_type->labels->singular_name;
								break;
						}

						$breadcrumbs_output .= $before . $text_title . $after;

					}

				} elseif ( ! is_single() && ! is_page() && get_post_type() != 'post' && ! is_404() && ! is_tag() ) {
					$post_type = get_post_type_object( get_post_type() );
					if ( ! empty( $post_type ) ) {
						$breadcrumbs_output .= $before . $post_type->labels->singular_name . $after;
					}

				} elseif ( is_attachment() ) {
					if ( $show_current === 1 ) {
						$breadcrumbs_output .= $delimiter . $before . $text_title . $after;
					}

				} elseif ( is_page() && ! $post->post_parent ) {
					if ( $show_current === 1 ) {
						$breadcrumbs_output .= $before . $text_title . $after;
					}

				} elseif ( is_page() && $post->post_parent ) {
					$parent_id   = $post->post_parent;
					$breadcrumbs = array();
					while ( $parent_id ) {
						$page          = get_post( $parent_id );
						$breadcrumbs[] = sprintf( $link, esc_url( get_permalink( $page->ID ) ), get_the_title( $page->ID ) );
						$parent_id     = $page->post_parent;
					}
					$breadcrumbs = array_reverse( $breadcrumbs );
					for ( $i = 0; $i < count( $breadcrumbs ); $i ++ ) {
						$breadcrumbs_output .= $breadcrumbs[ $i ];
						if ( $i != count( $breadcrumbs ) - 1 ) {
							$breadcrumbs_output .= $delimiter;
						}
					}
					if ( $show_current === 1 ) {
						$breadcrumbs_output .= $delimiter . $before . $text_title . $after;
					}

				} elseif ( is_tag() ) {
					$breadcrumbs_output .= $before . sprintf( $text['tag'], single_tag_title( '', false ) ) . $after;

				} elseif ( is_author() ) {
					global $author;
					$userdata = get_userdata( $author );
					$breadcrumbs_output .= $before . sprintf( $text['author'], $userdata->display_name ) . $after;

				} elseif ( is_404() ) {
					$breadcrumbs_output .= $before . $text['404'] . $after;
				}


				// ADD Paged test
				if ( get_query_var( 'paged' ) ) {
					if ( is_category() || is_day() || is_month() || is_year() || is_search() || crane_is_shop_search() || is_tag() || is_author() ) {
						$breadcrumbs_output .= '&nbsp;( ';
						$breadcrumbs_output .= esc_html__( 'Page', 'crane' ) . ' ' . get_query_var( 'paged' );
						$breadcrumbs_output .= ' )';
					}
				}

			}

			$breadcrumbs_output .= '</ul></div>
	</div>
  </div>';
		}

		echo crane_get_title_template( $text_title, $breadcrumbs_output, $breadcrumbs_type, ( isset( $crane_options['page-title-line-decorators-switch'] ) ? $crane_options['page-title-line-decorators-switch'] : '' ) );

	} // end crane_breadcrumbs()
}


/**
 * Breadcrumbs template for one link element
 *
 * @param string $link_pattern
 *
 * @return string
 */
function crane_breadcrumbs_middle_blog_link( $link_pattern = '' ) {
	$output = '';
	if ( empty( $link_pattern ) ) {
		return $output;
	}

	$blog_page_id = get_option( 'page_for_posts' );

	$post_type = get_post_type_object( get_post_type() );
	if ( ! empty( $post_type->name ) && 'post' === $post_type->name ) {
		if ( ! empty( $blog_page_id ) ) {
			//get_the_title( $ID );
			$post_title = get_the_title( $blog_page_id ) ? : esc_html_x( 'Blog', 'For breadcrumbs archive link. Use if blog page title is empty.', 'crane' );
			$post_url   = get_permalink( $blog_page_id );
			if ( $post_url ) {
				$output = sprintf( $link_pattern, $post_url, $post_title );
			}
		}
	}

	return $output;
}


if ( ! function_exists( 'crane_get_current_archive_title' ) ) {
	/**
	 * Get archive title
	 *
	 * @return string|void
	 */
	function crane_get_current_archive_title() {
		$text['search'] = esc_html__( 'Search Results for "%s" query', 'crane' ); // text for a search results page
		$text['tag']    = esc_html__( 'Posts Tagged "%s"', 'crane' ); // text for a tag page
		$text['author'] = esc_html__( 'Articles Posted by %s', 'crane' ); // text for an author page
		$text['404']    = esc_html__( 'Error 404', 'crane' ); // text for the 404 page

		$text_title = '';
		global $post, $crane_options;

		if ( is_search() || crane_is_shop_search() ) {
			$text_title = sprintf( $text['search'], get_search_query() );

		} elseif ( class_exists( 'WooCommerce' ) && is_shop() ) {
			$text_title = wc_get_page_id( 'shop' ) ? get_the_title( wc_get_page_id( 'shop' ) ) : '';
			if ( ! $text_title ) {
				$product_post_type = get_post_type_object( 'product' );
				$text_title        = $product_post_type->labels->singular_name;
			}

		} elseif ( is_category() || is_tax() || is_tag() ) {
			$text_title = single_cat_title( '', false );
		} elseif ( is_day() ) {
			$text_title = sprintf( esc_html__( 'Daily Archives: %s', 'crane' ), '<span>' . get_the_date() . '</span>' );

		} elseif ( is_month() ) {
			$text_title = sprintf( esc_html__( 'Monthly Archives: %s', 'crane' ), '<span>' . get_the_date( esc_html_x( 'F Y', 'monthly archives date format (by PHP date() format ). Keep this line untranslated if you not sure', 'crane' ) ) . '</span>' );

		} elseif ( is_year() ) {
			$text_title = sprintf( esc_html__( 'Yearly Archives: %s', 'crane' ), '<span>' . get_the_date( esc_html_x( 'Y', 'yearly archives date format (by PHP date() format ). Keep this line untranslated if you not sure', 'crane' ) ) . '</span>' );

		} elseif ( is_single() || is_page() ) {
			$text_title = get_the_title();

		} elseif ( ! is_singular() && ! is_page_template( 'archive.php' ) && 'archive.php' === get_page_template_slug( get_queried_object_id() ) ) {
			$text_title = get_the_title();

		} elseif ( is_page() && ! $post->post_parent ) {
			$text_title = get_the_title();

		} elseif ( is_tag() ) {
			$text_title = sprintf( $text['tag'], single_tag_title( '', false ) );

		} elseif ( is_author() ) {
			global $author;
			$userdata   = get_userdata( $author );
			$text_title = sprintf( $text['author'], $userdata->display_name );

		} elseif ( is_404() ) {
			$text_title = $text['404'];

		} elseif ( is_archive() && is_post_type_archive() ) {
			$post_type = get_post_type_object( get_post_type() );
			if ( ! empty( $post_type ) && isset( $post_type->name ) ) {
				switch ( $post_type->name ) {
					case 'crane_portfolio' :
						$text_title = isset( $crane_options['portfolio-name'] ) ? $crane_options['portfolio-name'] : esc_html__( 'Portfolio', 'crane' );
						break;
					default :
						$text_title = $post_type->labels->singular_name;
						break;
				}
			}

		} elseif ( ! is_single() && ! is_page() && get_post_type() != 'post' && ! is_404() && ! is_tag() ) {
			$post_type = get_post_type_object( get_post_type() );
			if ( ! empty( $post_type ) ) {
				$text_title = $post_type->labels->singular_name;
			}

		} elseif ( is_home() && crane_is_blog() && ! empty( $crane_options['blog-archive-title'] ) ) {
			$text_title = esc_js( $crane_options['blog-archive-title'] );

		} else {
			$text_title = esc_html__( 'Archive', 'crane' );
		}


		// Add pages text
		if ( get_query_var( 'paged' ) ) {
			if ( is_home() || is_category() || is_day() || is_month() || is_year() || is_search() || crane_is_shop_search() || is_tag() || is_author() ) {
				$text_title .= ' (';
			}
			$text_title .= ' ' . esc_html__( 'Page', 'crane' ) . ' ' . get_query_var( 'paged' );
			if ( is_home() || is_category() || is_day() || is_month() || is_year() || is_search() || crane_is_shop_search() || is_tag() || is_author() ) {
				$text_title .= ' )';
			}
		}

		return $text_title;
	}
}


if ( ! function_exists( 'crane_get_title_template' ) ) {
	/**
	 * Get title
	 *
	 * @param $text_title
	 * @param string $breadcrumbs_output
	 * @param string $breadcrumbs_type
	 * @param bool|false $title_heading_decorator
	 *
	 * @return string
	 */
	function crane_get_title_template( $text_title, $breadcrumbs_output = '', $breadcrumbs_type = '', $title_heading_decorator = false ) {

		if ( 'none' === $breadcrumbs_type ) {
			return '';
		}

		$title_heading_decorator = $title_heading_decorator ? '<span class="crane-page-title-heading-decorator"></span>' : '';

		$html_otput = '';
		if ( 'breadcrumbs' === $breadcrumbs_type ) {
			$html_otput .= $breadcrumbs_output;
		} elseif ( 'both_within' === $breadcrumbs_type ) {
			$html_otput .=
				'<div class="crane-page-title">' .
				'<div class="crane-page-title-wrapper">';
			$html_otput .= empty( $text_title ) ? '' :
				'<div class="crane-container">' .
				'	<div class="crane-page-title-holder">' .
				$title_heading_decorator .
				'       <h3 class="crane-page-title-heading">' . $text_title . '</h3>' .
				$title_heading_decorator .
				'	</div>' .
				'</div>';
			$html_otput .= $breadcrumbs_output;
			$html_otput .=
				'</div>' .
				'</div>';
		} elseif ( in_array( $breadcrumbs_type, [ 'both_before', 'both_after', 'title' ] ) ) {
			if ( 'both_before' === $breadcrumbs_type ) {
				$html_otput .= $breadcrumbs_output;
			}
			$html_otput .= empty( $text_title ) ? '' :
				'<div class="crane-page-title">' .
				'<div class="crane-container">' .
				'	<div class="crane-row-flex">' .
				'		<div class="crane-col-xs-12">' .
				'			<div class="crane-page-title-holder">' .
				$title_heading_decorator .
				'				<h3 class="crane-page-title-heading">' . $text_title . '</h3>' .
				$title_heading_decorator .
				'			</div>' .
				'		</div>' .
				'	</div>' .
				'</div>' .
				'</div>';
			if ( 'both_after' === $breadcrumbs_type ) {
				$html_otput .= $breadcrumbs_output;
			}
		}

		return $html_otput;
	}
}


if ( ! function_exists( 'crane_get_nav_menus' ) ) {

	/**
	 * Add default variant for standart get_nav_menus() function
	 *
	 * @return array
	 */
	function crane_get_nav_menus( $just_default = false ) {
		$nav_menus = array();

		if ( $just_default ) {
			$nav_menus['0'] = esc_html__( 'Default (from Primary Menu Location)', 'crane' );
		} else {
			$nav_menus['0'] = esc_html__( 'Default (from Theme Options)', 'crane' );
		}

		foreach ( wp_get_nav_menus() as $menu ) {
			$nav_menus[ $menu->slug ] = $menu->name;
		}

		return $nav_menus;
	}
}

function crane_is_blog() {
	global $post;
	$posttype = get_post_type( $post );

	return ( ( ( is_archive() ) || ( is_author() ) || ( is_category() ) || ( is_home() ) || ( is_single() ) || ( is_tag() ) ) && ( $posttype === 'post' ) ) ? true : false;
}

/**
 * Install bundled plugin
 *
 * @param string $plugin_slug
 * @param string $plugin_path
 * @param string $source
 */
function crane_run_necessary_plugins( $plugin_slug, $plugin_path, $source = '', $is_upgrade = false ) {


	if ( empty( $source ) ) {
		$source = 'http://updates.grooni.com/?action=download&slug=' . $plugin_slug;
	}


	if ( class_exists( 'TGMPA_Bulk_Installer' ) && class_exists( 'TGMPA_Bulk_Installer_Skin' ) ) {

		if ( ! is_dir( WP_PLUGIN_DIR . DIRECTORY_SEPARATOR . $plugin_slug ) ) {
			$install_type = 'install';
		} elseif ( $is_upgrade ) {
			$install_type = 'upgrade';
		}

		// Create a new instance of TGMPA_Bulk_Installer.
		$tgmpa_installer = new TGMPA_Bulk_Installer(
			new TGMPA_Bulk_Installer_Skin(
				array(
					'url'          => '',
					'nonce'        => 'bulk-plugins',
					'names'        => array( $plugin_slug ),
					'install_type' => esc_attr( $install_type ),
				)
			)
		);

		if ( 'install' === $install_type ) {
			$tgmpa_installer->install(
				$source,
				[ 'clear_update_cache' => true ]
			);
		} elseif ( 'upgrade' === $install_type ) {
			$tgmpa_installer->upgrade(
				$plugin_path,
				[ 'clear_update_cache' => true ]
			);
		}


		// Flush plugins cache so the headers of the newly installed plugins will be read correctly.
		wp_clean_plugins_cache();

		// Get the installed plugin file.
		$plugin_info = $tgmpa_installer->plugin_info();

		if ( function_exists( 'grooni_activate_plugin' ) ) {
			grooni_activate_plugin( $plugin_info );
		}

		return;

	}


	if ( ! defined( 'FS_METHOD' ) ) {
		define( 'FS_METHOD', 'direct' );
	}
	global $wp_filesystem;
	if ( empty( $wp_filesystem ) ) {
		if ( file_exists( ABSPATH . '/wp-admin/includes/file.php' ) ) {
			require_once ABSPATH . '/wp-admin/includes/file.php';
			WP_Filesystem();
		}
	}
	if ( empty( $wp_filesystem ) ) {
		return;
	}

	if ( ! class_exists( 'Plugin_Upgrader' ) ) {
		if ( file_exists( ABSPATH . '/wp-admin/includes/class-wp-upgrader.php' ) ) {
			require_once ABSPATH . '/wp-admin/includes/class-wp-upgrader.php';
		}
	}

	if ( ! class_exists( 'Crane_UpgraderSkin' ) ) {

		class Crane_UpgraderSkin extends Plugin_Installer_Skin {
			// ... empty skin (clean) ...
			public function header() {
			}

			public function bulk_header() {
			}

			public function before( $title = '' ) {
			}

			public function feedback( $string ) {
			}

			public function add_strings() {
			}

			public function after( $title = '' ) {
			}

			public function footer() {
			}

			public function bulk_footer() {
			}
		}
	}

	$upgrader = new Plugin_Upgrader( new Crane_UpgraderSkin() );

	if ( empty( $source ) ) {
		$source = 'http://updates.grooni.com/?action=download&slug=' . $plugin_slug;
	}

	if ( ! is_dir( WP_PLUGIN_DIR . DIRECTORY_SEPARATOR . $plugin_slug ) ) {
		$upgrader->install(
			$source,
			[ 'clear_update_cache' => true ]
		);
	} elseif ( $is_upgrade ) {
		$upgrader->upgrade(
			$plugin_path,
			[ 'clear_update_cache' => true ]
		);
	}

	$active_plugins = (array) get_option( 'active_plugins', array() );
	if ( is_array( $active_plugins ) ) {
		if ( ! in_array( $plugin_path, $active_plugins ) ) {
			if ( file_exists( WP_PLUGIN_DIR . DIRECTORY_SEPARATOR . $plugin_path ) ) {
				array_push( $active_plugins, $plugin_path );
			}
		}

		update_option( 'active_plugins', $active_plugins );
	}

}


/**
 * Check is plugin installed
 *
 * @param $plugin_slug
 * @param $plugin_path
 *
 * @return bool
 */
function crane_is_plugin_installed( $plugin_slug, $plugin_path ) {

	if ( is_dir( WP_PLUGIN_DIR . DIRECTORY_SEPARATOR . $plugin_slug ) ) {
		$active_plugins = (array) get_option( 'active_plugins', array() );
		if ( is_array( $active_plugins ) ) {
			if ( in_array( $plugin_path, $active_plugins ) ) {
				return true;
			}
		}
	}

	return false;

}


if ( ! function_exists( 'crane_get_crane_plugins_array' ) ) {

	/**
	 * Array of bundled plugins
	 *
	 * @return array
	 */
	function crane_get_crane_plugins_array( $additional_plugins = false ) {

		if ( true === $additional_plugins ) {
			$return_plugins = array(
				'mpc-massive' =>
					[
						'name'               => 'Massive Addons',
						'slug'               => 'mpc-massive',
						'source'             => 'http://updates.grooni.com/?action=download&slug=mpc-massive',
						'installed_path'     => 'mpc-massive/mpc-massive.php',
						'required'           => false,
						'force_activation'   => false,
						'grooni_premium'     => true,
						'conflicted_with'    => array( 'Ultimate_VC_Addons' ),
						'plugin_description' => esc_html__( 'Uber Extension for WPBakery Page Builder plugin.', 'crane' ),
					]
			);
		} else {
			$return_plugins = array(
				'js_composer'           =>
					[
						'name'               => 'WPBakery Page Builder',
						'slug'               => 'js_composer',
						'source'             => 'http://updates.grooni.com/?action=download&slug=js_composer',
						'installed_path'     => 'js_composer/js_composer.php',
						'required'           => false,
						'force_activation'   => false,
						'grooni_premium'     => true,
						'plugin_description' => esc_html__( 'Drag and drop page builder for WordPress. Take full control over your WordPress site, build any layout you can imagine – no programming knowledge required.', 'crane' ),
					],
				'revslider'             =>
					[
						'name'               => 'Revolution slider',
						'slug'               => 'revslider',
						'source'             => 'http://updates.grooni.com/?action=download&slug=revslider',
						'installed_path'     => 'revslider/revslider.php',
						'required'           => false,
						'force_activation'   => false,
						'grooni_premium'     => true,
						'plugin_description' => esc_html__( 'Slider Revolution - Premium responsive slider', 'crane' ),
					],
				'groovy-menu'           =>
					[
						'name'               => 'Groovy menu',
						'slug'               => 'groovy-menu',
						'source'             => 'http://updates.grooni.com/?action=download&slug=groovy-menu',
						'installed_path'     => 'groovy-menu/groovy-menu.php',
						'version'            => '1.6.1',
						'min-version'        => '1.6.1',
						'required'           => true,
						'force_activation'   => false,
						'grooni_premium'     => true,
						'plugin_description' => esc_html__( 'Groovy menu is a modern adjustable and flexible menu designed for creating mobile-friendly menus with a lot of options.', 'crane' ),
					],
				'grooni-theme-addons'   =>
					[
						'name'               => 'Grooni Theme Addons',
						'slug'               => 'grooni-theme-addons',
						'source'             => get_template_directory() . '/admin/tgm/plugins/grooni-theme-addons.zip',
						'installed_path'     => 'grooni-theme-addons/grooni-theme-addons.php',
						'version'            => '1.4.4.1',
						'min-version'        => '1.4.4.1',
						'required'           => true,
						'force_activation'   => false,
						'plugin_description' => esc_html__( 'Grooni theme addons (extensions). The plugin contains custom post type, shortcodes and custom shortcodes for Visual Composer.', 'crane' ),
					],
				'instagram-feed'        =>
					[
						'name'               => 'Instagram Feed',
						'slug'               => 'instagram-feed',
						'source'             => 'http://updates.grooni.com/?action=download&slug=instagram-feed',
						'installed_path'     => 'instagram-feed/instagram-feed.php',
						'required'           => false,
						'force_activation'   => false,
						'plugin_description' => esc_html__( 'Display Instagram posts from your Instagram accounts, either in the same single feed or in multiple different ones.', 'crane' ),
						'plugin_icon'        => 'https://ps.w.org/instagram-feed/assets/icon-128x128.png'
					],
				'woocommerce'           =>
					[
						'name'               => 'Woocommerce',
						'slug'               => 'woocommerce',
						'installed_path'     => 'woocommerce/woocommerce.php',
						'required'           => false,
						'force_activation'   => false,
						'plugin_description' => esc_html__( 'An eCommerce toolkit that helps you sell anything. Beautifully.', 'crane' ),
						'plugin_icon'        => 'https://ps.w.org/woocommerce/assets/icon-128x128.png'
					],
				'LayerSlider'           =>
					[
						'name'                   => 'LayerSlider',
						'slug'                   => 'LayerSlider',
						'source'                 => 'http://updates.grooni.com/?action=download&slug=LayerSlider',
						'installed_path'         => 'LayerSlider/layerslider.php',
						'required'               => false,
						'force_activation'       => false,
						'import_install_exclude' => true,
						'show_notices'           => false,
						'grooni_premium'         => true,
						'plugin_description'     => esc_html__( 'LayerSlider is a premium multi-purpose content creation and animation platform. Easily create sliders, image galleries, slideshows with mind-blowing effects, popups, landing pages, animated page blocks, or even a full website.', 'crane' ),

					],
				'Ultimate_VC_Addons'    =>
					[
						'name'               => 'Ultimate VC addons',
						'slug'               => 'Ultimate_VC_Addons',
						'source'             => 'http://updates.grooni.com/?action=download&slug=Ultimate_VC_Addons',
						'installed_path'     => 'Ultimate_VC_Addons/Ultimate_VC_Addons.php',
						'required'           => false,
						'force_activation'   => false,
						'grooni_premium'     => true,
						'conflicted_with'    => array( 'mpc-massive' ),
						'plugin_description' => esc_html__( 'Includes WPBakery Page Builder premium addon elements like Icon, Info Box, Interactive Banner, Flip Box, Info List & Counter. Best of all - provides A Font Icon Manager allowing users to upload / delete custom icon fonts.', 'crane' ),
					],
				'grooni_twitter_widget' =>
					[
						'name'               => 'Grooni Twitter Feeds widget',
						'slug'               => 'grooni_twitter_widget',
						'source'             => 'http://updates.grooni.com/?action=download&slug=grooni_twitter_widget',
						'installed_path'     => 'grooni_twitter_widget/grooni_twitter_widget.php',
						'required'           => false,
						'force_activation'   => false,
						'plugin_description' => esc_html__( 'Displays latest tweets from any Twitter account.', 'crane' ),
					],
				'contact-form-7'        =>
					[
						'name'               => 'Contact Form 7',
						'slug'               => 'contact-form-7',
						'installed_path'     => 'contact-form-7/wp-contact-form-7.php',
						'required'           => false,
						'force_activation'   => false,
						'plugin_description' => esc_html__( 'Just another contact form plugin. Simple but flexible.', 'crane' ),
						'plugin_icon'        => 'https://ps.w.org/contact-form-7/assets/icon-128x128.png'
					],
				'convertplug'           =>
					[
						'name'               => 'ConvertPlug',
						'slug'               => 'convertplug',
						'source'             => 'http://updates.grooni.com/?action=download&slug=convertplug',
						'installed_path'     => 'convertplug/convertplug.php',
						'required'           => false,
						'force_activation'   => false,
						'grooni_premium'     => true,
						'plugin_description' => esc_html__( 'Convert Plus will help you build email lists, drive traffic, promote videos, offer coupons and much more!', 'crane' ),
					],
				'mailchimp-for-wp'      =>
					[
						'name'               => 'MailChimp for WordPress',
						'slug'               => 'mailchimp-for-wp',
						'installed_path'     => 'mailchimp-for-wp/mailchimp-for-wp.php',
						'required'           => false,
						'force_activation'   => false,
						'plugin_description' => esc_html__( 'MailChimp for WordPress by ibericode. Adds various highly effective sign-up methods to your site.', 'crane' ),
						'plugin_icon'        => 'https://ps.w.org/mailchimp-for-wp/assets/icon-128x128.png'
					],
			);

		}

		return $return_plugins;

	}

}


if ( ! function_exists( 'crane_get_crane_sidebars_array' ) ) {

	/**
	 * Array of theme sidebars
	 *
	 * @return array
	 */
	function crane_get_crane_sidebars_array() {
		$sidebars = array();

		$sidebars['crane_basic_sidebar'] = array(
			'name'          => esc_html__( 'Basic sidebar', 'crane' ),
			'id'            => 'crane_basic_sidebar',
			'class'         => '',
			'before_widget' => '<div id="%1$s" class="widget %2$s">',
			'after_widget'  => '</div>',
			'before_title'  => '<h4 class="widget-title">',
			'after_title'   => '</h4>'
		);

		return $sidebars;

	}
}


if ( ! function_exists( 'crane_get_js_l10n' ) ) {

	/**
	 * Return translates for JS scripts
	 *
	 * @param bool|false $for_admin
	 *
	 * @return array
	 */
	function crane_get_js_l10n( $for_admin = false ) {
		$crane_js_l10n = array();

		if ( $for_admin ) {
			$crane_js_l10n['are_u_sure']                = esc_html__( 'Are you sure?', 'crane' );
			$crane_js_l10n['are_u_sure_delete']         = esc_html__( 'Are you sure that you want to delete', 'crane' );
			$crane_js_l10n['will_remove_widgets']       = esc_html__( 'This will remove any widgets you have assigned to this sidebar.', 'crane' );
			$crane_js_l10n['remove_image']              = esc_html__( 'Remove image?', 'crane' );
			$crane_js_l10n['select']                    = esc_html__( 'Select', 'crane' );
			$crane_js_l10n['unselect']                  = esc_html__( 'Unselect', 'crane' );
			$crane_js_l10n['please_enter_sidebar_name'] = esc_html__( 'Please, enter sidebar name', 'crane' );
			$crane_js_l10n['choose_image']              = esc_html__( 'Please, choose image', 'crane' );
			$crane_js_l10n['use_image']                 = esc_html__( 'Use image', 'crane' );
			$crane_js_l10n['save_alert']                = esc_html__( 'The changes you made will be lost if you navigate away from this page.', 'crane' );
			$crane_js_l10n['delete_sidebar']            = esc_html__( 'Do you want to delete this sidebar?', 'crane' );
			$crane_js_l10n['delete_sidebar_btn']        = esc_html__( 'Delete sidebar', 'crane' );
			$crane_js_l10n['edit_sidebar_btn']          = esc_html__( 'Edit sidebar', 'crane' );
			$crane_js_l10n['sidebar_new_name']          = esc_html__( 'Enter new sidebar name:', 'crane' );
			$crane_js_l10n['sidebar_name_empty']        = esc_html__( 'Please, add sidebar name.', 'crane' );
			$crane_js_l10n['quote_with_author']         = esc_html__( 'Insert quote with author', 'crane' );
			$crane_js_l10n['select_some_quote_text']    = esc_html__( 'Please select some quote text.', 'crane' );
			$crane_js_l10n['quote_must_not_empty']      = esc_html__( 'Please select some quote text.', 'crane' );
			$crane_js_l10n['quote_text']                = esc_html__( 'Quote text', 'crane' );
			$crane_js_l10n['quote_author_name']         = esc_html__( 'Quote author name', 'crane' );
			$crane_js_l10n['quote_author_url']          = esc_html__( 'Quote author URL', 'crane' );
		} else {
			$crane_js_l10n['out_of_stock']    = esc_html__( 'out of stock', 'crane' );
			$crane_js_l10n['wc_added_2_cart'] = esc_html__( 'Product added to cart', 'crane' );
			$crane_js_l10n['excerpt_more']    = ' ...';
		}

		return $crane_js_l10n;
	}
}


if ( ! function_exists( 'crane_is_shop_search' ) ) {

	/**
	 * Return true if current search page is post_type === 'product'
	 *
	 * @return bool
	 */
	function crane_is_shop_search() {
		if ( get_search_query() && get_query_var( 'post_type' ) && 'product' === get_query_var( 'post_type' ) ) {
			return true;
		}

		return false;
	}
}


/**
 * Shop search fix for title
 *
 * @param $query
 */
function crane_update_shop_search_query( $query ) {
	if ( is_search() && crane_is_shop_search() ) {

		$query->is_search = false;

		add_filter( 'woocommerce_page_title', function ( $title ) {
			$title = sprintf( esc_html__( 'Search results: &ldquo;%s&rdquo;', 'crane' ), get_search_query() );

			if ( get_query_var( 'paged' ) ) {
				$title .= sprintf( esc_html__( '&nbsp;&ndash; Page %s', 'crane' ), get_query_var( 'paged' ) );
			}

			return $title;
		} );
	}
}

add_action( 'parse_query', 'crane_update_shop_search_query' );


if ( ! function_exists( 'crane_get_current_page_type' ) ) {

	/**
	 * Uses for many custom options
	 *
	 * @return string
	 */
	function crane_get_current_page_type() {

		$type = 'regular-page';

		if ( crane_is_shop_search() ) {

			$type = 'shop';

		} elseif ( is_search() ) {

			$type = 'search';

		} elseif ( is_404() ) {

			$type = '404';

		} elseif ( is_attachment() ) {

			$type = 'attachment';

		} elseif ( crane_is_product_woocommerce_page() ) {

			$type = 'shop-single';

		} elseif ( crane_is_shop_and_category_woocommerce_page() || crane_is_additional_woocommerce_page() || crane_is_product_woocommerce_page() ) {

			$type = 'shop';

		} elseif ( is_page_template( 'template-blog.php' ) || is_home() ) {

			$type = 'blog';

		} elseif ( is_page_template( 'template-portfolio.php' ) ) {

			$type = 'portfolio-archive';

		} elseif ( ( is_single() && 'crane_portfolio' === get_post_type() ) || ( is_archive() && 'crane_portfolio' === get_post_type() ) || 'crane_portfolio_cats' === get_query_var( 'taxonomy' ) || 'crane_portfolio_tags' === get_query_var( 'taxonomy' ) ) {

			$type = is_single() ? 'portfolio-single' : 'portfolio-archive';

		} elseif ( ( is_single() && 'post' === get_post_type() ) || ( is_archive() && 'post' === get_post_type() ) || is_archive() ) {

			$type = is_single() ? 'blog-single' : 'blog';

		} elseif ( is_page() && 'page' === get_post_type() ) {

			$type = 'regular-page';

		} elseif ( 'posts' === get_option( 'show_on_front' ) ) {
			// Check if the blog page is the front page.
			$type = 'blog';

		}

		return $type;
	}
}


if ( ! function_exists( 'crane_get_current_page_meta_override' ) ) {

	/**
	 * Detect: If meta data of page - then override global theme options
	 *
	 * @return bool
	 */
	function crane_get_current_page_meta_override() {
		$Crane_Meta_Data = crane_get_meta_data();

		global $wp_query;
		$queried_post_id = isset( $wp_query->queried_object->ID ) ? $wp_query->queried_object->ID : null;

		if ( is_single() || is_page() || crane_is_product_woocommerce_page() || crane_check_404_page() ) {
			if ( $Crane_Meta_Data->get( 'override_global', get_the_ID() ) ) {
				return true;
			}
		} elseif ( is_archive() ) {
			$current_cat      = get_queried_object();
			$term_id          = isset( $current_cat->term_id ) ? $current_cat->term_id : null;
			$category_options = $term_id ? crane_get_current_category_options( $term_id ) : null;

			if ( $category_options && isset( $category_options['custom_options'] ) && $category_options['custom_options'] === '1' ) {
				return true;
			}
		} elseif ( intval( get_option( 'page_for_posts' ) ) === $queried_post_id ) {
			if ( $Crane_Meta_Data->get( 'override_global', $queried_post_id ) ) {
				return true;
			}
		}

		return false;
	}
}


if ( ! function_exists( 'crane_get_options_for_current_page' ) ) {

	/**
	 * Get options of the current page. Override global theme options if MetaData exist
	 *
	 * @return array
	 */
	function crane_get_options_for_current_page( $post_id = null ) {

		static $options = array();

		if ( ! empty( $options ) ) {
			return $options;
		}

		global $crane_options;
		$Crane_Meta_Data = crane_get_meta_data();

		if ( empty( $post_id ) ) {
			$post_id = get_the_ID();
		}

		$current_cat = get_queried_object();
		$term_id     = isset( $current_cat->term_id ) ? $current_cat->term_id : null;

		$category_options = $term_id ? crane_get_current_category_options( $term_id ) : null;

		$options = array(
			'type'                => crane_get_current_page_type(),
			'page_class'          => 'crane-regular',
			'meta_override'       => crane_get_current_page_meta_override(),
			'breadcrumbs'         => '',
			'breadcrumbs_view'    => 'only_name',
			'groovy_menu'         => 'default',
			'nav_menu'            => '',
			'show-prev-next-post' => false,
			'show-author-info'    => '0',
			'show-related-posts'  => '1',
			'show_comment_link'   => '0',
			'show_share_button'   => '0',
			'has-sidebar'         => false,
			'sidebar'             => '',
			'content_width'       => 75,
			'sidebar_width'       => 25,
			'sticky'              => '0',
			'sticky-offset'       => '15',
			'template'            => '',
			'footer_preset'       => '',
			'footer_appearance'   => '',
			'image_resolution'    => '',
			'portfolio-single'    => array(
				'show-title'  => true,
				'show-border' => true,
				'show-tags'   => true,
				'show-date'   => true,
				'show-cats'   => true,
				'show-share'  => true,
			),
		);

		$page_type = $options['type'];

		// attachment has not own options yet
		if ( 'attachment' === $options['type'] ) {
			$page_type = 'blog';
		}

		if ( isset( $crane_options[ $page_type . '-has-sidebar' ] ) && 'none' !== $crane_options[ $page_type . '-has-sidebar' ] ) {
			$options['has-sidebar'] = $crane_options[ $page_type . '-has-sidebar' ];
			$options['sidebar']     = $crane_options[ $page_type . '-sidebar' ];
		}

		if ( isset( $crane_options[ 'footer_preset_global' ] ) ) {
			$options['footer_preset']     = $crane_options[ 'footer_preset_global' ];
			$options['footer_appearance'] = $crane_options[ 'footer_appearance' ];
		}

		if ( isset( $crane_options[ $page_type . '-footer_preset' ] ) && 'default' !== $crane_options[ $page_type . '-footer_preset' ] ) {
			$options['footer_preset']     = $crane_options[ $page_type . '-footer_preset' ];
		}
		if ( isset( $crane_options[ $page_type . '-footer_appearance' ] ) && 'default' !== $crane_options[ $page_type . '-footer_appearance' ] ) {
			$options['footer_appearance'] = $crane_options[ $page_type . '-footer_appearance' ];
		}

		if ( $options['has-sidebar'] ) {
			$options['content_width'] = isset( $crane_options[ $page_type . '-content-width' ] ) ? $crane_options[ $page_type . '-content-width' ] : $options['content_width'];
			$options['sidebar_width'] = isset( $crane_options[ $page_type . '-sidebar-width' ] ) ? $crane_options[ $page_type . '-sidebar-width' ] : $options['sidebar_width'];
		}

		$options['image_resolution'] = isset( $crane_options[ $page_type . '-image_resolution' ] ) ? $crane_options[ $page_type . '-image_resolution' ] : 'full';

		$options['breadcrumbs'] = isset( $crane_options[ 'breadcrumbs-' . $page_type ] ) ? $crane_options[ 'breadcrumbs-' . $page_type ] : null;

		if ( ! $options['breadcrumbs'] && class_exists( 'Redux' ) ) {
			$_breadcrumbs_type_opt = Redux::getField( 'crane_options', 'breadcrumbs-' . $page_type );
			if ( isset( $_breadcrumbs_type_opt['default'] ) ) {
				$options['breadcrumbs'] = $_breadcrumbs_type_opt['default'];
			}
		}

		if ( ! empty( $crane_options[ $page_type . '-breadcrumbs_view' ] ) ) {
			$options['breadcrumbs_view'] = $crane_options[ $page_type . '-breadcrumbs_view' ];
		}

		if ( isset( $crane_options[ $page_type . '-show-prev-next-post' ] ) ) {
			$options['show-prev-next-post'] = $crane_options[ $page_type . '-show-prev-next-post' ];
		}

		if ( isset( $crane_options[ $page_type . '-show-author-info' ] ) ) {
			$options['show-author-info'] = $crane_options[ $page_type . '-show-author-info' ];
		}

		if ( isset( $crane_options[ $page_type . '-show-related-posts' ] ) ) {
			$options['show-related-posts'] = $crane_options[ $page_type . '-show-related-posts' ];
		}

		if ( isset( $crane_options[ $page_type . '-show-comment-counter' ] ) ) {
			$options['show_comment_link'] = $crane_options[ $page_type . '-show-comment-counter' ];
		}

		if ( isset( $crane_options[ $page_type . '-show_share_button' ] ) ) {
			$options['show_share_button'] = $crane_options[ $page_type . '-show_share_button' ];
		}


		if ( defined( 'GROOVY_MENU_DB_VER_OPTION' ) ) {
			$gm_db_version = get_option( GROOVY_MENU_DB_VER_OPTION );
		}
		if ( ! empty( $gm_db_version ) && version_compare( $gm_db_version, '1.4.4.403', '<' ) ) {
			if ( isset( $crane_options[ $page_type . '-nav_menu' ] ) ) {
				$options['nav_menu'] = $crane_options[ $page_type . '-nav_menu' ];
			}

			if ( $options['meta_override'] && ! empty( $category_options['nav_menu'] ) ) {
				$options['nav_menu'] = $category_options['nav_menu'];
			}
		}

		if ( isset( $crane_options[ $page_type . '-sticky' ] ) ) {
			$options['sticky'] = $crane_options[ $page_type . '-sticky' ];
		}

		if ( isset( $crane_options[ $page_type . '-sticky-offset' ] ) ) {
			$options['sticky-offset'] = $crane_options[ $page_type . '-sticky-offset' ];
		}


		switch ( $page_type ) {

			// --=[ attachment ]=--
			case 'attachment':
				$options['page_class'] = 'crane-attachment';

				break;


			// --=[ blog-single ]=--
			case 'blog-single':
				$options['page_class'] = 'crane-blog-single';

				$options['groovy_menu'] = isset( $crane_options['blog-single-menu'] ) ? $crane_options['blog-single-menu'] : null;

				if ( 'inherit' === $options['breadcrumbs'] ) {
					$options['breadcrumbs'] = isset( $crane_options['breadcrumbs-blog'] ) ? $crane_options['breadcrumbs-blog'] : null;
				}

				if ( ! $options['breadcrumbs'] && class_exists( 'Redux' ) ) {
					$_breadcrumbs_type_opt = Redux::getField( 'crane_options', 'breadcrumbs-blog' );
					if ( isset( $_breadcrumbs_type_opt['default'] ) ) {
						$options['breadcrumbs'] = $_breadcrumbs_type_opt['default'];
					}
				}

				break;


			// --=[ blog ]=--
			case 'blog':
				$options['page_class'] = 'crane-blog-archive';

				$options['template']    = isset( $crane_options['blog-template'] ) ? $crane_options['blog-template'] : 'standard';
				$options['groovy_menu'] = isset( $crane_options['blog-menu'] ) ? $crane_options['blog-menu'] : null;
				if ( $category_options && isset( $category_options['custom_options'] ) && $category_options['custom_options'] === '1' && class_exists( 'GroovyMenuCategoryPreset' ) ) {
					$groovyMenuPreset       = intval( GroovyMenuCategoryPreset::getCurrentPreset( $term_id ) );
					$options['groovy_menu'] = $groovyMenuPreset ? $groovyMenuPreset : ( isset( $crane_options['blog-menu'] ) ? $crane_options['blog-menu'] : null );
				}
				if ( isset( $crane_options[ 'blog-archive-sticky' ] ) ) {
					$options['sticky'] = $crane_options[ 'blog-archive-sticky' ];
				}
				if ( isset( $crane_options[ 'blog-archive-sticky-offset' ] ) ) {
					$options['sticky-offset'] = $crane_options[ 'blog-archive-sticky-offset' ];
				}

				if ( $category_options && isset( $category_options['custom_options'] ) && $category_options['custom_options'] === '1' ) {
					if ( ! empty( $category_options['template'] ) ) {
						if ( 'standard' === $category_options['template'] ) {
							$options['template']            = 'standard';
							$crane_options['blog-template'] = 'standard';
						} elseif ( 'masonry' === $category_options['template'] ) {
							$options['template']            = 'masonry';
							$crane_options['blog-template'] = 'masonry';
						} elseif ( 'cell' === $category_options['template'] ) {
							$options['template']            = 'cell';
							$crane_options['blog-template'] = 'cell';
						}
					}


					$category_options_list = array(
						'cell_columns'        => 'blog-cell-columns',
						'post_height_desktop' => '',
						'post_height_mobile'  => '',
						'cell-item-bg-color'  => ''
					);

					foreach ( $category_options_list as $cat_index => $to_index ) {
						$opt_name_to = empty( $to_index ) ? 'blog-' . $cat_index : $to_index;

						if ( isset( $category_options[ $cat_index ] ) && 'default' !== $category_options[ $cat_index ] ) {
							$crane_options[ $opt_name_to ] = $category_options[ $cat_index ];
						}
					}

					if ( ! empty( $category_options['style'] ) && 'default' !== $category_options['style'] ) {
						$crane_options['blog-style'] = $category_options['style'];
					}

					if ( isset( $category_options['image_resolution'] ) && 'default' !== $category_options['image_resolution'] ) {
						$crane_options['blog-image_resolution'] = $category_options['image_resolution'];
					}

					if ( ! empty( $category_options['masonry_columns'] ) && 'default' !== $category_options['masonry_columns'] ) {
						if ( intval( $category_options['masonry_columns'] ) ) {
							$crane_options['blog-masonry-columns'] = intval( $category_options['masonry_columns'] );
						}
					}

					if ( isset( $category_options['grid_spacing'] ) && 'default' !== $category_options['grid_spacing'] ) {
						$crane_options['blog-grid_spacing'] = intval( $category_options['grid_spacing'] );
					}

					if ( ! empty( $category_options['max_width'] ) && 'default' !== $category_options['max_width'] ) {
						$crane_options['blog-max_width'] = intval( $category_options['max_width'] );
					}

					if ( isset( $category_options['comment_counter'] ) && 'default' !== $category_options['comment_counter'] ) {
						$crane_options['blog-show-comment-counter'] = intval( $category_options['comment_counter'] );
					}

					if ( isset( $category_options['show_title_description'] ) && 'default' !== $category_options['show_title_description'] ) {
						$crane_options['blog-show_title_description'] = intval( $category_options['show_title_description'] );
					}

					if ( isset( $category_options['show_pubdate'] ) && 'default' !== $category_options['show_pubdate'] ) {
						$crane_options['blog-show_pubdate'] = intval( $category_options['show_pubdate'] );
					}

					if ( isset( $category_options['show_author'] ) && 'default' !== $category_options['show_author'] ) {
						$crane_options['blog-show_author'] = intval( $category_options['show_author'] );
					}

					if ( isset( $category_options['show_cats'] ) && 'default' !== $category_options['show_cats'] ) {
						$crane_options['blog-show_cats'] = intval( $category_options['show_cats'] );
					}

					if ( isset( $category_options['show_tags'] ) && 'default' !== $category_options['show_tags'] ) {
						$crane_options['blog-show_tags'] = intval( $category_options['show_tags'] );
					}

					if ( isset( $category_options['show_excerpt'] ) && 'default' !== $category_options['show_excerpt'] ) {
						$crane_options['blog-show_excerpt'] = intval( $category_options['show_excerpt'] );
					}

					if ( isset( $category_options['excerpt_strip_html'] ) && 'default' !== $category_options['excerpt_strip_html'] ) {
						$crane_options['blog-excerpt_strip_html'] = intval( $category_options['excerpt_strip_html'] );
					}

					if ( ! empty( $category_options['excerpt_height'] ) && 'default' !== $category_options['excerpt_height'] ) {
						$crane_options['blog-excerpt_height'] = intval( $category_options['excerpt_height'] );
					}

					if ( isset( $category_options['show_read_more'] ) && 'default' !== $category_options['show_read_more'] ) {
						$crane_options['blog-show_read_more'] = intval( $category_options['show_read_more'] );
					}

					if ( isset( $category_options['show_share_button'] ) && 'default' !== $category_options['show_share_button'] ) {
						$crane_options['blog-show_share_button'] = intval( $category_options['show_share_button'] );
					}

					if ( ! empty( $category_options['show_post_meta'] ) && 'default' !== $category_options['show_post_meta'] ) {
						$crane_options['blog-show_post_meta'] = $category_options['show_post_meta'];
					}

					if ( ! empty( $category_options['target'] ) && 'default' !== $category_options['target'] ) {
						$crane_options['blog-target'] = $category_options['target'];
					}

					if ( ! empty( $category_options['img_proportion'] ) && 'default' !== $category_options['img_proportion'] ) {
						$crane_options['blog-img_proportion'] = $category_options['img_proportion'];
					}

					if ( isset( $category_options['has-sidebar'] ) && 'default' !== $category_options['has-sidebar'] ) {
						$options['has-sidebar'] = $category_options['has-sidebar'];
						$options['sidebar']     = ( isset( $category_options['sidebar'] ) && 'default' !== $category_options['sidebar'] ) ? $category_options['sidebar'] : $options['sidebar'];
						if ( isset( $category_options['sidebar-width'] ) && 'default' !== $category_options['sidebar-width'] ) {
							$options['sidebar_width'] = intval( $category_options['sidebar-width'] );
						}
						if ( isset( $category_options['content-width'] ) && 'default' !== $category_options['content-width'] ) {
							$options['content_width'] = intval( $category_options['content-width'] );
						}
						if ( isset( $category_options['sticky'] ) && 'default' !== $category_options['sticky'] ) {
							$options['sticky'] = intval( $category_options['sticky'] );
						}
						if ( isset( $category_options['sticky-offset'] ) && 'default' !== $category_options['sticky-offset'] ) {
							$options['sticky-offset'] = array( 'padding-top' => intval( $category_options['sticky-offset'] ) );
						}
					}

					if ( isset( $category_options['padding'] ) && 'default' !== $category_options['padding'] ) {
						$value_explode = is_string( $category_options['padding'] ) ? explode( '|', $category_options['padding'] ) : array();

						$field_params_value = array();

						if ( count( $value_explode ) === 3 ) {
							foreach ( $value_explode as $key => $param ) {
								switch ( $key ) {
									case 0 :
										$field_params_value['padding-top'] = $param;
										break;
									case 1 :
										$field_params_value['padding-bottom'] = $param;
										break;
									case 2 :
										$field_params_value['units'] = $param;
										break;
								}
							}

							$field_params_value['padding-top']    = $field_params_value['padding-top'] . $field_params_value['units'];
							$field_params_value['padding-bottom'] = $field_params_value['padding-bottom'] . $field_params_value['units'];

							$crane_options['blog-archive-padding'] = $field_params_value;

						}
					}
					if ( isset( $category_options['padding-mobile'] ) && 'default' !== $category_options['padding-mobile'] ) {
						$value_explode = is_string( $category_options['padding-mobile'] ) ? explode( '|', $category_options['padding-mobile'] ) : array();

						$field_params_value = array();

						if ( count( $value_explode ) === 3 ) {
							foreach ( $value_explode as $key => $param ) {
								switch ( $key ) {
									case 0 :
										$field_params_value['padding-top'] = $param;
										break;
									case 1 :
										$field_params_value['padding-bottom'] = $param;
										break;
									case 2 :
										$field_params_value['units'] = $param;
										break;
								}
							}

							$field_params_value['padding-top']    = $field_params_value['padding-top'] . $field_params_value['units'];
							$field_params_value['padding-bottom'] = $field_params_value['padding-bottom'] . $field_params_value['units'];

							$crane_options['blog-archive-padding-mobile'] = $field_params_value;

						}
					}

				} // if custom_options

				$options['breadcrumbs'] = isset( $crane_options['breadcrumbs-blog'] ) ? $crane_options['breadcrumbs-blog'] : null;
				if ( ! $options['breadcrumbs'] && class_exists( 'Redux' ) ) {
					$_breadcrumbs_type_opt = Redux::getField( 'crane_options', 'breadcrumbs-blog' );
					if ( isset( $_breadcrumbs_type_opt['default'] ) ) {
						$options['breadcrumbs'] = $_breadcrumbs_type_opt['default'];
					}
				}

				break;


			// --=[ portfolio-single ]=--
			case 'portfolio-single':
				$options['page_class'] = 'crane-portfolio-single';

				$options['groovy_menu'] = isset( $crane_options['portfolio-single-menu'] ) ? $crane_options['portfolio-single-menu'] : null;

				if ( 'inherit' === $options['breadcrumbs'] ) {
					$options['breadcrumbs'] = isset( $crane_options['breadcrumbs-portfolio'] ) ? $crane_options['breadcrumbs-portfolio'] : null;
				}

				if ( ! $options['breadcrumbs'] && class_exists( 'Redux' ) ) {
					$_breadcrumbs_type_opt = Redux::getField( 'crane_options', 'breadcrumbs-portfolio' );
					if ( isset( $_breadcrumbs_type_opt['default'] ) ) {
						$options['breadcrumbs'] = $_breadcrumbs_type_opt['default'];
					}
				}

				foreach ( $options['portfolio-single'] as $opt_key => $opt_val ) {
					if (
						! is_null( $Crane_Meta_Data->get( 'portfolio-single-' . $opt_key, $post_id ) ) &&
						'default' !== $Crane_Meta_Data->get( 'portfolio-single-' . $opt_key, $post_id )
					) {
						$options['portfolio-single'][ $opt_key ] = $Crane_Meta_Data->get( 'portfolio-single-' . $opt_key, $post_id );
					} else {
						$options['portfolio-single'][ $opt_key ] = isset( $crane_options[ 'portfolio-single-' . $opt_key ] ) ? $crane_options[ 'portfolio-single-' . $opt_key ] : true;
					}
				}

				break;


			// --=[ portfolio-archive ]=--
			case 'portfolio-archive':

				$options['page_class'] = 'crane-portfolio-archive';

				$options['groovy_menu'] = isset( $crane_options['portfolio-menu'] ) ? $crane_options['portfolio-menu'] : null;

				if ( $category_options && isset( $category_options['custom_options'] ) && $category_options['custom_options'] === '1' && class_exists( 'GroovyMenuCategoryPreset' ) ) {
					$groovyMenuPreset       = intval( GroovyMenuCategoryPreset::getCurrentPreset( $term_id ) );
					$options['groovy_menu'] = $groovyMenuPreset ? $groovyMenuPreset : ( isset( $crane_options['portfolio-menu'] ) ? $crane_options['portfolio-menu'] : null );
				}

				if ( isset( $crane_options['portfolio-footer_preset'] ) && 'default' !== $crane_options['portfolio-footer_preset'] ) {
					$options['footer_preset']     = $crane_options['portfolio-footer_preset'];
				}

				if ( isset( $crane_options['portfolio-footer_appearance'] ) && 'default' !== $crane_options['portfolio-footer_appearance'] ) {
					$options['footer_appearance'] = $crane_options['portfolio-footer_appearance'];
				}

				if ( $category_options && isset( $category_options['custom_options'] ) && $category_options['custom_options'] === '1' ) {
					if ( ! empty( $category_options['layout'] ) ) {
						if ( 'grid' === $category_options['layout'] ) {
							$options['template']                       = 'grid';
							$crane_options['portfolio-archive-layout'] = 'grid';
						} elseif ( 'masonry' === $category_options['layout'] ) {
							$options['template']                       = 'masonry';
							$crane_options['portfolio-archive-layout'] = 'masonry';
						}
					}

					$category_options_list = array(
						'layout_mode'                 => '',
						'img_proportion'              => '',
						'style'                       => '',
						'columns'                     => '',
						'image_resolution'            => '',
						'grid_spacing'                => '',
						'max_width'                   => '',
						'posts_limit'                 => '',
						'hover_style'                 => '',
						'shuffle_text'                => '',
						'show_categories'             => '',
						'show_custom_text'            => '',
						'show_imgtags'                => '',
						'show_title_description'      => '',
						'show_excerpt'                => '',
						'excerpt_strip_html'          => '',
						'excerpt_height'              => '',
						'show_read_more'              => '',
						'target'                      => '',
						'sortable'                    => '',
						'sortable_align'              => '',
						'sortable_style'              => '',
						'sortable_background_color'   => '',
						'sortable_text_color'         => '',
						'pagination_type'             => '',
						'pagination_color'            => '',
						'pagination_background'       => '',
						'pagination_color_hover'      => '',
						'pagination_background_hover' => '',
						'show_more_text'              => '',
						'orderby'                     => '',
						'order'                       => '',
						'custom_order'                => '',
					);

					foreach ( $category_options_list as $cat_index => $to_index ) {
						$opt_name_to = empty( $to_index ) ? 'portfolio-archive-' . $cat_index : $to_index;

						if ( isset( $category_options[ $cat_index ] ) && 'default' !== $category_options[ $cat_index ] ) {
							$crane_options[ $opt_name_to ] = $category_options[ $cat_index ];
						}
					}

					if ( isset( $category_options['has-sidebar'] ) && 'default' !== $category_options['has-sidebar'] ) {
						$options['has-sidebar'] = $category_options['has-sidebar'];
						$options['sidebar']     = ( isset( $category_options['sidebar'] ) && 'default' !== $category_options['sidebar'] ) ? $category_options['sidebar'] : $options['sidebar'];
						if ( isset( $category_options['sidebar-width'] ) && 'default' !== $category_options['sidebar-width'] ) {
							$options['sidebar_width'] = intval( $category_options['sidebar-width'] );
						}
						if ( isset( $category_options['content-width'] ) && 'default' !== $category_options['content-width'] ) {
							$options['content_width'] = intval( $category_options['content-width'] );
						}
						if ( isset( $category_options['sticky'] ) && 'default' !== $category_options['sticky'] ) {
							$options['sticky'] = intval( $category_options['sticky'] );
						}
						if ( isset( $category_options['sticky-offset'] ) && 'default' !== $category_options['sticky-offset'] ) {
							$options['sticky-offset'] = array( 'padding-top' => intval( $category_options['sticky-offset'] ) );
						}
					}

					if ( isset( $category_options['padding'] ) && 'default' !== $category_options['padding'] ) {
						$value_explode = is_string( $category_options['padding'] ) ? explode( '|', $category_options['padding'] ) : array();

						$field_params_value = array();

						if ( count( $value_explode ) === 3 ) {
							foreach ( $value_explode as $key => $param ) {
								switch ( $key ) {
									case 0 :
										$field_params_value['padding-top'] = $param;
										break;
									case 1 :
										$field_params_value['padding-bottom'] = $param;
										break;
									case 2 :
										$field_params_value['units'] = $param;
										break;
								}
							}

							$field_params_value['padding-top']    = $field_params_value['padding-top'] . $field_params_value['units'];
							$field_params_value['padding-bottom'] = $field_params_value['padding-bottom'] . $field_params_value['units'];

							$crane_options['portfolio-archive-padding'] = $field_params_value;

						}
					}
					if ( isset( $category_options['padding-mobile'] ) && 'default' !== $category_options['padding-mobile'] ) {
						$value_explode = is_string( $category_options['padding-mobile'] ) ? explode( '|', $category_options['padding-mobile'] ) : array();

						$field_params_value = array();

						if ( count( $value_explode ) === 3 ) {
							foreach ( $value_explode as $key => $param ) {
								switch ( $key ) {
									case 0 :
										$field_params_value['padding-top'] = $param;
										break;
									case 1 :
										$field_params_value['padding-bottom'] = $param;
										break;
									case 2 :
										$field_params_value['units'] = $param;
										break;
								}
							}

							$field_params_value['padding-top']    = $field_params_value['padding-top'] . $field_params_value['units'];
							$field_params_value['padding-bottom'] = $field_params_value['padding-bottom'] . $field_params_value['units'];

							$crane_options['portfolio-archive-padding-mobile'] = $field_params_value;

						}
					}

				} // custom taxonomy options.

				$options['breadcrumbs'] = isset( $crane_options['breadcrumbs-portfolio'] ) ? $crane_options['breadcrumbs-portfolio'] : null;
				if ( ! $options['breadcrumbs'] && class_exists( 'Redux' ) ) {
					$_breadcrumbs_type_opt = Redux::getField( 'crane_options', 'breadcrumbs-portfolio' );
					if ( isset( $_breadcrumbs_type_opt['default'] ) ) {
						$options['breadcrumbs'] = $_breadcrumbs_type_opt['default'];
					}
				}

				break;


			// --=[ shop-single ]=--
			case 'shop-single':
				$options['page_class'] = 'crane-shop-single';

				$options['groovy_menu'] = isset( $crane_options['shop-single-menu'] ) ? $crane_options['shop-single-menu'] : null;

				if ( 'inherit' === $options['breadcrumbs'] ) {
					$options['breadcrumbs'] = isset( $crane_options['breadcrumbs-shop'] ) ? $crane_options['breadcrumbs-shop'] : null;
				}

				if ( ! $options['breadcrumbs'] && class_exists( 'Redux' ) ) {
					$_breadcrumbs_type_opt = Redux::getField( 'crane_options', 'breadcrumbs-shop' );
					if ( isset( $_breadcrumbs_type_opt['default'] ) ) {
						$options['breadcrumbs'] = $_breadcrumbs_type_opt['default'];
					}
				}

				break;

			// --=[ shop ]=--
			case 'shop':
				$options['page_class'] = 'crane-shop-archive';

				if ( isset( $crane_options['shop-archive-sticky'] ) ) {
					$options['sticky'] = $crane_options['shop-archive-sticky'];
				}
				if ( isset( $crane_options['shop-archive-sticky-offset'] ) ) {
					$options['sticky-offset'] = $crane_options['shop-archive-sticky-offset'];
				}

				if ( $category_options && isset( $category_options['custom_options'] ) && $category_options['custom_options'] === '1' && class_exists( 'GroovyMenuCategoryPreset' ) ) {
					$groovyMenuPreset       = intval( GroovyMenuCategoryPreset::getCurrentPreset( $term_id ) );
					$options['groovy_menu'] = $groovyMenuPreset ? $groovyMenuPreset : ( isset( $crane_options['shop-menu'] ) ? $crane_options['shop-menu'] : null );
				} else {
					$options['groovy_menu'] = isset( $crane_options['shop-menu'] ) ? $crane_options['shop-menu'] : null;
				}

				if ( $category_options && isset( $category_options['custom_options'] ) && $category_options['custom_options'] === '1' ) {
					if ( isset( $category_options['has-sidebar'] ) && 'default' !== $category_options['has-sidebar'] ) {
						$options['has-sidebar'] = $category_options['has-sidebar'];
						$options['sidebar']     = ( isset( $category_options['sidebar'] ) && 'default' !== $category_options['sidebar'] ) ? $category_options['sidebar'] : $options['sidebar'];
						if ( isset( $category_options['sidebar-width'] ) && 'default' !== $category_options['sidebar-width'] ) {
							$options['sidebar_width'] = intval( $category_options['sidebar-width'] );
						}
						if ( isset( $category_options['content-width'] ) && 'default' !== $category_options['content-width'] ) {
							$options['content_width'] = intval( $category_options['content-width'] );
						}
						if ( isset( $category_options['sticky'] ) && 'default' !== $category_options['sticky'] ) {
							$options['sticky'] = intval( $category_options['sticky'] );
						}
						if ( isset( $category_options['sticky-offset'] ) && 'default' !== $category_options['sticky-offset'] ) {
							$options['sticky-offset'] = array( 'padding-top' => intval( $category_options['sticky-offset'] ) );
						}
					}

					if ( isset( $category_options['padding'] ) && 'default' !== $category_options['padding'] ) {
						$value_explode = is_string( $category_options['padding'] ) ? explode( '|', $category_options['padding'] ) : array();

						$field_params_value = array();

						if ( count( $value_explode ) === 3 ) {
							foreach ( $value_explode as $key => $param ) {
								switch ( $key ) {
									case 0 :
										$field_params_value['padding-top'] = $param;
										break;
									case 1 :
										$field_params_value['padding-bottom'] = $param;
										break;
									case 2 :
										$field_params_value['units'] = $param;
										break;
								}
							}

							$field_params_value['padding-top']    = $field_params_value['padding-top'] . $field_params_value['units'];
							$field_params_value['padding-bottom'] = $field_params_value['padding-bottom'] . $field_params_value['units'];

							$crane_options['shop-archive-padding'] = $field_params_value;

						}
					}
					if ( isset( $category_options['padding-mobile'] ) && 'default' !== $category_options['padding-mobile'] ) {
						$value_explode = is_string( $category_options['padding-mobile'] ) ? explode( '|', $category_options['padding-mobile'] ) : array();

						$field_params_value = array();

						if ( count( $value_explode ) === 3 ) {
							foreach ( $value_explode as $key => $param ) {
								switch ( $key ) {
									case 0 :
										$field_params_value['padding-top'] = $param;
										break;
									case 1 :
										$field_params_value['padding-bottom'] = $param;
										break;
									case 2 :
										$field_params_value['units'] = $param;
										break;
								}
							}

							$field_params_value['padding-top']    = $field_params_value['padding-top'] . $field_params_value['units'];
							$field_params_value['padding-bottom'] = $field_params_value['padding-bottom'] . $field_params_value['units'];

							$crane_options['shop-archive-padding-mobile'] = $field_params_value;

						}
					}

				} // custom taxonomy options.

				if ( crane_is_additional_woocommerce_page() ) {
					$options['has-sidebar'] = false;
				}

				break;


			// --=[ regular ]=--
			case 'regular-page':
				$options['page_class'] = 'crane-regular-page';

				$options['groovy_menu'] = isset( $crane_options['regular-page-menu'] ) ? $crane_options['regular-page-menu'] : null;
				$options['breadcrumbs'] = isset( $crane_options['breadcrumbs-regular'] ) ? $crane_options['breadcrumbs-regular'] : null;
				if ( ! $options['breadcrumbs'] && class_exists( 'Redux' ) ) {
					$_breadcrumbs_type_opt = Redux::getField( 'crane_options', 'breadcrumbs-regular' );
					if ( isset( $_breadcrumbs_type_opt['default'] ) ) {
						$options['breadcrumbs'] = $_breadcrumbs_type_opt['default'];
					}
					if ( isset( $category_options['sticky'] ) && 'default' !== $category_options['sticky'] ) {
						$options['sticky'] = intval( $category_options['sticky'] );
					}
					if ( isset( $category_options['sticky-offset'] ) && 'default' !== $category_options['sticky-offset'] ) {
						$options['sticky-offset'] = array( 'padding-top' => intval( $category_options['sticky-offset'] ) );
					}
				}

				break;


			// --=[ search ]=--
			case 'search':
				$options['page_class'] = 'crane-search-page';

				$options['groovy_menu'] = isset( $crane_options['search-menu'] ) ? $crane_options['search-menu'] : null;

				break;


			// --=[ 404 ]=--
			case '404':
				$options['page_class'] = 'crane-404-page';

				$options['groovy_menu'] = isset( $crane_options['404-menu'] ) ? $crane_options['404-menu'] : null;

				break;


			// --=[ default ]=--
			default:
				$options['page_class'] = 'crane-regular-page';

				break;
		}


		global $wp_query;
		$queried_post_id = isset( $wp_query->queried_object->ID ) ? $wp_query->queried_object->ID : null;

		if ( intval( get_option( 'page_for_posts' ) ) === $queried_post_id ) {
			$post_id = $queried_post_id;
		}

		if ( $options['meta_override'] && ( is_single() || is_page() || crane_is_product_woocommerce_page() || crane_check_404_page() || intval( get_option( 'page_for_posts' ) ) === $queried_post_id ) ) {
			$sidebar_pos = $Crane_Meta_Data->get( 'single-has-sidebar', $post_id );
			if ( $sidebar_pos && 'default' !== $sidebar_pos ) {
				$options['has-sidebar'] = $Crane_Meta_Data->get( 'single-has-sidebar', $post_id );
				if ( '0' !== $Crane_Meta_Data->get( 'single-sidebar', $post_id ) ) {
					$options['sidebar'] = $Crane_Meta_Data->get( 'single-sidebar', $post_id );
				}
				if ( $sidebar_pos && 'none' !== $sidebar_pos && 'default' !== $sidebar_pos ) {
					if ( $Crane_Meta_Data->get( 'override_sidebar_content_width', $post_id ) ) {
						$options['sidebar_width'] = $Crane_Meta_Data->get( 'single-sidebar-width', $post_id );
						$options['content_width'] = $Crane_Meta_Data->get( 'single-content-width', $post_id );
					}
				}
			}

			if ( $Crane_Meta_Data->get( 'breadcrumbs', $post_id ) && 'default' !== $Crane_Meta_Data->get( 'breadcrumbs', $post_id ) ) {
				$options['breadcrumbs'] = $Crane_Meta_Data->get( 'breadcrumbs', $post_id );
			}

			if (
				! is_null( $Crane_Meta_Data->get( 'show-prev-next-post', $post_id ) ) &&
				'default' !== $Crane_Meta_Data->get( 'show-prev-next-post', $post_id )
			) {
				$options['show-prev-next-post'] = $Crane_Meta_Data->get( 'show-prev-next-post', $post_id );
			}

		}

		if (
			! is_null( $Crane_Meta_Data->get( 'show-author-info', $post_id ) ) &&
			'default' !== $Crane_Meta_Data->get( 'show-author-info', $post_id )
		) {
			$options['show-author-info'] = $Crane_Meta_Data->get( 'show-author-info', $post_id );
		}

		if (
			! is_null( $Crane_Meta_Data->get( 'show-related-posts', $post_id ) ) &&
			'default' !== $Crane_Meta_Data->get( 'show-related-posts', $post_id )
		) {
			$options['show-related-posts'] = $Crane_Meta_Data->get( 'show-related-posts', $post_id );
		}

		$all_sidebars = Crane_Sidebars_Creator::get_sidebars();
		if ( ! isset( $all_sidebars[ $options['sidebar'] ] ) ) {
			$options['sidebar'] = 'crane_basic_sidebar';
		}

		if ( 'none' === $options['has-sidebar'] || ! is_active_sidebar( $options['sidebar'] ) ) {
			$options['has-sidebar'] = false;
		}

		return $options;
	}
}


if ( ! function_exists( 'crane_get_options_for_current_blog' ) ) {

	/**
	 * Get options of the current blog options.
	 *
	 * @return array
	 */
	function crane_get_options_for_current_blog() {
		$crane_override_options = crane_override_options();

		if ( ! empty( $crane_override_options['ct_vc_blog'] ) && is_array( $crane_override_options['ct_vc_blog'] ) ) {
			return $crane_override_options['ct_vc_blog'];
		}

		$prefix = 'blog';
		if ( is_single() ) {
			$prefix = 'blog-single';
		}

		global $crane_options;

		$blog_template = isset( $crane_options['blog-template'] ) ? $crane_options['blog-template'] : 'standard';
		$columns       = ( 'cell' === $blog_template ) ?
			( isset( $crane_options['blog-cell-columns'] ) ? $crane_options['blog-cell-columns'] : '' ) :
			( isset( $crane_options['blog-masonry-columns'] ) ? $crane_options['blog-masonry-columns'] : '' );

		$options = array(
			'layout'                 => $blog_template,
			'style'                  => isset( $crane_options['blog-style'] ) ? $crane_options['blog-style'] : '',
			'show_tags'              => isset( $crane_options['blog-show_tags'] ) ? $crane_options['blog-show_tags'] : '',
			'show_cats'              => isset( $crane_options['blog-show_cats'] ) ? $crane_options['blog-show_cats'] : '',
			'show_author'            => isset( $crane_options['blog-show_author'] ) ? $crane_options['blog-show_author'] : '',
			'show_pubdate'           => isset( $crane_options['blog-show_pubdate'] ) ? $crane_options['blog-show_pubdate'] : '',
			'show_title_description' => isset( $crane_options['blog-show_title_description'] ) ? $crane_options['blog-show_title_description'] : '',
			'show_excerpt'           => isset( $crane_options['blog-show_excerpt'] ) ? $crane_options['blog-show_excerpt'] : '',
			'excerpt_strip_html'     => isset( $crane_options['blog-excerpt_strip_html'] ) ? $crane_options['blog-excerpt_strip_html'] : '',
			'excerpt_height'         => isset( $crane_options['blog-excerpt_height'] ) ? $crane_options['blog-excerpt_height'] : 70,
			'show_read_more'         => isset( $crane_options['blog-show_read_more'] ) ? $crane_options['blog-show_read_more'] : '',
			'show_comment_link'      => isset( $crane_options[ $prefix . '-show-comment-counter' ] ) ? $crane_options[ $prefix . '-show-comment-counter' ] : '',
			'show_share_button'      => isset( $crane_options[ $prefix . '-show_share_button' ] ) ? $crane_options[ $prefix . '-show_share_button' ] : '',
			'show_post_meta'         => isset( $crane_options['blog-show_post_meta'] ) ? $crane_options['blog-show_post_meta'] : '',
			'img_proportion'         => isset( $crane_options['blog-img_proportion'] ) ? $crane_options['blog-img_proportion'] : '',
			'image_resolution'       => isset( $crane_options['blog-image_resolution'] ) ? $crane_options['blog-image_resolution'] : '',
			'grid_spacing'           => isset( $crane_options['blog-grid_spacing'] ) ? $crane_options['blog-grid_spacing'] : '',
			'padding'                => isset( $crane_options['blog-archive-padding'] ) ? $crane_options['blog-archive-padding'] : '',
			'columns'                => $columns,
			'max_width'              => isset( $crane_options['blog-max_width'] ) ? $crane_options['blog-max_width'] : '',
			'post_height_desktop'    => isset( $crane_options['blog-post_height_desktop'] ) ? $crane_options['blog-post_height_desktop'] : '',
			'post_height_mobile'     => isset( $crane_options['blog-post_height_mobile'] ) ? $crane_options['blog-post_height_mobile'] : '',
			'posts_limit'            => '',
			'pagination_type'        => 'wordpress',
			'author'                 => '',
			'category'               => '',
			'tag'                    => '',
			'order'                  => '',
			'orderby'                => '',
			'target'                 => isset( $crane_options['blog-target'] ) ? $crane_options['blog-target'] : '',
		);

		return $options;
	}
}

/**
 * Return unique string (randomize)
 *
 * @param bool|false $more_entropy
 *
 * @return string
 */
function crane_uniqid_base36( $more_entropy = false ) {
	$s = uniqid( '', $more_entropy );
	if ( ! $more_entropy ) {
		return base_convert( $s, 16, 36 );
	}
	$hex = substr( $s, 0, 13 );
	$dec = $s[13] . substr( $s, 15 ); // skip the dot

	return base_convert( $hex, 16, 36 ) . base_convert( $dec, 10, 36 );
}


function crane_is_enable_privacy_embeds( $without_embeds = false ) {
	global $crane_options;

	$is_enable_privacy   = false;
	$privacy_preferences = isset( $crane_options['privacy-preferences'] ) && $crane_options['privacy-preferences'];
	$privacy_embeds      = isset( $crane_options['privacy-embeds'] ) && $crane_options['privacy-embeds'];
	$privacy_services    = empty( $crane_options['privacy-services'] ) ? null : $crane_options['privacy-services'];

	if ( $without_embeds ) {
		$is_enable_privacy = $privacy_preferences && ( ! $privacy_embeds || ( $privacy_embeds && ! $privacy_services ) );
	} else {
		$is_enable_privacy = $privacy_preferences && $privacy_embeds;
	}

	return $is_enable_privacy;
}


if ( ! function_exists( 'crane_get_first_embed_media' ) ) {

	/**
	 * Get first embed media from post content
	 *
	 * @param $post_id
	 * @param $width
	 *
	 * @return bool|mixed|array
	 */
	function crane_get_first_embed_media( $post_id, $width ) {
		$post = get_post( $post_id );
		if ( ! $post ) {
			return false;
		}
		$post_format = get_post_format();
		if ( ! $post_format ) {
			return false;
		}
		$post_content = $post->post_content;

		$position_of_shortcode = [
			'embed'      => null,
			$post_format => null,
		];

		switch ( $post_format ) {

			case 'video':
				break;

			case 'audio':
				$position_of_shortcode['soundcloud'] = null;
				break;

			case 'gallery':
				$position_of_shortcode['gallery'] = null;
				break;

			case 'image':
				break;

			default:
				return false;
				break;

		}

		foreach ( $position_of_shortcode as $name => $data ) {
			$position_of_shortcode[ $name ] = stripos( $post_content, '[' . $name );
			if ( $position_of_shortcode[ $name ] === false ) {
				unset( $position_of_shortcode[ $name ] );
			}
		}

		if ( empty( $position_of_shortcode ) ) {

			return crane_get_media_by_first_url( $post_content, $post_format );

		}

		$shortcode_name = array_keys( $position_of_shortcode, min( $position_of_shortcode ) )[0];

		$pattern = '#\[(\[?)' . $shortcode_name . '(?![\w-])([^\]\/]*(?:\/(?!\])[^\]\/]*)*?)(?:(\/)\]|\](?:([^\[]*+(?:\[(?!\/\2\])[^\[]*+)*+)\[\/\2\])?)(\]?)([^\[]+)?#im';
		preg_match( $pattern, $post_content, $matches );

		if ( ! $matches ) {
			return false;
		}

		global $crane_options;
		$privacy_force_agree   = crane_get_privacy_cookie( 'force-agree', false, false );
		$privacy_cookie_data   = crane_get_privacy_cookie( 'embeds' );
		$enable_privacy_embeds = crane_is_enable_privacy_embeds();
		$privacy_services      = empty( $crane_options['privacy-services'] ) ? array() : $crane_options['privacy-services'];

		if ( 'embed' === $shortcode_name ) {

			if ( $matches[6] ) {

				$privacy_block_this_shortcode = false;

				if ( ! $privacy_force_agree && $enable_privacy_embeds ) {
					$embed_class = new WP_oEmbed();
					$provider    = $embed_class->get_provider( $matches[6], array( 'discover' => false ) );

					if ( $provider ) {
						foreach ( $privacy_services as $service ) {
							$url_embeds = stristr( $provider, $service, true );
							if ( false !== $url_embeds ) {
								if ( isset( $privacy_cookie_data[ $service ] ) ) {
									if ( ! $privacy_cookie_data[ $service ] ) {
										$privacy_block_this_shortcode = true;
									}
									break;
								} elseif ( in_array( $service, $privacy_services ) ) {
									$privacy_block_this_shortcode = true;
									break;
								}
							}
						}

					}
				}

				if ( $privacy_block_this_shortcode ) {
					$output = crane_get_privacy_of_embeds_text();
				} else {
					$output = wp_oembed_get( $matches[6], array(
						'width' => $width,
						'class' => 'embeded-content'
					) );
				}

				return [
					'shortcode' => $shortcode_name,
					'html'      => $output,
				];
			}
		} else {
			if ( $matches[2] ) {

				$privacy_block_this_shortcode = false;

				if ( ! $privacy_force_agree && $enable_privacy_embeds ) {
					foreach ( $privacy_services as $service ) {
						$url_embeds = stristr( $matches[2], $service, true );
						if ( false !== $url_embeds ) {
							if ( isset( $privacy_cookie_data[ $service ] ) ) {
								if ( ! $privacy_cookie_data[ $service ] ) {
									$privacy_block_this_shortcode = true;
								}
								break;
							} elseif ( in_array( $service, $privacy_services ) ) {
								$privacy_block_this_shortcode = true;
								break;
							}
						}
					}
				}

				if ( $privacy_block_this_shortcode ) {
					$output = crane_get_privacy_of_embeds_text();
				} else {
					$output = do_shortcode( '[' . $shortcode_name . $matches[2] . ']' );
				}

				if ( 'gallery' === $post_format && $output ) {

					$output = preg_replace( '#gallery-columns-..#m', '', $output, 1 );

					$output = preg_replace( '#href=(["\'])(.*?)(["\'])#m', 'href="' . esc_url( get_permalink( $post->ID ) ) . '"', $output );

					$layout_options = crane_get_options_for_current_blog();
					if ( isset( $layout_options['target'] ) && $layout_options['target'] ) {
						if ( 'blank' === $layout_options['target'] ) {
							$output = str_replace( '<a ', '<a target="_blank" ', $output );
						}
					}
				}

				return [ 'shortcode' => $shortcode_name, 'html' => $output ];
			}
		}

		return false;
	}
}


if ( ! function_exists( 'crane_get_media_by_first_url' ) ) {

	/**
	 * Get first embed media from post content
	 *
	 * @param string $post_content
	 * @param string $post_format
	 *
	 * @return bool|mixed|array
	 */
	function crane_get_media_by_first_url( $post_content, $post_format ) {

		$url_mask = '`(http|ftp|https):\/\/([\w_-]+(?:(?:\.[\w_-]+)+))([\w.,@?^=%&:/~+#-]*[\w@?^=%&/~+#-])?`i';

		if ( 'video' === $post_format ) {

			if ( ! empty( $post_content ) ) {
				$output = preg_match( $url_mask, $post_content, $matches );

				if ( ! empty( $matches[3] ) ) {

					$output = do_shortcode( '[video src="' . $matches[0] . '"]' );

					return [ 'shortcode' => $post_format, 'html' => $output ];
				}
			}

		} elseif ( 'audio' === $post_format ) {

			if ( ! empty( $post_content ) ) {
				$output = preg_match( $url_mask, $post_content, $matches );

				if ( ! empty( $matches[3] ) && ! empty( $matches[2] ) && 'soundcloud.com' === $matches[2] ) {

					$output = do_shortcode( '[soundcloud url="' . $matches[0] . '"]' );

					//$output = wp_oembed_get( $matches[0], array( 'class' => 'embeded-content' ) ); // does not consider privacy settings

					return [ 'shortcode' => 'soundcloud', 'html' => $output ];

				} elseif ( ! empty( $matches[3] ) && ! empty( $matches[2] ) && array_key_exists( $matches[2], crane_get_privacy_elements( true ) ) ) {


					$output = do_shortcode( '[embed]' . $matches[0] . '[/embed]' );

					return [ 'shortcode' => $matches[2], 'html' => $output ];

				} elseif ( ! empty( $matches[3] ) ) {

					$output = do_shortcode( '[embed]' . $matches[0] . '[/embed]' );

					return [ 'shortcode' => $post_format, 'html' => $output ];
				}
			}

		}


		return false;
	}
}


function crane_checking_privacy_of_embeds( $html, $url, $attr, $post_ID ) {

	global $crane_options;

	if ( ! crane_is_enable_privacy_embeds() ) {
		return $html;
	}

	if ( crane_get_privacy_cookie( 'force-agree', false, false ) ) {
		return $html;
	}

	$privacy_cookie_data = crane_get_privacy_cookie( 'embeds' );

	$privacy_services = empty( $crane_options['privacy-services'] ) ? array() : $crane_options['privacy-services'];

	$embed_class = new WP_oEmbed();
	$provider    = $embed_class->get_provider( $url, array( 'discover' => false ) );

	$for_check_embeds = array();

	foreach ( $privacy_services as $service_by_config ) {
		if ( array_key_exists( $service_by_config, crane_get_privacy_elements() ) ) {

			$allow_service = ( isset( $privacy_cookie_data[ $service_by_config ] ) && $privacy_cookie_data[ $service_by_config ] ) ? true : false;

			// false mean block embed
			$for_check_embeds[ $service_by_config ] = $allow_service;
		}
	}

	if ( empty( $for_check_embeds ) ) {
		return $html;
	}

	$block_this_embed = false;

	foreach ( $for_check_embeds as $service => $perm ) {
		if ( ! $perm && strpos( $provider, $service ) !== false ) {
			$block_this_embed = true;
		}
	}

	if ( $block_this_embed ) {
		$html = ( is_archive() ) ? '&nbsp;' : crane_get_privacy_of_embeds_text();
	}

	return $html;
}

add_filter( 'embed_oembed_html', 'crane_checking_privacy_of_embeds', 999, 4 );


function crane_checking_privacy_of_wp_video( $html, $attr, $content, $instance ) {

	$url = isset( $attr['src'] ) ? $attr['src'] : null;

	if ( empty( $url ) ) {
		return $html;
	}

	global $crane_options;

	if ( ! crane_is_enable_privacy_embeds() ) {
		return $html;
	}

	if ( crane_get_privacy_cookie( 'force-agree', false, false ) ) {
		return $html;
	}

	$privacy_cookie_data = crane_get_privacy_cookie( 'embeds' );

	$privacy_services = empty( $crane_options['privacy-services'] ) ? array() : $crane_options['privacy-services'];

	$embed_class = new WP_oEmbed();
	$provider    = $embed_class->get_provider( $url, array( 'discover' => false ) );

	$for_check_embeds = array();

	foreach ( $privacy_services as $service_by_config ) {
		if ( array_key_exists( $service_by_config, crane_get_privacy_elements() ) ) {

			$allow_service = ( isset( $privacy_cookie_data[ $service_by_config ] ) && $privacy_cookie_data[ $service_by_config ] ) ? true : false;

			// false mean block embed
			$for_check_embeds[ $service_by_config ] = $allow_service;
		}
	}

	if ( empty( $for_check_embeds ) ) {
		return $html;
	}

	$block_this_embed = false;

	foreach ( $for_check_embeds as $service => $perm ) {
		if ( ! $perm && strpos( $provider, $service ) !== false ) {
			$block_this_embed = true;
		}
	}

	if ( $block_this_embed ) {
		$html = crane_get_privacy_of_embeds_text();
	}

	return $html;
}

add_filter( 'wp_video_shortcode_override', 'crane_checking_privacy_of_wp_video', 999, 4 );


if ( ! function_exists( 'crane_get_terms_by_taxonomy' ) ) {

	/**
	 * Get taxonomy items (terms)
	 *
	 * @param string $taxonomy_name
	 * @param string $term_ids
	 *
	 * @return array
	 */
	function crane_get_terms_by_taxonomy( $taxonomy_name, $term_ids = '' ) {
		static $cache = array();

		$taxonomy_name = esc_attr( $taxonomy_name );

		$terms = array();
		if ( ! $taxonomy_name ) {
			return $terms;
		}

		$term_ids_cache = $term_ids ? md5( $term_ids ) : 'none';

		if ( isset( $cache[ $taxonomy_name ][ $term_ids_cache ] ) ) {
			return $cache[ $taxonomy_name ][ $term_ids_cache ];
		}

		if ( ! empty( $term_ids ) && $all_term = crane_get_terms_by_taxonomy( $taxonomy_name ) ) {
			$term_ids = empty( $term_ids ) ? array() : explode( ',', $term_ids );
			foreach ( $all_term as $term ) {
				if ( in_array( $term['slug'], $term_ids, true ) ) {
					$terms[] = $term['id'];
				}
			}
			$term_ids = ( ! empty( $terms ) ? implode( ',', $terms ) : array() );
		}

		$args = array(
			'taxonomy'   => $taxonomy_name,
			'hide_empty' => false,
			'include'    => $term_ids
		);

		$tax_terms = get_terms( $args );

		$terms = array();
		if ( ! is_wp_error( $tax_terms ) ) {
			foreach ( $tax_terms as $term ) {
				$terms[] = array(
					'id'    => $term->term_id,
					'title' => $term->name,
					'slug'  => $term->slug
				);
			}
		}

		$cache[ $taxonomy_name ][ $term_ids_cache ] = $terms;

		return $terms;
	}

}


if ( ! function_exists( 'crane_single_prev_next_link' ) ) {

	/**
	 * Get prev\next links
	 *
	 * @param $output
	 * @param $format
	 * @param $link
	 * @param $post
	 * @param $adjacent
	 *
	 * @return mixed
	 */
	function crane_single_prev_next_link( $output, $format, $link, $post, $adjacent ) {
		$post_id = is_object( $post ) ? $post->ID : null;

		$output = str_replace( '<a ', '<a class="single-post-nav crane-link-' . ( $adjacent === 'next' ? 'next' : 'previous' ) . '" ', $output );
		$thumb  = get_the_post_thumbnail( $post_id, 'thumbnail' );

		if ( empty( $thumb ) ) {
			$img = crane_get_thumb( $post_id, array(
				crane_get_image_width( 'thumbnail' ),
				crane_get_image_height( 'thumbnail' )
			) );
			if ( $img ) {
				$thumb = '<img src="' . esc_url( $img ) . '" class="attachment-thumbnail wp-post-image" alt="thumbnail">';
			} else {
				$thumb = '';
			}

		}

		if ( ! empty( $thumb ) ) {
			$thumb = '<span class="single-post-nav-img">' . $thumb . '</span>';
		}

		$output = str_replace( '%image', $thumb, $output );
		if ( ! $thumb ) {
			$output = str_replace( 'single-post-nav-img', 'single-post-nav-img' . crane_get_placeholder_html_class( $thumb ), $output );
		}

		if ( is_single() && function_exists( 'is_product' ) && is_product() && function_exists( 'wc_get_product' ) && function_exists( 'wc_price' ) ) {
			$_product = wc_get_product( $post_id );
			if ( is_object( $_product ) && method_exists( $_product, 'get_price' ) ) {
				$product_price = wc_get_price_to_display( $_product );
			    $product_price = $product_price ? wc_price( $product_price ) : '';
			} else {
				$product_price = '';
			}
			$output = str_replace( '%price', $product_price, $output );
		} else {
			$output = str_replace( '%price', '', $output );
		}

		return $output;
	}
}
add_filter( 'next_post_link', 'crane_single_prev_next_link', 10, 5 );
add_filter( 'previous_post_link', 'crane_single_prev_next_link', 10, 5 );


/**
 * Get size information for all currently-registered image sizes.
 *
 * @global $_wp_additional_image_sizes
 * @uses   get_intermediate_image_sizes()
 * @return array $sizes Data for all currently-registered image sizes.
 */
function crane_get_image_sizes() {
	global $_wp_additional_image_sizes;

	$sizes = array();

	foreach ( get_intermediate_image_sizes() as $_size ) {
		if ( in_array( $_size, array( 'thumbnail', 'medium', 'medium_large', 'large' ) ) ) {
			$sizes[ $_size ]['width']  = get_option( "{$_size}_size_w" );
			$sizes[ $_size ]['height'] = get_option( "{$_size}_size_h" );
			$sizes[ $_size ]['crop']   = (bool) get_option( "{$_size}_crop" );
		} elseif ( isset( $_wp_additional_image_sizes[ $_size ] ) ) {
			$sizes[ $_size ] = array(
				'width'  => $_wp_additional_image_sizes[ $_size ]['width'],
				'height' => $_wp_additional_image_sizes[ $_size ]['height'],
				'crop'   => $_wp_additional_image_sizes[ $_size ]['crop'],
			);
		}
	}

	return $sizes;
}

/**
 * Get size information for a specific image size.
 *
 * @uses   crane_get_image_sizes()
 *
 * @param  string $size The image size for which to retrieve data.
 *
 * @return bool|array $size Size data about an image size or false if the size doesn't exist.
 */
function crane_get_image_size( $size ) {
	$sizes = crane_get_image_sizes();

	if ( isset( $sizes[ $size ] ) ) {
		return $sizes[ $size ];
	}

	return false;
}

/**
 * Get the width of a specific image size.
 *
 * @uses   crane_get_image_size()
 *
 * @param  string $size The image size for which to retrieve data.
 *
 * @return bool|string $size Width of an image size or false if the size doesn't exist.
 */
function crane_get_image_width( $size ) {
	if ( ! $size = crane_get_image_size( $size ) ) {
		return false;
	}

	if ( isset( $size['width'] ) ) {
		return $size['width'];
	}

	return false;
}


/**
 * Get the height of a specific image size.
 *
 * @uses   crane_get_image_size()
 *
 * @param  string $size The image size for which to retrieve data.
 *
 * @return bool|string $size Height of an image size or false if the size doesn't exist.
 */
function crane_get_image_height( $size ) {
	if ( ! $size = crane_get_image_size( $size ) ) {
		return false;
	}

	if ( isset( $size['height'] ) ) {
		return $size['height'];
	}

	return false;
}


if ( ! function_exists( 'crane_get_image_sizes_select_values' ) ) {
	/**
	 * Get image size values for HTML select
	 *
	 * @return array
	 */
	function crane_get_image_sizes_select_values() {

		$_sizes = array();

		foreach ( crane_get_image_sizes() as $size_name => $size_data ) {

			if ( $size_name === 'full' ) {
				$title = __( 'Full Size', 'crane' );
			} else {

				$title = ( ( $size_data['width'] === 0 ) ? __( 'Any', 'crane' ) : $size_data['width'] );
				$title .= ' x ';
				$title .= ( $size_data['height'] === 0 ) ? __( 'Any', 'crane' ) : $size_data['height'];

				if ( $size_data['crop'] ) {
					$title .= ' ' . __( 'cropped', 'crane' );
				}

			}

			$_sizes[ $size_name ] = $title;

		}

		$_sizes['full'] = esc_html__( 'Full size (original)', 'crane' );


		return $_sizes;
	}
}


if ( ! function_exists( 'crane_get_thumb' ) ) {
	/**
	 * Portfolio thumb
	 *
	 * @param $post_id
	 * @param $size
	 * @param bool|false $return_array
	 * @param bool $is_attachment_id
	 *
	 * @return array|false|mixed
	 */
	function crane_get_thumb( $post_id, $size, $return_array = false, $is_attachment_id = false ) {
		$thumb = '';
		if ( ! $is_attachment_id ) {
			$thumb_id = (int) get_post_thumbnail_id( $post_id );
		} else {
			$thumb_id = (int) $post_id;
		}
		if ( $thumb_id ) {
			$attachment_src = wp_get_attachment_image_src( $thumb_id, $size );
			if ( is_array( $attachment_src ) && isset( $attachment_src[0] ) && $attachment_src[0] ) {
				$thumb = $attachment_src;
			}
		}

		if ( $thumb ) {

			return $return_array ? $thumb : $thumb[0];

		} else {

			return false;

		}

	}
}


if ( ! function_exists( 'crane_get_placeholder_html_class' ) ) {
	/**
	 * Return placeholder css classes
	 *
	 * @param bool $is_image If empty of false this function echo placeholder string class
	 *
	 * @return string
	 */
	function crane_get_placeholder_html_class( $is_image = false ) {

		$show_placeholder = $is_image ? false : true;

		global $crane_options;
		if ( ! isset( $crane_options['show_featured_placeholders'] ) || ! $crane_options['show_featured_placeholders'] ) {
			$show_placeholder = false;
		}

		return $show_placeholder ? ' crane-placeholder crane-placeholder-' . rand( 1, 10 ) : '';
	}
}


if ( ! function_exists( 'crane_custom_css_4_customize' ) ) {
	/**
	 * Add custom CSS for customize
	 */
	function crane_custom_css_4_customize() {

		$css = '';

		if ( is_customize_preview() && class_exists( 'ReduxFramework' ) && class_exists( 'Redux_Functions' ) ) {

			global $crane_options;
			$redux = ReduxFrameworkInstances::get_instance( 'crane_options' );

			$compiler_fields = ( isset( $redux->compiler_fields ) && is_array( $redux->compiler_fields ) ) ? $redux->compiler_fields : array();
			$parsed_css      = '';

			if ( ! empty( $redux->args['opt_name'] ) && ! empty( $redux->sections ) && is_array( $redux->sections ) ) {

				foreach ( $redux->sections as $section ) {

					if ( empty( $section['fields'] ) && ! is_array( $section['fields'] ) ) {
						continue;
					}

					foreach ( $section['fields'] as $field_params ) {
						$field_id = $field_params['id'];

						if ( ! isset( $compiler_fields[ $field_id ] ) || ! $compiler_fields[ $field_id ] ) {
							continue;
						}

						$field_compiler = ( isset( $field_params['compiler'] ) && ! empty( $field_params['compiler'] ) && is_array( $field_params['compiler'] ) ) ? $field_params['compiler'] : null;

						if ( empty( $field_compiler ) ) {
							continue;
						}

						$mode = ( isset( $field_params['mode'] ) && ! empty( $field_params['mode'] ) ? $field_params['mode'] : $field_params['type'] );

						$_val = $crane_options[ $field_id ];

						foreach ( $field_compiler as $element => $selector ) {

							$css_prop = $element;
							if ( ! is_string( $element ) ) {
								$css_prop = $mode;
							}

							$cssStyle = '';
							if ( is_string( $_val ) ) {
								$cssStyle .= $css_prop . ':' . $_val . ';';
							} elseif ( is_array( $_val ) ) {
								foreach ( $_val as $param => $val ) {
									if ( is_array( $val ) ) {
										continue;
									}
									$cssStyle .= $param . ':' . $val . ';';
								}

							}

							if ( ! empty( $cssStyle ) ) {
								$parsed_css .= $selector . '{' . $cssStyle . '}';
							}

						}
					}
				}

			}

			$css .= '
			/* START rendered customize to styles */
			' . $parsed_css . '
			/* END of rendered customize to styles */
			';
			$css .= crane_redux_compiler_css( $crane_options, '' );
		}

		return $css;

	}
}


if ( ! function_exists( 'crane_is_import_process' ) ) {
	/**
	 * Check is now import process work
	 *
	 * @return bool
	 */
	function crane_is_import_process( $is_import = false ) {

		static $import_process_flag = false;

		if ( $is_import ) {
			$import_process_flag = $is_import;
		}

		return $import_process_flag;
	}
}


if ( ! function_exists( 'crane_wc_get_attribute_taxonomies' ) ) {
	/**
	 * Get attribute taxonomies for woocommerce
	 *
	 * @return array
	 */
	function crane_wc_get_attribute_taxonomies() {

		static $attributes = array();

		if ( ! empty( $attributes ) ) {
			return $attributes;
		}

		if ( defined( 'WP_LOAD_IMPORTERS' ) && WP_LOAD_IMPORTERS ) {
			return $attributes;
		}

		if ( ! get_transient( 'wc_attribute_taxonomies' ) ) {
			return $attributes;
		}

		if ( ! crane_is_import_process() && function_exists( 'wc_get_attribute_taxonomies' ) ) {
			foreach ( wc_get_attribute_taxonomies() as $wc_attribute ) {
				if ( is_object( $wc_attribute ) && ( isset( $wc_attribute->attribute_name ) && isset( $wc_attribute->attribute_label ) ) ) {
					$attributes[ wc_attribute_taxonomy_name( $wc_attribute->attribute_name ) ] = $wc_attribute->attribute_label;
				}
			}
		}

		return $attributes;
	}
}


/**
 * Get any posts content by ID
 *
 * @param $post_id
 *
 * @return bool|string
 */
function crane_get_the_content_by_id( $post_id ) {
	$page_data = get_post( $post_id );
	if ( $page_data ) {
		return $content = apply_filters( 'the_content', $page_data->post_content );
	} else {
		return false;
	}
}


/**
 * Insert array element after another array element
 *
 * @param $arr
 * @param $insert
 * @param $position
 *
 * @return array
 */
function crane_array_insert( $arr, $insert, $position ) {

	foreach ( $arr as $key => $value ) {
		$result[ $key ] = $value;
		if ( $key === $position ) {
			foreach ( $insert as $ikey => $ivalue ) {
				$result[ $ikey ] = $ivalue;
			}
		}

	}

	return $result;
}


if ( ! function_exists( 'crane_debug_value' ) ) {
	/**
	 * Write some variable value to debug file, when it's hard to output it directly
	 *
	 * @param $value
	 * @param bool|FALSE $with_backtrace
	 * @param bool $append
	 */
	function crane_debug_value( $value, $with_backtrace = false, $append = false ) {
		if ( ! defined( 'FS_METHOD' ) ) {
			define( 'FS_METHOD', 'direct' );
		}
		global $wp_filesystem;
		if ( empty( $wp_filesystem ) ) {
			if ( file_exists( ABSPATH . '/wp-admin/includes/file.php' ) ) {
				require_once ABSPATH . '/wp-admin/includes/file.php';
				WP_Filesystem();
			}
		}
		if ( empty( $wp_filesystem ) ) {
			return;
		}

		$data = '';
		static $auto_append = false;

		$data .= '[' . date( 'm/d/Y h:i:s a', time() ) . ']' . "\n";

		if ( $with_backtrace ) {
			$backtrace = debug_backtrace();
			array_shift( $backtrace );
			$data .= print_r( $backtrace, true ) . ":\n";
		}

		$upload_dir_data = wp_upload_dir();
		$basedir         = get_template_directory();
		if ( isset( $upload_dir_data['basedir'] ) ) {
			$basedir = $upload_dir_data['basedir'];
		}

		$filename = $basedir . '/crane_debug.html';

		if ( file_exists( $filename && ! is_writable( $filename ) ) ) {
			$wp_filesystem->chmod( $filename, 0666 );
		}

		ob_start();
		var_dump( $value );
		$data .= ob_get_clean() . "\n\n";
		$is_append = $append ? : $auto_append;


		if ( is_writable( $filename ) || ( ! file_exists( $filename ) && is_writable( dirname( $filename ) ) ) ) {
			if ( $is_append ) {
				$data = $wp_filesystem->get_contents( $filename ) . $data;
			}

			$wp_filesystem->put_contents( $filename, $data );

		}


		$auto_append = true;

	}
}


if ( ! function_exists( 'crane_debug_message' ) ) {
	/**
	 * Write debug message
	 *
	 * @param $value
	 * @param bool|FALSE $with_backtrace
	 *
	 * @return null
	 */
	function crane_debug_message( $message ) {
		global $crane_options;

		if ( ! isset( $crane_options['debug'] ) || ! $crane_options['debug'] ) {
			return null;
		}
		if ( ! defined( 'FS_METHOD' ) ) {
			define( 'FS_METHOD', 'direct' );
		}
		global $wp_filesystem;
		if ( empty( $wp_filesystem ) ) {
			if ( file_exists( ABSPATH . '/wp-admin/includes/file.php' ) ) {
				require_once ABSPATH . '/wp-admin/includes/file.php';
				WP_Filesystem();
			}
		}
		if ( empty( $wp_filesystem ) ) {
			return;
		}

		$data = '[' . date( 'm/d/Y h:i:s a', time() ) . ']' . " ";

		$upload_dir_data = wp_upload_dir();
		$basedir         = get_template_directory();
		if ( isset( $upload_dir_data['basedir'] ) ) {
			$basedir = $upload_dir_data['basedir'];
		}

		$filename = $basedir . '/crane_debug_messages.html';

		$data .= $message . "\n<br>\n";

		if ( file_exists( $filename && ! is_writable( $filename ) ) ) {
			$wp_filesystem->chmod( $filename, 0666 );
		}

		if ( is_writable( $filename ) || ( ! file_exists( $filename ) && is_writable( dirname( $filename ) ) ) ) {

			$data = $wp_filesystem->get_contents( $filename ) . $data;
			$wp_filesystem->put_contents( $filename, $data, FS_CHMOD_FILE );

		}

	}
}


/**
 * Function make update custom-style.css
 *
 * @return null
 */
function crane_update_custom_style_css() {
	global $crane_options;

	if ( ! class_exists( 'Redux' ) && ! class_exists( 'ReduxFrameworkInstances' ) ) {
		return null;
	}

	$redux = ReduxFrameworkInstances::get_instance( 'crane_options' );

	try {
		if ( isset ( $redux->validation_ran ) ) {
			unset ( $redux->validation_ran );
		}

		$_options = $crane_options;

		if ( is_array( $_options ) && isset( $_options['redux-backup'] ) ) {
			unset( $_options['redux-backup'] );
		}

		crane_redux_compiler_action( $_options, $redux->compilerCSS, '' );

		crane_debug_message( esc_html__( 'Update custom-style.css', 'crane' ) );

	} catch ( Exception $e ) {
		crane_debug_message( sprintf( esc_html__( 'Error! when update custom-style.css: %s', 'crane' ), $e->getMessage() ) );
	}

	return true;

}

function crane_get_video_patterns() {
	return array(
		'youtube_watch'      => '#https?://((m|www)\.)?youtube\.com/watch.*#i',
		'youtube_playlist'   => '#https?://((m|www)\.)?youtube\.com/playlist.*#i',
		'youtube_be'         => '#https?://youtu\.be/.*#i',
		'vimeo'              => '#https?://(.+\.)?vimeo\.com/.*#i',
		'hulu'               => '#https?://(www\.)?hulu\.com/watch/.*#i',
		'wordpress'          => '#https?://wordpress\.tv/.*#i',
		'animoto'            => '#https?://(www\.)?(animoto|video214)\.com/play/.*#i',
		'videopress'         => '#https?://videopress\.com/v/.*#i',
		'facebook_videos'    => '#https?://www\.facebook\.com/.*/videos/.*#i',
		'facebook_video_php' => '#https?://www\.facebook\.com/video\.php.*#i',
	);
}


function crane_is_video_pattern( $url ) {
	foreach ( crane_get_video_patterns() as $provider => $regex ) {
		if ( ! preg_match( $regex, $url, $matches ) ) {
			continue;
		}

		return true;
		break;
	}

	return false;
}

function crane_get_logo_html() {
	$return = '';

	global $crane_options;

	if ( isset( $crane_options['header_logo_switcher'] ) ) {

		if ( $crane_options['header_logo_switcher'] ) {
			if ( ! empty( $crane_options['header_logo_image']['url'] ) ) {
				$return = '<img src="' . esc_url( $crane_options['header_logo_image']['url'] ) . '" class="attachment-thumbnail wp-post-image" alt="logo">';
			}
		} else {
			if ( ! empty( $crane_options['header_logo_text'] ) ) {
				$text_logo = esc_textarea( $crane_options['header_logo_text'] );
			} else {
				$text_logo = get_bloginfo( 'name', 'display' );
			}

			$return = '<span class="crane-text-logo">' . $text_logo . '</span>';
		}

	}

	if ( ! empty( $return ) ) {
		$return = '<a href="' . esc_url( home_url( '/' ) ) . '" title="' . esc_attr( get_bloginfo( 'name', 'display' ) ) . '" rel="home">' . $return . '</a>';
	}


	return $return;
}

function crane_page_type() {
	global $wp_query;
	$page_type = 'notfound';

	if ( $wp_query->is_page ) {
		$page_type = is_front_page() ? 'front' : 'page';
	} elseif ( $wp_query->is_home ) {
		$page_type = 'home';
	} elseif ( $wp_query->is_single ) {
		$page_type = ( $wp_query->is_attachment ) ? 'attachment' : 'single';
	} elseif ( $wp_query->is_category ) {
		$page_type = 'category';
	} elseif ( $wp_query->is_tag ) {
		$page_type = 'tag';
	} elseif ( $wp_query->is_tax ) {
		$page_type = 'tax';
	} elseif ( $wp_query->is_archive ) {
		if ( $wp_query->is_day ) {
			$page_type = 'day';
		} elseif ( $wp_query->is_month ) {
			$page_type = 'month';
		} elseif ( $wp_query->is_year ) {
			$page_type = 'year';
		} elseif ( $wp_query->is_author ) {
			$page_type = 'author';
		} else {
			$page_type = 'archive';
		}
	} elseif ( $wp_query->is_search ) {
		$page_type = 'search';
	} elseif ( $wp_query->is_404 ) {
		$page_type = 'notfound';
	}

	return $page_type;
}


if ( ! function_exists( 'crane_override_options' ) ) {
	function crane_override_options( $elements = array() ) {

		static $override_options = array();

		if ( ! empty( $elements ) ) {
			$override_options = array_merge( $override_options, $elements );
		}

		return $override_options;

	}
}


if ( ! function_exists( 'crane_alowed_tags' ) ) {
	/**
	 * Return array of allowed html tags
	 *
	 * @param bool $enable_script if true allowed html tag script
	 *
	 * @return array
	 */
	function crane_alowed_tags( $enable_script = false ) {

		$default_attr = array(
			'id'             => array(),
			'class'          => array(),
			'style'          => array(),
			'title'          => array(),
			'data'           => array(),
			'data-mce-id'    => array(),
			'data-mce-style' => array(),
			'data-mce-bogus' => array(),
		);

		$allowed_tags = array(
			'p'          => $default_attr,
			'div'        => $default_attr,
			'a'          => array_merge( $default_attr, array(
				'href'    => array(),
				'onclick' => array(),
				'target'  => array( '_blank', '_top', '_self' ),
			) ),
			'img'        => array_merge( $default_attr, array(
				'src'      => array(),
				'srcset'   => array(),
				'width'    => array(),
				'height'   => array(),
				'alt'      => array(),
				'align'    => array(),
				'hspace'   => array(),
				'vspace'   => array(),
				'sizes'    => array(),
				'longdesc' => array(),
				'border'   => array(),
				'usemap'   => array(),
			) ),
			'span'       => $default_attr,
			'code'       => $default_attr,
			'strong'     => $default_attr,
			'u'          => $default_attr,
			'i'          => $default_attr,
			'q'          => $default_attr,
			'b'          => $default_attr,
			'ul'         => $default_attr,
			'ol'         => $default_attr,
			'li'         => $default_attr,
			'br'         => $default_attr,
			'hr'         => $default_attr,
			'blockquote' => $default_attr,
			'del'        => $default_attr,
			'strike'     => $default_attr,
			'em'         => $default_attr,
			'noscript'   => array(),
		);

		if ( $enable_script ) {
			$allowed_tags['script'] = array(
				'type'    => array(),
				'async'   => array(),
				'charset' => array(),
				'defer'   => array(),
				'src'     => array(),
			);
		}

		return $allowed_tags;
	}
}


if ( ! function_exists( 'crane_alowed_tags_head' ) ) {
	/**
	 * Return array of allowed html tags for HEAD
	 *
	 * @return array
	 */
	function crane_alowed_tags_head() {

		$default_attr = array(
			'id'   => array(),
			'data' => array(),
		);

		$allowed_tags = array(
			'script' => array_merge( $default_attr, array(
				'type'    => array(),
				'async'   => array(),
				'charset' => array(),
				'defer'   => array(),
				'src'     => array(),
			) ),
			'style'  => array_merge( $default_attr, array(
				'type'  => array(),
				'media' => array(),
			) ),
			'link'   => array_merge( $default_attr, array(
				'type'        => array(),
				'rel'         => array(),
				'rev'         => array(),
				'sizes'       => array(),
				'href'        => array(),
				'hreflang'    => array(),
				'media'       => array(),
				'crossorigin' => array(),
				'target'      => array(),
			) ),
			'meta'   => array_merge( $default_attr, array(
				'name'       => array(),
				'content'    => array(),
				'http-equiv' => array(),
				'charset'    => array(),
			) ),
		);


		return $allowed_tags;
	}
}


/**
 * @param string $url
 *
 * @return string
 */
function crane_redux_sym_link_url( $url = '' ) {

	if ( is_admin() ) {
		$url = get_template_directory_uri() . '/admin/redux-framework/';
	}

	return $url;
}


if ( ! function_exists( 'crane_hex2rgba' ) ) {
	/**
	 * Convert hexdec color string to rgb(a) string
	 */

	function crane_hex2rgba( $color, $opacity = false ) {

		$default = 'rgb(0,0,0)';

		// Return default if no color provided
		if ( empty( $color ) ) {
			return $default;
		}

		// Sanitize $color if "#" is provided
		if ( $color[0] === '#' ) {
			$color = substr( $color, 1 );
		}

		// Check if color has 6 or 3 characters and get values
		if ( strlen( $color ) === 6 ) {
			$hex = array( $color[0] . $color[1], $color[2] . $color[3], $color[4] . $color[5] );
		} elseif ( strlen( $color ) === 3 ) {
			$hex = array( $color[0] . $color[0], $color[1] . $color[1], $color[2] . $color[2] );
		} else {
			return $default;
		}

		// Convert hexadec to rgb
		$rgb = array_map( 'hexdec', $hex );

		// Check if opacity is set(rgba or rgb)
		if ( $opacity ) {
			if ( abs( $opacity ) > 1 ) {
				$opacity = 1.0;
			}
			$output = 'rgba(' . implode( ",", $rgb ) . ',' . $opacity . ')';
		} else {
			$output = 'rgb(' . implode( ",", $rgb ) . ')';
		}

		// Return rgb(a) color string
		return $output;
	}
}


if ( ! function_exists( 'crane_set_default_options' ) ) {
	function crane_set_default_options() {
		global $crane_options;

		$crane_options = json_decode( '{"last_tab":"","favicon":{"url":"","id":"0","height":"","width":"","thumbnail":""},"wide-layout":"","wide-layout-padding":{"padding-right":"15px","padding-left":"15px"},"main-grid-width":"1200","show-back-to-top":"1","show_featured_placeholders":"","preloader":"1","preloader-type":"ball-pulse","preloader-color":"#93cb52","preloader-bg-color":"#ffffff","lazyload":"","page-title-dimensions":{"height":"200px","units":"px"},"page-title-typography":{"font-family":"Open Sans","font-options":"","google":"1","font-weight":"400","font-style":"","subsets":"","text-transform":"none","font-size":"37px","color":"#000000"},"page-title-background":{"background-color":"#f9f9f9","background-repeat":"","background-size":"","background-attachment":"","background-position":"","background-image":"","media":{"id":"","height":"","width":"","thumbnail":""}},"page-title-line-decorators-switch":"1","page-title-border":{"border-top":"","border-right":"","border-bottom":"1px","border-left":"","border-style":"solid","border-color":"#eaeaea"},"breadcrumbs-text":"","page-breadcrumbs-typography":{"font-family":"Open Sans","font-options":"","google":"1","font-weight":"600","font-style":"","subsets":"","text-transform":"uppercase","font-size":"12px","color":"#4d4d4d"},"page-breadcrumbs-delimiter-color":"#b9b9b9","breadcrumbs-regular":"","breadcrumbs-portfolio":"breadcrumbs","breadcrumbs-portfolio-single":"inherit","breadcrumbs-blog":"both_within","breadcrumbs-blog-single":"inherit","breadcrumbs-shop":"breadcrumbs","breadcrumbs-shop-single":"inherit","regular-txt-typography":{"font-family":"Open Sans","font-options":"","google":"1","font-weight":"400","font-style":"","subsets":"","text-transform":"initial","font-size":"14px"},"h1-typography":{"font-family":"Open Sans","font-options":"","google":"1","font-weight":"700","font-style":"","subsets":"","text-transform":"initial","font-size":"34px"},"h2-typography":{"font-family":"Open Sans","font-options":"","google":"1","font-weight":"700","font-style":"","subsets":"","text-transform":"initial","font-size":"31px"},"h3-typography":{"font-family":"Open Sans","font-options":"","google":"1","font-weight":"700","font-style":"","subsets":"","text-transform":"initial","font-size":"23px"},"h4-typography":{"font-family":"Open Sans","font-options":"","google":"1","font-weight":"700","font-style":"","subsets":"","text-transform":"initial","font-size":"20px"},"h5-typography":{"font-family":"Open Sans","font-options":"","google":"1","font-weight":"700","font-style":"","subsets":"","text-transform":"initial","font-size":"17px"},"h6-typography":{"font-family":"Open Sans","font-options":"","google":"1","font-weight":"700","font-style":"","subsets":"","text-transform":"initial","font-size":"15px"},"primary-color":"#93cb52","secondary-color":"#fab710","background-color":{"background-color":"#fff"},"alt-background-color":{"background-color":"#fbfbfb"},"heading-color":"#686868","regular-txt-color":"#686868","opt-link-color":{"regular":"#85bf43","hover":"#6eb238","active":"#85bf43"},"border-color":"#dbdbdb","border-color-focus":"#c5c5c5","selection-color":"#cccccc","regular-page-menu":"","regular-page-nav_menu":"0","regular-page-has-sidebar":"none","regular-page-sidebar":"crane_basic_sidebar","regular-page-sidebar-width":"25","regular-page-content-width":"75","regular-page-sticky":"0","regular-page-sticky-offset":{"padding-top":"15"},"regular-page-padding":{"padding-top":"80px","padding-bottom":"80px"},"regular-page-padding-mobile":{"padding-top":"40px","padding-bottom":"40px"},"portfolio-name":"Portfolio","portfolio-slug":"portfolio","portfolio_cats-slug":"portfolio-category","portfolio_tags-slug":"portfolio-tag","portfolio-menu":"","portfolio-archive-nav_menu":"0","portfolio-archive-has-sidebar":"at-right","portfolio-archive-sidebar":"crane_basic_sidebar","portfolio-archive-sidebar-width":"25","portfolio-archive-content-width":"75","portfolio-archive-sticky":"0","portfolio-archive-sticky-offset":{"padding-top":"15"},"portfolio-archive-padding":{"padding-top":"80px","padding-bottom":"80px"},"portfolio-archive-padding-mobile":{"padding-top":"40px","padding-bottom":"40px"},"portfolio-archive-style":"flat","portfolio-archive-layout":"grid","portfolio-archive-layout_mode":"masonry","portfolio-archive-img_proportion":"1x1","portfolio-archive-image_resolution":"crane-portfolio-300","portfolio-archive-hover_style":"4","portfolio-archive-direction_aware_color":{"color":"#000","alpha":"0.5","rgba":"rgba(0,0,0,0.5)"},"portfolio-archive-shuffle_text":"View project","portfolio-archive-grid_spacing":"30","portfolio-archive-columns":"4","portfolio-archive-max_width":"769","portfolio-archive-posts_limit":"0","portfolio-archive-show_title_description":"1","portfolio-archive-show_categories":"1","portfolio-archive-show_custom_text":"","portfolio-archive-show_excerpt":"1","portfolio-archive-excerpt_strip_html":"1","portfolio-archive-excerpt_height":"170","portfolio-archive-show_read_more":"","portfolio-archive-show_imgtags":"0","portfolio-archive-sortable":"","portfolio-archive-sortable_align":"center","portfolio-archive-sortable_style":"in_grid","portfolio-archive-sortable_background_color":"","portfolio-archive-sortable_text_color":"","portfolio-archive-pagination_type":"show_more","portfolio-archive-pagination_typography":{"font-family":"Open Sans","font-options":"","google":"1","font-weight":"600","font-style":"","subsets":"","text-transform":"","font-size":"18px"},"portfolio-archive-pagination_color":"#ffffff","portfolio-archive-pagination_background":"#393b3f","portfolio-archive-pagination_color_hover":"#ffffff","portfolio-archive-pagination_background_hover":"#93cb52","portfolio-archive-show_more_text":"Show more","portfolio-archive-orderby":"post_date","portfolio-archive-order":"ASC","portfolio-archive-custom_order":"","portfolio-archive-target":"same","portfolio-single-menu":"","portfolio-single-nav_menu":"0","portfolio-single-breadcrumbs_view":"only_name","portfolio-single-has-sidebar":"none","portfolio-single-sticky":"0","portfolio-single-sticky-offset":{"padding-top":"15"},"portfolio-single-padding":{"padding-top":"80px","padding-bottom":"80px"},"portfolio-single-padding-mobile":{"padding-top":"40px","padding-bottom":"40px"},"portfolio-single-show-prev-next-post":"1","portfolio-single-sidebar":"crane_basic_sidebar","portfolio-single-sidebar-width":"25","portfolio-single-content-width":"75","portfolio-single-show-title":"1","portfolio-single-show-border":"1","portfolio-single-show-tags":"1","portfolio-single-show-date":"1","portfolio-single-show-cats":"1","portfolio-single-show-share":"1","blog_cats-slug":"category","blog_tags-slug":"tag","blog-menu":"","blog-nav_menu":"0","blog-archive-title":"Archive","blog-has-sidebar":"at-right","blog-sidebar":"crane_basic_sidebar","blog-sidebar-width":"25","blog-content-width":"75","blog-archive-sticky":"0","blog-archive-sticky-offset":{"padding-top":"15"},"blog-archive-padding":{"padding-top":"80px","padding-bottom":"80px"},"blog-archive-padding-mobile":{"padding-top":"40px","padding-bottom":"40px"},"blog-template":"standard","blog-image_resolution":"crane-featured","blog-show_pubdate":"1","blog-show_author":"1","blog-show_cats":"1","blog-style":"corporate","blog-img_proportion":"1x1","blog-masonry-columns":"4","blog-cell-columns":"2","blog-max_width":"768","blog-grid_spacing":"30","blog-post_height_desktop":"350","blog-post_height_mobile":"350","blog-show_title_description":"","blog-show_tags":"1","blog-show_excerpt":"","blog-excerpt_strip_html":"1","blog-excerpt_height":"170","blog-cell-item-bg-color":"#f8f7f5","blog-show_read_more":"","blog-show-comment-counter":"1","blog-show_share_button":"1","blog-show_post_meta":"author-and-date","blog-target":"same","blog-single-menu":"","blog-single-nav_menu":"0","blog-single-breadcrumbs_view":"with_category","blog-single-has-sidebar":"at-right","blog-single-sidebar":"crane_basic_sidebar","blog-single-sidebar-width":"25","blog-single-content-width":"75","blog-single-sticky":"0","blog-single-sticky-offset":{"padding-top":"15"},"blog-single-padding":{"padding-top":"80px","padding-bottom":"80px"},"blog-single-padding-mobile":{"padding-top":"40px","padding-bottom":"40px"},"blog-single-show-content-title":"1","blog-single-show-meta-in-featured":"","blog-single-show-featured":"1","blog-fib-title-typography":{"font-family":"Open Sans","font-options":"","google":"1","font-weight":"600","font-style":"","subsets":"","text-align":"","font-size":"46px","line-height":"60px","color":"#fff"},"blog-fib-category-typography":{"font-family":"Open Sans","font-options":"","google":"1","font-weight":"600","font-style":"","subsets":"","text-align":"","font-size":"16px","line-height":"25px","color":"#fff"},"blog-fib-divider-color":{"color":"rgba(211,211,211,0.65)","alpha":"1","rgba":"rgba(0,186,2,1)"},"blog-single-show-comment-counter":"1","blog-single-show_share_button":"1","blog-single-show-author-info":"1","blog-single-show-prev-next-post":"1","blog-single-show-related-posts":"1","blog-single-related-posts-hover-type":"hover-gradient","blog-single-related-posts-gradient":{"from":"#7ad4f1","to":"#cef17a"},"blog-single-show-tags":"1","shop-is-catalog":"","ajax-add-to-cart":"1","shop-menu":"","shop-nav_menu":"0","shop-has-sidebar":"at-right","shop-sidebar":"crane_basic_sidebar","shop-sidebar-width":"25","shop-content-width":"75","shop-archive-sticky":"0","shop-archive-sticky-offset":{"padding-top":"15"},"shop-archive-padding":{"padding-top":"80px","padding-bottom":"80px"},"shop-archive-padding-mobile":{"padding-top":"40px","padding-bottom":"40px"},"shop-columns":"3","shop-per-page":"12","crane-shop-paginator":"numbers","shop-pagination-prev_next-type":"arrows","shop-design":"simple","shop-show-image-type":"single","shop-show-star-rating":"1","shop-show-description-excerpt":"","shop-show-product-categories":"","shop-show-product-tags":"","shop-show-product-filter":"1","shop-single-menu":"","shop-single-nav_menu":"0","shop-single-breadcrumbs_view":"with_category","shop-single-has-sidebar":"none","shop-single-sidebar":"crane_basic_sidebar","shop-single-sidebar-width":"25","shop-single-content-width":"75","shop-single-sticky":"0","shop-single-sticky-offset":{"padding-top":"15"},"shop-single-padding":{"padding-top":"80px","padding-bottom":"80px"},"shop-single-padding-mobile":{"padding-top":"40px","padding-bottom":"40px"},"shop-show-related":"","shop-rows-related":"2","shop-columns-related":"4","shop-single-show-prev-next-post":"0","search-menu":"","search-nav_menu":"0","search-has-sidebar":"at-right","search-sidebar":"crane_basic_sidebar","search-sidebar-width":"25","search-content-width":"75","search-sticky":"0","search-sticky-offset":{"padding-top":"15"},"search-padding":{"padding-top":"80px","padding-bottom":"80px"},"search-padding-mobile":{"padding-top":"40px","padding-bottom":"40px"},"crane-search-paginator":"numbers","search-pagination-prev_next-type":"text","footer_preset_global":"basic-footer","footer_appearance":"appearance-regular","share-social-facebook":"1","share-social-twitter":"1","share-social-googleplus":"1","share-social-pinterest":"1","share-social-linkedin":"1","404-type":"default","404-page":"","404-menu":"","404-nav_menu":"0","404-title":"Oops, This Page Could Not Be Found!","404-text":"Unfortunately, the page was not found. It was deleted or moved and is now at another address.","404-footer_preset":"basic-footer","404-footer_appearance":"appearance-regular","custom-css":"","custom-html_head":"","custom-html":"","maintenance-mode":"0","maintenance-page":"","maintenance-503":"0","minify-js":"0","minify-css":"0","custom-image-sizes":"","privacy-google_fonts":"1","privacy-preferences":"","privacy-toolbar-bg-color":{"background-color":"#1b1f26"},"privacy-toolbar-typography":{"font-family":"Open Sans","font-options":"","google":"1","font-weight":"400","font-style":"","subsets":"","text-transform":"none","font-size":"14px","color":"#fff"},"privacy-placeholder-bg-color":{"background-color":"#424242"},"privacy-placeholder-typography":{"font-family":"Open Sans","font-options":"","google":"1","font-weight":"400","font-style":"","subsets":"","text-transform":"none","font-size":"14px","color":"#fff"},"privacy-embeds":"","privacy-expiration":"30"}', true );

	}
}


if ( ! function_exists( 'crane_get_fonts_url' ) ) {
	/**
	 * Register Google fonts
	 */
	function crane_get_fonts_url() {
		$font_url = add_query_arg( 'family', urlencode( 'Open Sans:400,400i,600,700' ), "https://fonts.googleapis.com/css" );

		return $font_url;
	}
}


if ( ! function_exists( 'crane_add_default_fonts' ) ) {
	/**
	 *  Enqueue scripts and styles.
	 */
	function crane_add_default_fonts() {
		wp_enqueue_style( 'crane-default-fonts', crane_get_fonts_url(), array(), CRANE_THEME_VERSION );
	}
}


if ( ! function_exists( 'crane_get_footer_by_option' ) ) {
	function crane_get_footer_by_option( $option_name = 'footer_preset_global', $type = 'theme-options' ) {

		static $data = array(
			'meta'          => array(),
			'theme-options' => array(),
		);

		if ( ! empty( $data[ $type ][ $option_name ] ) ) {
			return $data[ $type ][ $option_name ];
		}

		global $crane_options;

		$current_options = $crane_options;

		if ( empty( $current_options ) ) {
			$current_options = maybe_unserialize( get_option( 'crane_options' ) );
		}

		if ( 'meta' === $type && ! empty( $_GET['post'] ) ) {

			$edited_post_id = intval( $_GET['post'] );
			$grooni_meta    = json_decode( maybe_unserialize( get_post_meta( $edited_post_id, 'grooni_meta', true ) ), true );
			$_footer        = isset( $grooni_meta[ $option_name ] ) ? $grooni_meta[ $option_name ] : null;
			if ( 'default' === $_footer ) {
				$_footer = isset( $current_options['footer_preset_global'] ) ? $current_options['footer_preset_global'] : null;
			}

		} else {
			$_footer = isset( $current_options[ $option_name ] ) ? $current_options[ $option_name ] : null;
		}

		$_footer = get_page_by_path( $_footer, OBJECT, 'crane_footer' );
		$_footer = $_footer ? : get_page_by_path( 'basic-footer', OBJECT, 'crane_footer' );

		$footer_id = ( isset( $_footer->ID ) ) ? $_footer->ID : null;

		$data[ $type ][ $option_name ] = '<a target="_blank" href="' . admin_url() . 'post.php?post=' . $footer_id . '&action=edit">';
		$data[ $type ][ $option_name ] .= esc_html__( 'edit', 'crane' );
		$data[ $type ][ $option_name ] .= '</a>';


		return $data[ $type ][ $option_name ];
	}
}


if ( class_exists( 'Ultimate_VC_Addons' ) ) {

	class Crane_Ultimate_VC_Addons extends Ultimate_VC_Addons {
		public function __construct() {
			if ( ! defined( 'UAVC_DIR' ) ) {
				define( 'UAVC_DIR', plugin_dir_path( trailingslashit( WP_PLUGIN_DIR ) . 'Ultimate_VC_Addons/Ultimate_VC_Addons.php' ) );
			}
			if ( ! defined( 'UAVC_URL' ) ) {
				define( 'UAVC_URL', plugins_url( '/', trailingslashit( WP_PLUGIN_DIR ) . 'Ultimate_VC_Addons/Ultimate_VC_Addons.php' ) );
			}
			$this->vc_template_dir = UAVC_DIR . 'vc_templates/';
			$this->vc_dest_dir     = get_template_directory() . '/vc_templates/';
			$this->module_dir      = UAVC_DIR . 'modules/';
			$this->params_dir      = UAVC_DIR . 'params/';
			$this->assets_js       = UAVC_URL . 'assets/js/';
			$this->assets_css      = UAVC_URL . 'assets/css/';
			$this->admin_js        = UAVC_URL . 'admin/js/';
			$this->admin_css       = UAVC_URL . 'admin/css/';

			$this->paths          = wp_upload_dir();
			$this->paths['fonts'] = 'smile_fonts';

			$scheme = is_ssl() ? 'https' : 'http';

			$this->paths['fonturl'] = set_url_scheme( $this->paths['baseurl'] . '/' . $this->paths['fonts'], $scheme );
		}
	}

}

if ( ! function_exists( 'crane_show_maintenance_page' ) ) {

	function crane_show_maintenance_page() {

		global $crane_options;

		$_rewrite_page_id = intval( $crane_options['maintenance-page'] );

		if ( ! empty( $_rewrite_page_id ) ) {

			if ( ! empty( $crane_options['maintenance-503'] ) && $crane_options['maintenance-503'] ) {
				header( 'HTTP/1.1 503 Service Temporarily Unavailable' );
				header( 'Status: 503 Service Temporarily Unavailable' );
				header( 'Retry-After: ' . DAY_IN_SECONDS ); // retry in a day
			}

			query_posts( 'page_id=' . $_rewrite_page_id );
			if ( have_posts() ) : while ( have_posts() ) : the_post();
				// ... empty
			endwhile; endif; // End of the loop.

		}
	}

}

if ( ! function_exists( 'crane_maintenance_admin_bar_notice' ) ) {

	function crane_maintenance_admin_bar_notice() {

		global $crane_options, $wp_admin_bar;

		if ( isset( $crane_options['maintenance-mode'] ) && $crane_options['maintenance-mode'] ) {

			$maintenance_page = get_post( $crane_options['maintenance-page'] );

			if ( $maintenance_page ) {

				$wp_admin_bar->add_node(
					array(
						'id'    => 'crane-maintenance-notice',
						'title' => __( 'Maintenance Mode', 'crane' ),
						'href'  => admin_url() . 'admin.php?page=crane-theme-options',
						'meta'  => array(
							'class' => 'crane-maintenance',
							'html'  => '<style>.crane-maintenance a{background-color:rgba(245,0,0,0.3)!important;color:#f74200!important;font-weight:900!important;}</style>',
						),
					)
				);
			}
		}

	}

}


if ( ! function_exists( 'crane_is_lazyload_enabled' ) ) {
	/**
	 * Get a value of the Lazy Load setting
	 *
	 * @return bool
	 */
	function crane_is_lazyload_enabled() {

		if ( class_exists( 'Crane_Options_Helper' ) ) {
			return Crane_Options_Helper::is_lazyload_enabled();
		}

		return false;

	}

}


if ( ! function_exists( 'crane_clear_echo' ) ) {
	function crane_clear_echo( $text ) {

		if ( function_exists( 'grooni_esc_text' ) ) {
			$text = grooni_esc_text( $text );
		}

		return $text;

	}
}


if ( ! function_exists( 'crane_show_back_to_top' ) ) {
	/**
	 * Show "back-to-top" button based on the theme options
	 */
	function crane_show_back_to_top() {
		global $crane_options;
		if ( isset( $crane_options['show-back-to-top'] ) && $crane_options['show-back-to-top'] ) {
			?>
			<a href="#0" class="crane-top">
				<i class="fa fa-angle-up"></i>
			</a>
			<?php
		}
	}
}
add_action( 'crane_after_footer', 'crane_show_back_to_top', 20 );


/**
 * Return array of allowed privacy elements
 *
 * @param bool $return_keys_only
 *
 * @return array
 */
function crane_get_privacy_elements( $return_keys_only = false ) {

	$return_array = array();

	$all_elements = array(
		'youtube.com'      => array(
			'name'  => 'YouTube.com',
			'domen' => 'youtube.com',
			'desc'  => '',
			'type'  => 'video',
		),
		'vimeo.com'        => array(
			'name'  => 'Vimeo.com',
			'domen' => 'vimeo.com',
			'desc'  => '',
			'type'  => 'video',
		),
		'facebook.com'     => array(
			'name'  => 'Facebook.com',
			'domen' => 'facebook.com',
			'desc'  => '',
			'type'  => 'site',
		),
		'twitter.com'      => array(
			'name'  => 'Twitter.com',
			'domen' => 'twitter.com',
			'desc'  => '',
			'type'  => 'site',
		),
		'soundcloud.com'   => array(
			'name'  => 'SoundCloud.com',
			'domen' => 'soundcloud.com',
			'desc'  => '',
			'type'  => 'audio',
		),
		'spotify.com'      => array(
			'name'  => 'Spotify.com',
			'domen' => 'spotify.com',
			'desc'  => '',
			'type'  => 'audio',
		),
		'instagram.com'    => array(
			'name'  => 'Instagram.com',
			'domen' => 'instagram.com',
			'desc'  => '',
			'type'  => 'images',
		),
		'tumblr.com'       => array(
			'name'  => 'Tumblr.com',
			'domen' => 'tumblr.com',
			'desc'  => '',
			'type'  => 'images',
		),
		'imgur.com'        => array(
			'name'  => 'Imgur.com',
			'domen' => 'imgur.com',
			'desc'  => '',
			'type'  => 'images',
		),
		'dailymotion.com'  => array(
			'name'  => 'Dailymotion.com',
			'domen' => 'dailymotion.com',
			'desc'  => '',
			'type'  => 'site',
		),
		'flickr.com'       => array(
			'name'  => 'Flickr.com',
			'domen' => 'flickr.com',
			'desc'  => '',
			'type'  => 'images',
		),
		'smugmug.com'      => array(
			'name'  => 'Smugmug.com',
			'domen' => 'smugmug.com',
			'desc'  => '',
			'type'  => 'images',
		),
		'hulu.com'         => array(
			'name'  => 'Hulu.com',
			'domen' => 'hulu.com',
			'desc'  => '',
			'type'  => 'video',
		),
		'photobucket.com'  => array(
			'name'  => 'PhotoBucket.com',
			'domen' => 'photobucket.com',
			'desc'  => '',
			'type'  => 'images',
		),
		'slideshare.net'   => array(
			'name'  => 'SlideShare.net',
			'domen' => 'slideshare.net',
			'desc'  => '',
			'type'  => 'site',
		),
		'amazon'           => array(
			'name'  => 'Amazon',
			'domen' => 'amazon',
			'desc'  => '',
			'type'  => 'site',
		),
		'mixcloud.com'     => array(
			'name'  => 'Mixcloud.com',
			'domen' => 'mixcloud.com',
			'desc'  => '',
			'type'  => 'site',
		),
		'wordpress'        => array(
			'name'  => 'WordPress',
			'domen' => 'wordpress',
			'desc'  => '',
			'type'  => 'video',
		),
		'kickstarter.com'  => array(
			'name'  => 'KickStarter.com',
			'domen' => 'kickstarter.com',
			'desc'  => '',
			'type'  => 'site',
		),
		'reddit.com'       => array(
			'name'  => 'Reddit.com',
			'domen' => 'reddit.com',
			'desc'  => '',
			'type'  => 'site',
		),
		'screencast.com'   => array(
			'name'  => 'Screencast.com',
			'domen' => 'screencast.com',
			'desc'  => '',
			'type'  => 'video',
		),
		'someecards.com'   => array(
			'name'  => 'Someecards.com',
			'domen' => 'someecards.com',
			'desc'  => '',
			'type'  => 'site',
		),
		'speakerdeck.com'  => array(
			'name'  => 'SpeakerDeck.com',
			'domen' => 'speakerdeck.com',
			'desc'  => '',
			'type'  => 'site',
		),
		'animoto.com'      => array(
			'name'  => 'Animoto.com',
			'domen' => 'animoto.com',
			'desc'  => '',
			'type'  => 'video',
		),
		'meetup.com'       => array(
			'name'  => 'Meetup.com',
			'domen' => 'meetup.com',
			'desc'  => '',
			'type'  => 'site',
		),
		'collegehumor.com' => array(
			'name'  => 'Collegehumor.com',
			'domen' => 'collegehumor.com',
			'desc'  => '',
			'type'  => 'site',
		),
		'ted.com'          => array(
			'name'  => 'ted.com',
			'domen' => 'ted.com',
			'desc'  => '',
			'type'  => 'site',
		),
		'issuu.com'        => array(
			'name'  => 'issuu.com',
			'domen' => 'issuu.com',
			'desc'  => '',
			'type'  => 'site',
		),
		'polldaddy.com'    => array(
			'name'  => 'Polldaddy.com',
			'domen' => 'polldaddy.com',
			'desc'  => '',
			'type'  => 'site',
		),
		'scribd.com'       => array(
			'name'  => 'Scribd.com',
			'domen' => 'scribd.com',
			'desc'  => '',
			'type'  => 'site',
		),
		'funnyordie.com'   => array(
			'name'  => 'FunnyOrDie.com',
			'domen' => 'funnyordie.com',
			'desc'  => '',
			'type'  => 'site',
		),
		'cloudup.com'      => array(
			'name'  => 'Cloudup.com',
			'domen' => 'cloudup.com',
			'desc'  => '',
			'type'  => 'site',
		),
		'reverbnation.com' => array(
			'name'  => 'Reverbnation.com',
			'domen' => 'reverbnation.com',
			'desc'  => '',
			'type'  => 'site',
		),
	);

	if ( $return_keys_only ) {
		foreach ( $all_elements as $element ) {
			$return_array[ $element['domen'] ] = $element['name'];
		}
		asort( $return_array, SORT_REGULAR );
	} else {
		$return_array = $all_elements;
	}


	return $return_array;
}


/**
 * Get cookie for privacy (gdpr)
 *
 * @param string $type Postfix for cookie
 * @param bool $return_name
 * @param bool $output_array
 *
 * @return array|mixed
 */
function crane_get_privacy_cookie( $type = '', $return_name = false, $output_array = true ) {

	$type                = $type ? '-' . $type : '';
	$privacy_cookie_name = 'crane--privacy-options' . $type;

	if ( $return_name ) {
		return $privacy_cookie_name;
	}

	if ( empty( $_COOKIE[ $privacy_cookie_name ] ) ) {
		$privacy_cookie_data = $output_array ? array() : null;
	} else {
		if ( $output_array ) {
			$privacy_cookie_data = json_decode( str_replace( '\"', '"', $_COOKIE[ $privacy_cookie_name ] ), true );
			if ( ! is_array( $privacy_cookie_data ) ) {
				$privacy_cookie_data = array();
			}
		} else {
			$privacy_cookie_data = esc_attr( $_COOKIE[ $privacy_cookie_name ] );
		}
	}

	return $privacy_cookie_data;
}

function crane_get_privacy_toolbar( $without_embeds = true ) {

	global $crane_options;

	if ( crane_get_privacy_cookie( 'force-agree', false, false ) ) {
		return '';
	}

	if ( ! empty( $without_embeds ) && $without_embeds ) {
		$privacy_policy_page_id = get_option( 'wp_page_for_privacy_policy' );
		if ( $privacy_policy_page_id ) {
			$privacy_policy_page = get_permalink( $privacy_policy_page_id );
			if ( $privacy_policy_page ) {
				$privacy_link_escaped = sprintf(
					'href="%s" class="button crane-privacy__btn crane-privacy__learnmore" target="_blank">%s',
					$privacy_policy_page,
					esc_html__( 'Learn More', 'crane' )
				);
			}
			$privacy_text_link_escaped = sprintf(
				'href="%s" class="crane-privacy__learnmore" target="_blank">%s',
				$privacy_policy_page,
				esc_html__( 'Learn More', 'crane' )
			);
		}
	} else {
		$privacy_link_escaped = sprintf(
			'href="%s" class="button crane-privacy__btn crane-privacy__preferences">%s',
			'#crane-privacy-block',
			esc_html__( 'Privacy Preferences', 'crane' )
		);
		$privacy_text_link_escaped = sprintf(
			'href="%s" class="crane-privacy__preferences">%s',
			'#crane-privacy-block',
			esc_html__( 'Privacy Preferences', 'crane' )
		);
	}

	$privacy_link_escaped      = empty( $privacy_link_escaped ) ? '' : sprintf( '<a %s</a>', $privacy_link_escaped );
	$privacy_text_link_escaped = empty( $privacy_text_link_escaped ) ? esc_html__( 'Privacy Preferences', 'crane' ) : sprintf( '<a %s</a>', $privacy_text_link_escaped );
	$privacy_expiration        = empty( $crane_options['privacy-expiration'] ) ? 30 : $crane_options['privacy-expiration'];

	?>
	<div class="crane-privacy-toolbar" id="crane-privacy-toolbar"
	     data-expiration="<?php echo esc_attr( $privacy_expiration ); ?>"
	     data-cookie="<?php echo esc_attr( crane_get_privacy_cookie( '', true ) ); ?>">
		<div class="crane-privacy-toolbar__text">
			<?php echo sprintf( esc_html__( 'We use cookies from third party services to offer you a better experience. Read about how we use cookies and how you can control them by clicking %s', 'crane' ), trim( $privacy_text_link_escaped ) ); ?></div>
		<div class="crane-privacy-toolbar__action">
			<?php echo crane_clear_echo( $privacy_link_escaped ); ?>
			<button class="crane-privacy__btn crane-privacy__btn--white crane-privacy__force-agree">
				<?php echo esc_html__( 'I Agree', 'crane' ) ?>
			</button>
		</div>
	</div>
	<?php

}


if ( ! function_exists( 'crane_show_privacy_block' ) ) {
	/**
	 * Show the Privacy block based on the theme options
	 *
	 * @param bool $only_link
	 *
	 * @return string
	 */
	function crane_show_privacy_block( $only_link = true ) {
		global $crane_options;

		static $counter = 1;

		$privacy_expiration = empty( $crane_options['privacy-expiration'] ) ? 30 : $crane_options['privacy-expiration'];
		$privacy_services   = empty( $crane_options['privacy-services'] ) ? null : $crane_options['privacy-services'];
		$privacy_link_html  = '<a href="#crane-privacy-block" class="crane-privacy__preferences" data-counter="' . esc_attr( $counter ) . '">' . esc_html__( 'Privacy Preferences', 'crane' ) . '</a>';


		if ( $privacy_services && $only_link ) {
			return $privacy_link_html;
		} elseif ( $privacy_services && crane_is_enable_privacy_embeds() ) {


			$counter ++;

			$privacy_cookie_data = crane_get_privacy_cookie( 'embeds' );
			$privacy_force_agree = crane_get_privacy_cookie( 'force-agree', false, false );

			if ( empty( $privacy_force_agree ) && empty( $privacy_cookie_data ) ) {
				$without_embeds = ( ! empty( $privacy_services ) ) ? false : true;
				crane_get_privacy_toolbar( $without_embeds );
			}
			?>

			<div id="crane-privacy-block" class="crane-privacy-dialog mfp-hide"
			     data-expiration="<?php echo esc_attr( $privacy_expiration ); ?>"
			     data-cookie="<?php echo esc_attr( crane_get_privacy_cookie( '', true ) ); ?>">
				<div class="crane-privacy-dialog__header">
					<h3 class="crane-privacy-dialog__title"><?php esc_html_e( 'Privacy Preferences', 'crane' ); ?></h3>

					<p class="crane-privacy-dialog__subtitle"><?php esc_html_e( 'When you visit any website, it may store or retrieve information through your browser, usually in the form of cookies. Since we respect your right to privacy, you can choose not to permit data collection from certain types of services. However, not allowing these services may impact your experience.', 'crane' ); ?></p>
				</div>
				<div class="crane-privacy-dialog__body">
					<div class="crane-privacy-dialog__elem">
								<span class="crane-privacy-dialog__service-name">
									<?php esc_html_e( 'Privacy Policy', 'crane' ); ?>
								</span>
								<span class="crane-privacy-dialog__service-desc">
									<?php
									$privacy_policy_page    = esc_html__( 'Privacy Policy', 'crane' );
									$privacy_policy_page_id = get_option( 'wp_page_for_privacy_policy' );
									if ( $privacy_policy_page_id ) {
										$privacy_policy_page = get_permalink( $privacy_policy_page_id );
										if ( $privacy_policy_page ) {
											$privacy_policy_page = sprintf( '<a href="%s" target="_blank">%s  <i class="fa fa-external-link" aria-hidden="true"></i></a>', $privacy_policy_page, esc_html__( 'Privacy Policy', 'crane' ) );
										}
									}
									echo sprintf( esc_html__( 'You read and agreed to our %s.', 'crane' ), $privacy_policy_page );
									?>
								</span>
								<span class="crane-privacy-dialog__required">
									<?php esc_html_e( 'REQUIRED', 'crane' ); ?>
								</span>
					</div>
					<?php
					$all_privacy_elements = crane_get_privacy_elements();

					foreach ( $privacy_services as $service ) {

						$element = array(
							'name'  => $service,
							'domen' => $service,
							'desc'  => $service,
						);

						if ( isset( $all_privacy_elements[ $service ] ) ) {
							$element = $all_privacy_elements[ $service ];
						}

						if ( empty( $element['desc'] ) ) {
							$element['desc'] = esc_html( 'Allow', 'crane' ) . ' ' . $element['name'] . ' ' . esc_html( 'embed content', 'crane' );
						}

						$service_state = '';
						if ( isset( $privacy_cookie_data[ $service ] ) && $privacy_cookie_data[ $service ] ) {
							$service_state = 'checked';
						}

						?>
						<div class="crane-privacy-dialog__elem">
									<span class="crane-privacy-dialog__service-name">
										<?php echo crane_clear_echo( $element['name'] ); ?>
									</span>
									<span class="crane-privacy-dialog__service-desc">
										<?php echo crane_clear_echo( $element['desc'] ); ?>
									</span>
							<label>
								<input
									type="checkbox"
									class="crane-privacy-dialog__input"
									data-domen="<?php echo crane_clear_echo( $element['domen'] ); ?>"
									<?php echo esc_attr( $service_state ); ?>
									/>
							</label>
						</div>
						<?php
					}
					?>
				</div>
				<div class="crane-privacy-dialog__footer">
					<div class="crane-privacy-block__action">
						<button class="crane-privacy__btn crane-privacy-block__action--agree">
							<?php echo esc_html__( 'I Agree', 'crane' ) ?>
						</button>
						<button
							class="crane-privacy__btn--flat crane-privacy__btn crane-privacy-block__action--close">
							<?php echo esc_html__( 'Cancel', 'crane' ) ?>
						</button>
					</div>
				</div>
			</div>
			<?php


		} elseif ( crane_is_enable_privacy_embeds( true ) ) {
			crane_get_privacy_toolbar( true );
		}
	}
}

add_action( 'crane_after_footer', 'crane_show_privacy_block', 50, 1 );
