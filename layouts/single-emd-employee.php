<?php $ent_attrs = get_option('empd_com_attr_list'); ?>
<div class="emd-container">
<?php $images = emd_mb_meta('emd_employee_photo', 'type=thickbox_image');
if (!empty($images)) { ?>
    <div id="emd-employee-emd-employee-photo-div" class="emd-single-div">
    <div id="emd-employee-emd-employee-photo-key" class="emd-single-title">
    <?php _e('Photo', 'empd-com'); ?>
    </div>
    <div id="emd-employee-emd-employee-photo-val" class="emd-single-val">
    <?php foreach ($images as $image) { ?>
    <a href='<?php echo esc_html($image['full_url']); ?>' title='<?php echo esc_attr($image['title']); ?>' rel='thickbox'>
    <img src='<?php echo esc_url($image['url']); ?>' width='<?php echo esc_attr($image['width']); ?>' height='<?php echo esc_attr($image['height']); ?>' alt='<?php echo esc_attr($image['alt']); ?>' />
    </a>
    <?php
	} ?>
    </div>
    </div>
<?php
} ?>
<?php
$emd_employee_featured = emd_mb_meta('emd_employee_featured');
if (!empty($emd_employee_featured)) { ?>
   <div id="emd-employee-emd-employee-featured-div" class="emd-single-div">
   <div id="emd-employee-emd-employee-featured-key" class="emd-single-title">
<?php _e('Featured', 'empd-com'); ?>
   </div>
   <div id="emd-employee-emd-employee-featured-val" class="emd-single-val">
<?php echo $emd_employee_featured; ?>
   </div>
   </div>
<?php
} ?>
<?php
$emd_employee_number = emd_mb_meta('emd_employee_number');
if (!empty($emd_employee_number)) { ?>
   <div id="emd-employee-emd-employee-number-div" class="emd-single-div">
   <div id="emd-employee-emd-employee-number-key" class="emd-single-title">
<?php _e('Employee No', 'empd-com'); ?>
   </div>
   <div id="emd-employee-emd-employee-number-val" class="emd-single-val">
<?php echo $emd_employee_number; ?>
   </div>
   </div>
<?php
} ?>
<?php $emd_employee_hiredate = emd_mb_meta('emd_employee_hiredate');
if (!empty($emd_employee_hiredate)) {
	$emd_employee_hiredate = emd_translate_date_format($ent_attrs['emd_employee']['emd_employee_hiredate'], $emd_employee_hiredate, 1);
?>
   <div id="emd-employee-emd-employee-hiredate-div" class="emd-single-div">
   <div id="emd-employee-emd-employee-hiredate-key" class="emd-single-title">
   <?php _e('Hire Date', 'empd-com'); ?>
   </div>
   <div id="emd-employee-emd-employee-hiredate-val" class="emd-single-val">
   <?php echo esc_html($emd_employee_hiredate); ?>
   </div></div>
<?php
} ?>
<?php $emd_employee_birthday = emd_mb_meta('emd_employee_birthday');
if (!empty($emd_employee_birthday)) {
	$emd_employee_birthday = emd_translate_date_format($ent_attrs['emd_employee']['emd_employee_birthday'], $emd_employee_birthday, 1);
?>
   <div id="emd-employee-emd-employee-birthday-div" class="emd-single-div">
   <div id="emd-employee-emd-employee-birthday-key" class="emd-single-title">
   <?php _e('Birthday', 'empd-com'); ?>
   </div>
   <div id="emd-employee-emd-employee-birthday-val" class="emd-single-val">
   <?php echo esc_html($emd_employee_birthday); ?>
   </div></div>
<?php
} ?>
<?php
$emd_employee_primary_address = emd_mb_meta('emd_employee_primary_address');
if (!empty($emd_employee_primary_address)) { ?>
   <div id="emd-employee-emd-employee-primary-address-div" class="emd-single-div">
   <div id="emd-employee-emd-employee-primary-address-key" class="emd-single-title">
<?php _e('Mailing Address', 'empd-com'); ?>
   </div>
   <div id="emd-employee-emd-employee-primary-address-val" class="emd-single-val">
<?php echo $emd_employee_primary_address; ?>
   </div>
   </div>
<?php
} ?>
<?php
$emd_employee_phone = emd_mb_meta('emd_employee_phone');
if (!empty($emd_employee_phone)) { ?>
   <div id="emd-employee-emd-employee-phone-div" class="emd-single-div">
   <div id="emd-employee-emd-employee-phone-key" class="emd-single-title">
<?php _e('Phone', 'empd-com'); ?>
   </div>
   <div id="emd-employee-emd-employee-phone-val" class="emd-single-val">
<?php echo $emd_employee_phone; ?>
   </div>
   </div>
<?php
} ?>
<?php
$emd_employee_extension = emd_mb_meta('emd_employee_extension');
if (!empty($emd_employee_extension)) { ?>
   <div id="emd-employee-emd-employee-extension-div" class="emd-single-div">
   <div id="emd-employee-emd-employee-extension-key" class="emd-single-title">
<?php _e('Extension', 'empd-com'); ?>
   </div>
   <div id="emd-employee-emd-employee-extension-val" class="emd-single-val">
<?php echo $emd_employee_extension; ?>
   </div>
   </div>
<?php
} ?>
<?php
$emd_employee_mobile = emd_mb_meta('emd_employee_mobile');
if (!empty($emd_employee_mobile)) { ?>
   <div id="emd-employee-emd-employee-mobile-div" class="emd-single-div">
   <div id="emd-employee-emd-employee-mobile-key" class="emd-single-title">
<?php _e('Mobile', 'empd-com'); ?>
   </div>
   <div id="emd-employee-emd-employee-mobile-val" class="emd-single-val">
<?php echo $emd_employee_mobile; ?>
   </div>
   </div>
<?php
} ?>
<?php
$emd_employee_email = emd_mb_meta('emd_employee_email');
if (!empty($emd_employee_email)) { ?>
   <div id="emd-employee-emd-employee-email-div" class="emd-single-div">
   <div id="emd-employee-emd-employee-email-key" class="emd-single-title">
<?php _e('Email', 'empd-com'); ?>
   </div>
   <div id="emd-employee-emd-employee-email-val" class="emd-single-val">
<?php echo $emd_employee_email; ?>
   </div>
   </div>
<?php
} ?>
<?php
$emd_employee_facebook = emd_mb_meta('emd_employee_facebook');
if (!empty($emd_employee_facebook)) { ?>
   <div id="emd-employee-emd-employee-facebook-div" class="emd-single-div">
   <div id="emd-employee-emd-employee-facebook-key" class="emd-single-title">
<?php _e('Facebook', 'empd-com'); ?>
   </div>
   <div id="emd-employee-emd-employee-facebook-val" class="emd-single-val">
<?php echo $emd_employee_facebook; ?>
   </div>
   </div>
<?php
} ?>
<?php
$emd_employee_google = emd_mb_meta('emd_employee_google');
if (!empty($emd_employee_google)) { ?>
   <div id="emd-employee-emd-employee-google-div" class="emd-single-div">
   <div id="emd-employee-emd-employee-google-key" class="emd-single-title">
<?php _e('Google+', 'empd-com'); ?>
   </div>
   <div id="emd-employee-emd-employee-google-val" class="emd-single-val">
<?php echo $emd_employee_google; ?>
   </div>
   </div>
<?php
} ?>
<?php
$emd_employee_twitter = emd_mb_meta('emd_employee_twitter');
if (!empty($emd_employee_twitter)) { ?>
   <div id="emd-employee-emd-employee-twitter-div" class="emd-single-div">
   <div id="emd-employee-emd-employee-twitter-key" class="emd-single-title">
<?php _e('Twitter', 'empd-com'); ?>
   </div>
   <div id="emd-employee-emd-employee-twitter-val" class="emd-single-val">
<?php echo $emd_employee_twitter; ?>
   </div>
   </div>
<?php
} ?>
<?php
$emd_employee_linkedin = emd_mb_meta('emd_employee_linkedin');
if (!empty($emd_employee_linkedin)) { ?>
   <div id="emd-employee-emd-employee-linkedin-div" class="emd-single-div">
   <div id="emd-employee-emd-employee-linkedin-key" class="emd-single-title">
<?php _e('Linkedin', 'empd-com'); ?>
   </div>
   <div id="emd-employee-emd-employee-linkedin-val" class="emd-single-val">
<?php echo $emd_employee_linkedin; ?>
   </div>
   </div>
<?php
} ?>
<?php
$emd_employee_github = emd_mb_meta('emd_employee_github');
if (!empty($emd_employee_github)) { ?>
   <div id="emd-employee-emd-employee-github-div" class="emd-single-div">
   <div id="emd-employee-emd-employee-github-key" class="emd-single-title">
<?php _e('Github', 'empd-com'); ?>
   </div>
   <div id="emd-employee-emd-employee-github-val" class="emd-single-val">
<?php echo $emd_employee_github; ?>
   </div>
   </div>
<?php
} ?>
<?php $blt_content = $post->post_content;
if (!empty($blt_content)) { ?>
   <div id="emd-employee-blt-content-div" class="emd-single-div">
   <div id="emd-employee-blt-content-key" class="emd-single-title">
   <?php _e('Bio', 'empd-com'); ?>
   </div>
   <div id="emd-employee-blt-content-val" class="emd-single-val">
   <?php echo $blt_content; ?>
   </div>
   </div>
<?php
} ?>
<?php
$taxlist = get_object_taxonomies(get_post_type() , 'objects');
foreach ($taxlist as $taxkey => $mytax) {
	$termlist = get_the_term_list(get_the_ID() , $taxkey, '', ' , ', '');
	if (!empty($termlist)) { ?>
      <div id="emd-employee-<?php echo esc_attr($taxkey); ?>-div" class="emd-single-div">
      <div id="emd-employee-<?php echo esc_attr($taxkey); ?>-key" class="emd-single-title">
      <?php echo esc_html($mytax->labels->singular_name); ?>
      </div>
      <div id="emd-employee-<?php echo esc_attr($taxkey); ?>-val" class="emd-single-val">
      <?php echo $termlist; ?>
      </div>
      </div>
   <?php
	}
} ?>
</div><!--container-end-->