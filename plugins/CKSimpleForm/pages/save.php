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
$f_description = gpc_get_string('description', '');

$t_table  = 'mantis_cksimpleform_table'; // same as the SQL you created
$t_query  = "INSERT INTO $t_table (user_id, project_id, title, description, date_submitted)
             VALUES (" . db_param() . "," . db_param() . "," . db_param() . "," . db_param() . "," . db_param() . ")";
$t_params = array(
    auth_get_current_user_id(),
    helper_get_current_project(),
    $f_title,
    $f_description,
    db_now()
);

db_query($t_query, $t_params);
form_security_purge('cksimpleform_save');

// Back to form (or redirect elsewhere)
print_successful_redirect( plugin_page('form', /* absolute */ true) );
