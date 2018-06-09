<?php
/**
 * Loads a partial view for the Administration screen
 *
 * @since 1.3.0
 *
 * @param   string  $template   PHP file at includes/admin/partials, excluding file extension
 * @param   array   $data       Any data to pass to the view
 * @return  void
 */
function envira_album_load_admin_partial( $template, $data = array() ){
 
    $dir = trailingslashit( plugin_dir_path( ENVIRA_ALBUMS_FILE ) . 'src/Views/admin/' );
 
    if ( file_exists( $dir . $template . '.php' ) ) {
        require_once(  $dir . $template . '.php' );
        return true;
    }
 
    return false;
 
}