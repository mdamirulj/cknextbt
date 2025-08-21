<?php
require_api('authentication_api.php');
require_api('access_api.php');
require_api('layout_api.php');
require_api('form_api.php');

auth_ensure_user_authenticated();
access_ensure_global_level( plugin_config_get('access_threshold') );

layout_page_header('Simple Form');
layout_page_begin();
?>
<div class="page-content">
  <div class="col-md-8 col-xs-12">
    <div class="widget-box widget-color-green">
      <div class="widget-header widget-header-small">
        <h4 class="widget-title lighter">Create New Entry</h4>
      </div>
      <div class="widget-body">
        <div class="widget-main">
          <form action="<?php echo plugin_page('save'); ?>" method="post" class="form-horizontal">
            <?php echo form_security_field('cksimpleform_save'); ?>

            <!-- Title -->
            <div class="form-group">
              <label class="col-sm-3 control-label" for="title">Title</label>
              <div class="col-sm-9">
                <input type="text" id="title" name="title" class="form-control" required maxlength="250">
              </div>
            </div>

            <!-- Category (dropdown) -->
            <div class="form-group">
              <label class="col-sm-3 control-label" for="category">Category</label>
              <div class="col-sm-9">
                <select id="category" name="category" class="form-control">
                  <option value="">-- Select --</option>
                  <option value="General">General</option>
                  <option value="Technical">Technical</option>
                  <option value="Finance">Finance</option>
                  <option value="Dispute">Dispute</option>
                  <option value="Other">Other</option>
                </select>
              </div>
            </div>

            <!-- Status (dropdown) -->
            <div class="form-group">
              <label class="col-sm-3 control-label" for="status">Status</label>
              <div class="col-sm-9">
                <select id="status" name="status" class="form-control">
                  <option value="">-- Select --</option>
                  <option value="Open">Open</option>
                  <option value="In Progress">In Progress</option>
                  <option value="Resolved">Resolved</option>
                  <option value="Closed">Closed</option>
                  <option value="On Hold">On Hold</option>
                </select>
              </div>
            </div>

            <!-- Due Date -->
            <div class="form-group">
              <label class="col-sm-3 control-label" for="due_date">Due Date</label>
              <div class="col-sm-9">
                <input type="date" id="due_date" name="due_date" class="form-control">
              </div>
            </div>

            <!-- Description -->
            <div class="form-group">
              <label class="col-sm-3 control-label" for="description">Description</label>
              <div class="col-sm-9">
                <textarea id="description" name="description" class="form-control" rows="5"></textarea>
              </div>
            </div>

            <div class="form-group">
              <div class="col-sm-offset-3 col-sm-9">
                <button type="submit" class="btn btn-primary">Save</button>
                <a href="<?php echo string_attribute( config_get_global( 'path' ) ); ?>" class="btn btn-default">Cancel</a>
              </div>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</div>
<?php layout_page_end();
