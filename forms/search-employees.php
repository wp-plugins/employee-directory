
<div class="form-alerts">
<?php
echo (isset($zf_error) ? $zf_error : (isset($error) ? $error : ''));
$form_list = get_option('empd_com_glob_forms_list');
$form_variables = $form_list['search_employees'];
$req_hide_vars = emd_get_form_req_hide_vars('empd_com', 'search_employees');
$glob_list = get_option('empd_com_glob_list');
?>
</div>
<fieldset>
<div class="search_employees-btn-fields">
<!-- search_employees Form Attributes -->
<div class="search_employees_attributes">
<div id="row1" class="row">
<!-- text input-->
<?php if ($form_variables['emd_employee_number']['show'] == 1) { ?>
<div class="col-md-<?php echo $form_variables['emd_employee_number']['size']; ?> woptdiv">
<div class="form-group">
<label id="label_emd_employee_number" class="control-label" for="emd_employee_number">
<?php _e('Employee No', 'empd-com'); ?>
<span style="display: inline-flex;right: 0px; position: relative; top:-6px;">
<a data-html="true" href="#" data-toggle="tooltip" title="<?php _e('The unique identifier for an employee', 'empd-com'); ?>" id="info_emd_employee_number" class="helptip"><span class="field-icons icons-help"></span></a>
<?php if (in_array('emd_employee_number', $req_hide_vars['req'])) { ?>
<a href="#" data-html="true" data-toggle="tooltip" title="<?php _e('Employee No field is required', 'empd-com'); ?>" id="info_emd_employee_number" class="helptip">
<span class="field-icons icons-required"></span>
</a>
<?php
	} ?>
</span>
</label>
<?php echo $emd_employee_number; ?>
</div>
</div>
<?php
} ?>
</div>
<div id="row2" class="row">
<!-- text input-->
<?php if ($form_variables['blt_title']['show'] == 1) { ?>
<div class="col-md-<?php echo $form_variables['blt_title']['size']; ?> woptdiv">
<div class="form-group">
<label id="label_blt_title" class="control-label" for="blt_title">
<?php _e('Full Name', 'empd-com'); ?>
<span style="display: inline-flex;right: 0px; position: relative; top:-6px;">
<?php if (in_array('blt_title', $req_hide_vars['req'])) { ?>
<a href="#" data-html="true" data-toggle="tooltip" title="<?php _e('Full Name field is required', 'empd-com'); ?>" id="info_blt_title" class="helptip">
<span class="field-icons icons-required"></span>
</a>
<?php
	} ?>
</span>
</label>
<?php echo $blt_title; ?>
</div>
</div>
<?php
} ?>
</div>
<div id="row3" class="row">
<!-- text input-->
<?php if ($form_variables['emd_employee_email']['show'] == 1) { ?>
<div class="col-md-<?php echo $form_variables['emd_employee_email']['size']; ?> woptdiv">
<div class="form-group">
<label id="label_emd_employee_email" class="control-label" for="emd_employee_email">
<?php _e('Email', 'empd-com'); ?>
<span style="display: inline-flex;right: 0px; position: relative; top:-6px;">
<?php if (in_array('emd_employee_email', $req_hide_vars['req'])) { ?>
<a href="#" data-html="true" data-toggle="tooltip" title="<?php _e('Email field is required', 'empd-com'); ?>" id="info_emd_employee_email" class="helptip">
<span class="field-icons icons-required"></span>
</a>
<?php
	} ?>
</span>
</label>
<?php echo $emd_employee_email; ?>
</div>
</div>
<?php
} ?>
</div>
<div id="row4" class="row">
<!-- Taxonomy input-->
<?php if ($form_variables['jobtitles']['show'] == 1) { ?>
<div class="col-md-<?php echo $form_variables['jobtitles']['size']; ?>">
<div class="form-group">
<label id="label_jobtitles" class="control-label" for="jobtitles">
<?php _e('Job Title', 'empd-com'); ?>
<span style="display: inline-flex;right: 0px; position: relative; top:-6px;">
<?php if (in_array('jobtitles', $req_hide_vars['req'])) { ?>
<a href="#" data-html="true" data-toggle="tooltip" title="<?php _e('Job Title field is required', 'empd-com'); ?>" id="info_jobtitles" class="helptip">
<span class="field-icons icons-required"></span>
</a>
<?php
	} ?>
</span>
</label>
<?php echo $jobtitles; ?>
</div>
</div>
<?php
} ?>
</div>
<div id="row5" class="row">
<!-- Taxonomy input-->
<?php if ($form_variables['departments']['show'] == 1) { ?>
<div class="col-md-<?php echo $form_variables['departments']['size']; ?>">
<div class="form-group">
<label id="label_departments" class="control-label" for="departments">
<?php _e('Department', 'empd-com'); ?>
<span style="display: inline-flex;right: 0px; position: relative; top:-6px;">
<?php if (in_array('departments', $req_hide_vars['req'])) { ?>
<a href="#" data-html="true" data-toggle="tooltip" title="<?php _e('Department field is required', 'empd-com'); ?>" id="info_departments" class="helptip">
<span class="field-icons icons-required"></span>
</a>
<?php
	} ?>
</span>
</label>
<?php echo $departments; ?>
</div>
</div>
<?php
} ?>
</div>
</div><!--form-attributes-->
<?php if ($show_captcha == 1) { ?>
<div class="row">
<div class="col-xs-12">
<div id="captcha-group" class="form-group">
<?php echo $captcha_image; ?>
<label style="padding:0px;" id="label_captcha_code" class="control-label" for="captcha_code">
<a id="info_captcha_code_help" class="helptip" data-html="true" data-toggle="tooltip" href="#" title="<?php _e('Please enter the characters with black color in the image above.', 'empd-com'); ?>">
<span class="field-icons icons-help"></span>
</a>
<a id="info_captcha_code_req" class="helptip" title="<?php _e('Security Code field is required', 'empd-com'); ?>" data-toggle="tooltip" href="#">
<span class="field-icons icons-required"></span>
</a>
</label>
<?php echo $captcha_code; ?>
</div>
</div>
</div>
<?php
} ?>
<?php wp_nonce_field('search_employees', 'search_employees_nonce'); ?>
<input type="hidden" name="form_name" id="form_name" value="search_employees">
<!-- Button -->
<div class="row">
<div class="col-md-12">
<div class="wpas-form-actions">
<?php echo $singlebutton_search_employees; ?>
</div>
</div>
</div>
</div><!--form-btn-fields-->
</fieldset>