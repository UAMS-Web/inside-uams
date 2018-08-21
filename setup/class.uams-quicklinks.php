<?php

/**
 * UAMS Institute Quicklinks
 * This will register the Institute Quicklinks navigation and provide a json feed for the current quicklinks menu
 */
class Institute_QuickLinks
{

  const NAME         = 'Quick Links';
  const LOCATION     = 'quick-links';

  function __construct()
  {

    add_action( 'after_setup_theme', array( $this, 'register_quick_links_menu') );

    add_action( 'wp_ajax_quicklinks', array( $this, 'uams_quicklinks_feed') );
    add_action( 'wp_ajax_nopriv_quicklinks', array( $this, 'uams_quicklinks_feed') );
  }

  function register_quick_links_menu()
  {
    register_nav_menu( self::LOCATION, __( self::NAME ) );
  }

  function uams_quicklinks_feed()
  {

    $locations = get_nav_menu_locations();
    if ( ( isset( $locations[ self::LOCATION ]) ) )
    {
      $this->items = wp_get_nav_menu_items( $locations[ self::LOCATION ] );
    }
      else if ( $location = wp_get_nav_menu_object( self::LOCATION ) )
    {
      $this->items = wp_get_nav_menu_items( $location->term_id );
    }

    wp_send_json( $this->parse_menu() ) ;
  }

  function parse_menu()
  {
    if ( isset($this->items) )
      foreach( $this->items as $index=>$item )
      {
        // Only keep the necessary keys of the $item
        $item = array_intersect_key( (array) $item , array_fill_keys( array('ID', 'title', 'url', 'classes', 'menu_item_parent'), null ) );

        if ( ! $item['classes'][0] ) $item['classes'] = false;

        $menu[] = $item;

      }

    return isset($menu) ? $menu : array();
  }

}
new Institute_QuickLinks();
