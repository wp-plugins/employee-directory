<?php

/**
 *  Class for CAPTCHA controls.
 *
 *  @author     Stefan Gabos <contact@stefangabos.ro>
 *  @copyright  (c) 2006 - 2013 Stefan Gabos
 *  @package    Controls
 */

class Zebra_Form_Captcha extends Zebra_Form_Control
{

    /**
     *  Adds a CAPTCHA image to the form.
     *
     *  @param  string  $id             Unique name to identify the control in the form.
     *
     *  @param  string  $attach_to      The <b>id</b> attribute of the {@link Zebra_Form_Text textbox} control to attach
     *                                  the CAPTCHA image to.
     *
     *  @return void
     */
    function __construct($id, $attach_to, $storage = 'cookie',$captcha_fa, $class)
    {

        // call the constructor of the parent class
        parent::__construct();
        
        // set the private attributes of this control
        // these attributes are private for this control and are for internal use only
        // and will not be rendered by the _render_attributes() method
        $this->private_attributes = array(

            'disable_xss_filters',
            'for',
            'locked',

        );

        // set the default attributes for the text control
        // put them in the order you'd like them rendered
        $this->set_attributes(
        
            array(
            
                'type'      =>  'captcha',
                'name'      =>  $id,
                'id'        =>  $id,
                'for'       =>  $attach_to,
		'fa'        =>  $captcha_fa,
		'class'     =>  $class,
            )
            
        );

    }

    /**
     *  Generates the control's HTML code.
     *
     *  @return string  The control's HTML code
     */
    function toHTML()
    {
        return '<div class="captcha-container"><img src="' . $this->form_properties['assets_url'] . 'process.php?captcha=' . ($this->form_properties['captcha_storage'] == 'session' ? 2 : 1) . '&amp;nocache=' . time() . '" alt=""' . ($this->form_properties['doctype'] == 'xhtml' ? '/' : '') . '><a href="javascript:void(0)" id="refcapt" title="' . $this->form_properties['language']['new_captcha'] . '" class="' . $this->attributes['class'] . '">' . $this->attributes['fa']  . '</a></div>';
    
    }
    
}

?>
