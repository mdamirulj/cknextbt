<?php
require_api('authentication_api.php');
require_api('access_api.php');
require_api('gpc_api.php');
require_api('form_api.php');
require_api('helper_api.php');
require_api('database_api.php');
require_api('print_api.php');

auth_ensure_user_authenticated();
access_ensure_global_level( plugin_config_get('access_threshold') );

form_security_validate('cksimpleform_save');

$f_title       = gpc_get_string('title');
$f_category    = gpc_get_string('category', '');     // optional
$f_status      = gpc_get_string('status', '');       // optional
$f_due_date    = gpc_get_string('due_date', '');     // optional: 'YYYY-MM-DD' or ''
$f_description = gpc_get_string('description', '');

// Normalize empty due_date to NULL
$t_due_date = ($f_due_date === '') ? null : $f_due_date;

$t_table  = 'mantis_cksimpleform_table';
$t_query  = "INSERT INTO $t_table
  (user_id, project_id, title, category, status, due_date, description, date_submitted)
  VALUES (" . db_param() . "," . db_param() . "," . db_param() . "," . db_param() . "," . db_param() . "," . db_param() . "," . db_param() . "," . db_param() . ")";

$t_params = array(
    auth_get_current_user_id(),
    helper_get_current_project(),
    $f_title,
    $f_category,
    $f_status,
    $t_due_date,        // DATE or NULL
    $f_description,
    db_now()            // INT timestamp
);

db_query($t_query, $t_params);
form_security_purge('cksimpleform_save');

// Redirect back to the form (or change to a list page if you add one)
// print_successful_redirect( plugin_page('form', /* absolute */ true) );
print_successful_redirect( plugin_page('list', /* absolute */ true) );
