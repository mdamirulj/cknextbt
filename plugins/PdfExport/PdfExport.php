<?php
require_api('authentication_api.php');
require_api('string_api.php');  // keep, we use string_sanitize_url()

class PdfExportPlugin extends MantisPlugin {
    public function register() {
        $this->name        = 'PDF Export';
        $this->description = 'Export current issue filter to PDF';
        $this->version     = '1.0.0';
        $this->requires    = array('MantisCore' => '2.0.0');
        $this->author      = 'Your Team';
    }

    public function hooks() {
        return array('EVENT_MENU_FILTER' => 'add_export_buttons');
    }

    public function add_export_buttons($p_event, $p_params) {
        if (!auth_is_user_authenticated()) return array();

        return array(
            // Original export (if you have it)
            '<a class="btn btn-sm btn-primary btn-white btn-round" href="' .
            string_sanitize_url(plugin_page('export.php')) .
            '">Export to PDF</a>',

            // Custom export – no filter params needed
            '<a class="btn btn-sm btn-primary btn-white btn-round" href="' .
            string_sanitize_url(plugin_page('pdf_custom.php')) .
            '">Export to PDF (Detail)</a>',
        );
    }
}
