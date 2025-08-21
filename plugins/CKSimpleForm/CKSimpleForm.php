<?php
class CKSimpleFormPlugin extends MantisPlugin {
    function register() {
        $this->name        = 'CK Simple Form';
        $this->description = 'Adds a sidebar link to a simple form that saves into a table.';
        $this->version     = '1.0.0';
        $this->requires    = array( 'MantisCore' => '2.25.0' );
        $this->author      = 'CKNext';
        // $this->page        = 'form'; // default page if someone clicks plugin
        $this->page = 'list'; // ⬅ default page = list

    }
    function config() {
        return array('access_threshold' => MANAGER); // who can see/use
    }
    function hooks() {
        return array('EVENT_MENU_MAIN' => 'add_menu'); // put link in left sidebar
    }
    function add_menu() {
        if( access_has_global_level( plugin_config_get('access_threshold') ) ) {
            return array( array(
                'title' => 'Simple Form',
                // 'url'   => plugin_page('form'),
                'url'   => plugin_page('list'),
                'icon'  => 'fa-clipboard'
            ) );
        }
        return array();
    }
}
