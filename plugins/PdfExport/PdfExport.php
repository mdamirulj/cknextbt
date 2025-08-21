<?php
class PdfExportPlugin extends MantisPlugin {
    function register() {
        $this->name        = 'PDF Export';
        $this->description = 'Export current issue filter to PDF';
        $this->version     = '1.0.0';
        $this->requires    = array('MantisCore' => '2.0.0');
        $this->author      = 'Your Team';
        $this->contact     = 'you@example.com';
        $this->url         = '';
    }
    function hooks() {
        // Add a link on the View Issues page’s menu
        // (EVENT_MENU_FILTER is the documented hook for this). 
        return array('EVENT_MENU_FILTER' => 'add_menu_item');
    }
    function add_menu_item() {
        if (auth_is_user_authenticated()) {
            return array('<a class="btn btn-sm btn-primary btn-white btn-round" <a href="' . plugin_page('export') . '">Export to PDF</a>');
        }
        return array();
    }
}
