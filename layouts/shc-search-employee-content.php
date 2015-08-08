<?php global $search_employee_count;
$ent_attrs = get_option('empd_com_attr_list'); ?>
<tr>
    <td><a href="<?php echo get_permalink(); ?>"><?php echo esc_html(emd_mb_meta('emd_employee_number')); ?>
</a></td>
    <td><a href="<?php echo get_permalink(); ?>"><?php echo get_the_title(); ?></a></td>
    <td><?php echo esc_html(emd_mb_meta('emd_employee_email')); ?>
</td>
    <td><?php echo get_the_term_list(get_the_ID() , 'departments', '', ' ', ''); ?></td>
    <td><?php echo get_the_term_list(get_the_ID() , 'jobtitles', '', ' ', ''); ?></td>
</tr>