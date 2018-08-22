<?php
/**
* Add an automatic default custom taxonomy for calendar.
* If no event (taxonomy) is set, the event will be sorted as “draft” and won’t return an offset error.
*
*/

// Fix ACF Support
add_filter('_includes/acf-pro/settings/show_admin', '__return_true');

/* Allow custom Quicklinks */
//require( 'setup/class.uams-quicklinks.php' );

// Breadcrumbs
function get_uams_breadcrumbs()
{

global $post;
$ancestors = array_reverse( get_post_ancestors( $post->ID ) );
$html = '<li><a href="http://inside.uams.edu" title="University of Arkansas for Medical Scineces">Home</a></li>';
if ( !is_main_site() )
{
$html .= '<li' . (is_front_page() ? ' class="current"' : '') . '><a href="' . home_url('/') . '" title="' . str_replace('   ', ' ', get_bloginfo('title')) . '">' . str_replace('   ', ' ', get_bloginfo('title')) . '</a><li>';
}

if ( is_404() )
{
    $html .=  '<li class="current"><span>Ooops!</span>';
} else

if ( is_search() )
{
    $html .=  '<li class="current"><span>Search results for ' . get_search_query() . '</span>';
} else

if ( is_author() )
{
    $author = get_queried_object();
    $html .=  '<li class="current"><span> Author: '  . $author->display_name . '</span>';
} else

if ( get_queried_object_id() === (Int) get_option('page_for_posts')   ) {
    $html .=  '<li class="current"><span> '. get_the_title( get_queried_object_id() ) . ' </span>';
}

// If the current view is a post type other than page or attachment then the breadcrumbs will be taxonomies.
if( is_category() || is_tax() || is_single() || is_post_type_archive() )
{

    if ( is_post_type_archive() && !is_tax() )
    {
    $posttype = get_post_type_object( get_post_type() );
    //$html .=  '<li class="current"><a href="'  . get_post_type_archive_link( $posttype->query_var ) .'" title="'. $posttype->labels->menu_name .'">'. $posttype->labels->menu_name  . '</a>';
    $html .=  '<li class="current"><span>'. $posttype->labels->menu_name  . '</span>';
    }

    if ( is_category() )
    {
    $category = get_category( get_query_var( 'cat' ) );
    //$html .=  '<li class="current"><a href="'  . get_category_link( $category->term_id ) .'" title="'. get_cat_name( $category->term_id ).'">'. get_cat_name($category->term_id ) . '</a>';
    $html .=  '<li class="current"><span>'. get_cat_name($category->term_id ) . '</span>';
    }

    if ( is_tax() && !is_post_type_archive() )
    {
    $term = get_term_by("slug", get_query_var("term"), get_query_var("taxonomy") );
    $tax = get_taxonomy( get_query_var("taxonomy") );
    //$html .=  '<li class="current"><a href="'  . get_category_link( $category->term_id ) .'" title="'. get_cat_name( $category->term_id ).'">'. get_cat_name($category->term_id ) . '</a>';
    $html .=  '<li class="current"><span>'. $tax->labels->name .': '. $term->name .'</span>';
    }

    if ( is_tax() && is_post_type_archive() )
    {
    $tax = get_term_by("slug", get_query_var("term"), get_query_var("taxonomy") );
    $posttype = get_post_type_object( get_post_type() );
    //$html .=  '<li class="current"><a href="'  . get_category_link( $category->term_id ) .'" title="'. get_cat_name( $category->term_id ).'">'. get_cat_name($category->term_id ) . '</a>';
    $html .=  '<li class="current"><span>'. $tax->name . '</span>';
    }

    if ( is_single() )
    {
    if ( has_category() )
    {
        $thecat = get_the_category( $post->ID  );
        $category = array_shift( $thecat ) ;
        $html .=  '<li><a href="'  . get_category_link( $category->term_id ) .'" title="'. get_cat_name( $category->term_id ).'">'. get_cat_name($category->term_id ) . '</a>';
    }
    if ( uams_is_custom_post_type() )
    {
        $posttype = get_post_type_object( get_post_type() );
        $archive_link = get_post_type_archive_link( $posttype->query_var );
        if (!empty($archive_link)) {
        $html .=  '<li><a href="'  . $archive_link .'" title="'. $posttype->labels->menu_name .'">'. $posttype->labels->menu_name  . '</a>';
        }
        else if (!empty($posttype->rewrite['slug'])){
        $html .=  '<li><a href="'  . site_url('/' . $posttype->rewrite['slug'] . '/') .'" title="'. $posttype->labels->menu_name .'">'. $posttype->labels->menu_name  . '</a>';
        }
    }
    $html .=  '<li class="current"><span>'. get_the_title( $post->ID ) . '</span>';
    }
}

// If the current view is a page then the breadcrumbs will be parent pages.
else if ( is_page() )
{

    if ( ! is_home() || ! is_front_page() )
    $ancestors[] = $post->ID;

    if ( ! is_front_page() )
    {
    foreach ( array_filter( $ancestors ) as $index=>$ancestor )
    {
        $class      = $index+1 == count($ancestors) ? ' class="current" ' : '';
        $page       = get_post( $ancestor );
        $url        = get_permalink( $page->ID );
        $title_attr = esc_attr( $page->post_title );
        if (!empty($class)){
        $html .= "<li $class><span>{$page->post_title}</span></li>";
        }
        else {
        $html .= "<li><a href=\"$url\" title=\"{$title_attr}\">{$page->post_title}</a></li>";
        }
    }
    }

}

return "<nav class='uams-breadcrumbs' aria-label='breadcrumbs'><ul>$html</ul></nav>";
}