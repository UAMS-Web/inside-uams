<?php

declare(strict_types=1);
/**
 * Add an automatic default custom taxonomy for calendar.
 * If no event (taxonomy) is set, the event will be sorted as “draft” and won’t return an offset error.
 */

// Fix ACF Support
add_filter('_includes/acf-pro/settings/show_admin', '__return_true');

/* Allow custom Quicklinks */
// require( 'setup/class.uams-quicklinks.php' );

// Breadcrumbs
function get_uams_breadcrumbs(): string
{

    // The template that calls this runs on a singular or archive view, where the global post is set.
    /** @var WP_Post $post */
    global $post;
    $ancestors = array_reverse(get_post_ancestors($post->ID));
    $html = '<li><a href="http://inside.uams.edu" title="University of Arkansas for Medical Scineces">Home</a></li>';
    if (! is_main_site()) {
        $html .= '<li'.(is_front_page() ? ' class="current"' : '').'><a href="'.home_url('/').'" title="'.str_replace('   ', ' ', get_bloginfo('title')).'">'.str_replace('   ', ' ', get_bloginfo('title')).'</a><li>';
    }

    if (is_404()) {
        $html .= '<li class="current"><span>Ooops!</span>';
    } elseif (is_search()) {
        $html .= '<li class="current"><span>Search results for '.get_search_query().'</span>';
    } elseif (is_author()) {
        // is_author() is true, so the queried object is the author's WP_User.
        /** @var WP_User $author */
        $author = get_queried_object();
        $html .= '<li class="current"><span> Author: '.$author->display_name.'</span>';
    } elseif (get_queried_object_id() === (int) get_option('page_for_posts')) { // @phpstan-ignore cast.int (the page_for_posts option is an integer string)
        $html .= '<li class="current"><span> '.get_the_title(get_queried_object_id()).' </span>';
    }

    // If the current view is a post type other than page or attachment then the breadcrumbs will be taxonomies.
    if (is_category() || is_tax() || is_single() || is_post_type_archive()) {

        if (is_post_type_archive() && ! is_tax()) {
            // On a post type archive get_post_type() is a string, not false, and its type is registered.
            /** @var WP_Post_Type $posttype */
            // @phpstan-ignore argument.type
            $posttype = get_post_type_object(get_post_type());
            // $html .=  '<li class="current"><a href="'  . get_post_type_archive_link( $posttype->query_var ) .'" title="'. $posttype->labels->menu_name .'">'. $posttype->labels->menu_name  . '</a>';
            // @phpstan-ignore binaryOp.invalid (a registered post type's menu_name label is a string)
            $html .= '<li class="current"><span>'.$posttype->labels->menu_name.'</span>';
        }

        if (is_category()) {
            // A category archive resolves its own term, so this is a WP_Term, never WP_Error or null.
            /** @var WP_Term $category */
            // @phpstan-ignore argument.type (the cat query var is an integer string)
            $category = get_category(get_query_var('cat'));
            // $html .=  '<li class="current"><a href="'  . get_category_link( $category->term_id ) .'" title="'. get_cat_name( $category->term_id ).'">'. get_cat_name($category->term_id ) . '</a>';
            $html .= '<li class="current"><span>'.get_cat_name($category->term_id).'</span>';
        }

        if (is_tax() && ! is_post_type_archive()) {
            // On a taxonomy archive the queried term and its taxonomy both exist.
            /** @var WP_Term $term */
            // @phpstan-ignore argument.type, argument.type (the term and taxonomy query vars are strings)
            $term = get_term_by('slug', get_query_var('term'), get_query_var('taxonomy'));
            /** @var WP_Taxonomy $tax */
            // @phpstan-ignore argument.type (the taxonomy query var is a string)
            $tax = get_taxonomy(get_query_var('taxonomy'));
            // $html .=  '<li class="current"><a href="'  . get_category_link( $category->term_id ) .'" title="'. get_cat_name( $category->term_id ).'">'. get_cat_name($category->term_id ) . '</a>';
            // @phpstan-ignore binaryOp.invalid (a registered taxonomy's name label is a string)
            $html .= '<li class="current"><span>'.$tax->labels->name.': '.$term->name.'</span>';
        }

        if (is_tax() && is_post_type_archive()) {
            // On a taxonomy archive the queried term exists.
            /** @var WP_Term $tax */
            // @phpstan-ignore argument.type, argument.type (the term and taxonomy query vars are strings)
            $tax = get_term_by('slug', get_query_var('term'), get_query_var('taxonomy'));
            // $html .=  '<li class="current"><a href="'  . get_category_link( $category->term_id ) .'" title="'. get_cat_name( $category->term_id ).'">'. get_cat_name($category->term_id ) . '</a>';
            $html .= '<li class="current"><span>'.$tax->name.'</span>';
        }

        if (is_single()) {
            if (has_category()) {
                $thecat = get_the_category($post->ID);
                // has_category() is true, so the post has at least one category term.
                /** @var WP_Term $category */
                $category = array_shift($thecat);
                $html .= '<li><a href="'.get_category_link($category->term_id).'" title="'.get_cat_name($category->term_id).'">'.get_cat_name($category->term_id).'</a>';
            }

            if (uams_is_custom_post_type()) {
                // A single post's type is a string, and uams_is_custom_post_type() means it is registered.
                /** @var WP_Post_Type $posttype */
                // @phpstan-ignore argument.type
                $posttype = get_post_type_object(get_post_type());
                // query_var is a string for any type with an archive; false only for types without one.
                // @phpstan-ignore argument.type
                $archive_link = get_post_type_archive_link($posttype->query_var);
                if (! empty($archive_link)) {
                    // @phpstan-ignore binaryOp.invalid, binaryOp.invalid (a registered post type's menu_name label is a string)
                    $html .= '<li><a href="'.$archive_link.'" title="'.$posttype->labels->menu_name.'">'.$posttype->labels->menu_name.'</a>';
                } elseif (! empty($posttype->rewrite['slug'])) {
                    // @phpstan-ignore binaryOp.invalid, binaryOp.invalid, binaryOp.invalid (the rewrite slug and the menu_name label are strings)
                    $html .= '<li><a href="'.site_url('/'.$posttype->rewrite['slug'].'/').'" title="'.$posttype->labels->menu_name.'">'.$posttype->labels->menu_name.'</a>';
                }
            }

            $html .= '<li class="current"><span>'.get_the_title($post->ID).'</span>';
        }
    }

    // If the current view is a page then the breadcrumbs will be parent pages.
    elseif (is_page()) {

        if (! is_front_page()) {
            $ancestors[] = $post->ID;

            foreach (array_filter($ancestors) as $index => $ancestor) {
                $class = $index + 1 === count($ancestors) ? ' class="current" ' : '';
                // Every id here is the current page or one of its ancestors, so the post exists.
                /** @var WP_Post $page */
                $page = get_post($ancestor);
                $url = get_permalink($page->ID);
                $title_attr = esc_attr($page->post_title);
                if (! empty($class)) {
                    $html .= "<li $class><span>{$page->post_title}</span></li>";
                } else {
                    $html .= "<li><a href=\"$url\" title=\"{$title_attr}\">{$page->post_title}</a></li>";
                }
            }
        }

    }

    return "<nav class='uams-breadcrumbs' aria-label='breadcrumbs'><ul>$html</ul></nav>";
}
