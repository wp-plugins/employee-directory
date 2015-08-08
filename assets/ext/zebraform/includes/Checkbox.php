<?php

/**
 *  Class for checkbox controls.
 *
 *  @author     Stefan Gabos <contact@stefangabos.ro>
 *  @copyright  (c) 2006 - 2013 Stefan Gabos
 *  @package    Controls
 */
class Zebra_Form_Checkbox extends Zebra_Form_Control
{

    /**
     *  Constructor of the class
     *
     *  Adds an <input type="checkbox"> control to the form.
     *
     *  @param  string  $id             Unique name to identify the control in the form.
     *
     *  @param  mixed   $value          Value of the checkbox.
     *
     *  @param  array   $attributes     (Optional) An array of attributes valid for
     *                                  {@link http://www.w3.org/TR/REC-html40/interact/forms.html#h-17.4 input}
     *                                  controls (disabled, readonly, style, etc)
     *
     *  @return void
     */
    function __construct($id, $value, $attributes = '')
    {
    
        // call the constructor of the parent class
        parent::__construct();
    
        // set the private attributes of this control
        // these attributes are private for this control and are for internal use only
        // and will not be rendered by the _render_attributes() method
        $this->private_attributes = array(

            'disable_spam_filter',
            'disable_xss_filters',
            'locked',

        );

        // set the default attributes for the checkbox control
        // put them in the order you'd like them rendered
        $this->set_attributes(
        
            array(
            
                'type'  =>  'checkbox',
                'name'  =>  $id . '[]',
                'id'    =>  str_replace(array(' ', '[', ']'), array('_', ''), $id) . '_' . str_replace(' ', '_', $value),
                'value' =>  $value,
                'class' =>  'control checkbox',

            )
            
        );
        
        // if "class" is amongst user specified attributes
        if (is_array($attributes) && isset($attributes['class'])) {

            // we need to set the "class" attribute like this, so it doesn't overwrite previous values
            $this->set_attributes(array('class' => $attributes['class']), false);

            // make sure we don't set it again below
            unset($attributes['class']);

        }

        // sets user specified attributes for the control
        $this->set_attributes($attributes);
        
    }
    
    /**
     *  Generates the control's HTML code.
     *
     *  @return string  The control's HTML code
     */
    function toHTML()
    {
    
        return '<input ' . $this->_render_attributes() . ($this->form_properties['doctype'] == 'xhtml' ? '/' : '') . '>';

    }

}

?>
