<?php

/**
 *  A generic class containing common methods, shared by all the controls.
 */
class Zebra_Form_Control extends XSS_Clean
{

    /**
     *  Array of HTML attributes of the element
     *
     *  @var array
     *
     *  @access private
     */
    public $attributes;

    /**
     *  Array of HTML attributes that the control's {@link render_attributes()} method should skip
     *
     *  @var array
     *
     *  @access private
     */
    public $private_attributes;

    /**
     *  Array of validation rules set for the control
     *
     *  @var array
     *
     *  @access private
     */
    public $rules;

    /**
     *  Constructor of the class
     *
     *  @return void
     *
     *  @access private
     */
    function __construct()
    {

        $this->attributes = array(

            'locked' => false,
            'disable_xss_filters' => false,

        );

        $this->private_attributes = array();

        $this->rules = array();

    }

    /**
     *  Call this method to instruct the script to force all letters typed by the user, to either uppercase or lowercase,
     *  in real-time.
     *
     *  @param  string  $case   The case to convert all entered characters to.
     *
     *                          Can be (case-insensitive) "upper" or "lower".
     *
     *  @since  2.8
     *
     *  @return void
     */
    function change_case($case)
    {

        // make sure the argument is lowercase
        $case = strtolower($case);

        // if valid case specified
        if ($case == 'upper' || $case == 'lower')

            // add an extra class to the element
            $this->set_attributes(array('class' => 'modifier-' . $case . 'case'), false);

    }

    /**
     *  Disables the SPAM filter for the control.
     *  @return void
     */
    function disable_spam_filter()
    {

        // set the "disable_xss_filters" private attribute of the control
        $this->set_attributes(array('disable_spam_filter' => true));

    }

    /**
     *  Disables XSS filtering of the control's submitted value.
     *
     *  @return void
     */
    function disable_xss_filters()
    {

        // set the "disable_xss_filters" private attribute of the control
        $this->set_attributes(array('disable_xss_filters' => true));

    }

    /**
     *  Returns the values of requested attributes.
     *
     *  @param  mixed   $attributes     A single or an array of attributes for which the values to be returned.
     *
     *  @return array                   Returns an associative array where keys are the attributes and the values are
     *                                  each attribute's value, respectively.
     */
    function get_attributes($attributes)
    {

        // initialize the array that will be returned
        $result = array();

        // if the request was for a single attribute,
        // treat it as an array of attributes
        if (!is_array($attributes)) $attributes = array($attributes);

        // iterate through the array of attributes to look for
        foreach ($attributes as $attribute)

            // if attribute exists
            if (array_key_exists($attribute, $this->attributes))

                // populate the $result array
                $result[$attribute] = $this->attributes[$attribute];

        // return the results
        return $result;

    }

    /**
     *  Returns the control's value <b>after</b> the form is submitted.
     *
     *  @return void
     */
    function get_submitted_value()
    {

        // get some attributes of the control
        $attribute = $this->get_attributes(array('name', 'type', 'value', 'disable_xss_filters', 'locked'));

        // if control's value is not locked to the default value
        if ($attribute['locked'] !== true) {

            // strip any [] from the control's name (usually used in conjunction with multi-select select boxes and
            // checkboxes)
            $attribute['name'] = preg_replace('/\[\]/', '', $attribute['name']);

            // reference to the form submission method
            global ${'_' . $this->form_properties['method']};

            $method = & ${'_' . $this->form_properties['method']};

	    //added to decode encoded spaces with ajax submit
            foreach($method as $method_key => $mymethod)
            {
                if(is_array($mymethod))
                {
                        $mymethod = array_map("urldecode",$mymethod);
                        $method[$method_key] = $mymethod;
                }
                else
                {
                        $method[$method_key] = urldecode($mymethod);
                }
            }

            // if form was submitted
            if (

                isset($method[$this->form_properties['identifier']]) &&

                $method[$this->form_properties['identifier']] == $this->form_properties['name']

            ) {

                // if control is a time picker control
                if ($attribute['type'] == 'time') {

                    // combine hour, minutes and seconds into one single string (values separated by :)
                    // hours
                    $combined = (isset($method[$attribute['name'] . '_hours']) ? $method[$attribute['name'] . '_hours'] : '');
                    // minutes
                    $combined .= (isset($method[$attribute['name'] . '_minutes']) ? ($combined != '' ? ':' : '') . $method[$attribute['name'] . '_minutes'] : '');
                    // seconds
                    $combined .= (isset($method[$attribute['name'] . '_seconds']) ? ($combined != '' ? ':' : '') . $method[$attribute['name'] . '_seconds'] : '');
                    // AM/PM
                    $combined .= (isset($method[$attribute['name'] . '_ampm']) ? ($combined != '' ? ' ' : '') . $method[$attribute['name'] . '_ampm'] : '');

                    // create a super global having the name of our time picker control
                    // we need to do this so that the values will also be filtered for XSS injection
                    $method[$attribute['name']] = $combined;

                    // unset the three temporary fields as we want to return to the user the result in a single field
                    // having the name he supplied
                    unset($method[$attribute['name'] . '_hours']);
                    unset($method[$attribute['name'] . '_minutes']);
                    unset($method[$attribute['name'] . '_seconds']);
                    unset($method[$attribute['name'] . '_ampm']);

                }

                // if control was submitted
                if (isset($method[$attribute['name']])) {

                    // create the submitted_value property for the control and
                    // assign to it the submitted value of the control
                    $this->submitted_value = $method[$attribute['name']];

                    // if submitted value is an array
                    if (is_array($this->submitted_value)) {

                        // iterate through the submitted values
                        foreach ($this->submitted_value as $key => $value)

                            // and also, if magic_quotes_gpc is on (meaning that
                            // both single and double quotes are escaped)
                            // strip those slashes
                            if (get_magic_quotes_gpc()) $this->submitted_value[$key] = stripslashes($value);

                    // if submitted value is not an array
                    } else

                        // and also, if magic_quotes_gpc is on (meaning that both
                        // single and double quotes are escaped)
                        // strip those slashes
                        if (get_magic_quotes_gpc()) $this->submitted_value = stripslashes($this->submitted_value);

                    // since 1.1
                    // if XSS filtering is not disabled
                    if ($attribute['disable_xss_filters'] !== true) {

                        // if submitted value is an array
                        if (is_array($this->submitted_value))

                            // iterate through the submitted values
                            foreach ($this->submitted_value as $key => $value)

                                // filter the control's value for XSS injection
                                $this->submitted_value[$key] = htmlspecialchars($this->sanitize($value));

                        // if submitted value is not an array, filter the control's value for XSS injection
                        else $this->submitted_value = htmlspecialchars($this->sanitize($this->submitted_value));

                        // set the respective $_POST/$_GET value to the filtered value
                        $method[$attribute['name']] = $this->submitted_value;

                    }

                // if control is a file upload control and a file was indeed uploaded
                } elseif ($attribute['type'] == 'file' && isset($_FILES[$attribute['name']]))

                    $this->submitted_value = true;

                // if control was not submitted
                else $this->submitted_value = false;

                if (

                    //if type is password, textarea or text OR
                    ($attribute['type'] == 'password' || $attribute['type'] == 'textarea' || $attribute['type'] == 'text') &&

                    // control has the "uppercase" or "lowercase" modifier set
                    preg_match('/\bmodifier\-uppercase\b|\bmodifier\-lowercase\b/i', $this->attributes['class'], $modifiers)

                ) {

                    // if string must be uppercase, update the value accordingly
                    if ($modifiers[0] == 'modifier-uppercase') $this->submitted_value = strtoupper($this->submitted_value);

                    // otherwise, string needs to be lowercase
                    else $this->submitted_value = strtolower($this->submitted_value);

                    // set the respective $_POST/$_GET value to the updated value
                    $method[$attribute['name']] = $this->submitted_value;

                }

            }

            // if control was submitted
            if (isset($this->submitted_value)) {

                // the assignment of the submitted value is type dependant
                switch ($attribute['type']) {

                    // if control is a checkbox
                    case 'checkbox':

                        if (

                            (

	                            // if is submitted value is an array
								is_array($this->submitted_value) &&

	                            // and the checkbox's value is in the array
	                            in_array($attribute['value'], $this->submitted_value)

							// OR
							) ||

                            // assume submitted value is not an array and the
                            // checkbox's value is the same as the submitted value
                            $attribute['value'] == $this->submitted_value

                        // set the "checked" attribute of the control
                        ) $this->set_attributes(array('checked' => 'checked'));

                        // if checkbox was "submitted" as not checked
                        // and if control's default state is checked, uncheck it
                        elseif (isset($this->attributes['checked'])) unset($this->attributes['checked']);

                        break;

                    // if control is a radio button
                    case 'radio':

                        if (

                            // if the radio button's value is the same as the
                            // submitted value
                            ($attribute['value'] == $this->submitted_value)

                        // set the "checked" attribute of the control
                        ) $this->set_attributes(array('checked' => 'checked'));

                        break;

                    // if control is a select box
                    case 'select':
                    case 'select_advanced':

                        // set the "value" private attribute of the control
                        // the attribute will be handled by the
                        // Zebra_Form_Select::_render_attributes() method
                        $this->set_attributes(array('value' => $this->submitted_value));

                        break;

                    // if control is a file upload control, a hidden control, a password field, a text field or a textarea control
                    case 'file':
                    case 'hidden':
                    case 'password':
                    case 'text':
                    case 'textarea':
                    case 'time':

                        // set the "value" standard HTML attribute of the control
                        $this->set_attributes(array('value' => $this->submitted_value));

                        break;

                }

            }

        }

    }

    /**
     *  Locks the control's value. A <i>locked</i> control will preserve its default value after the form is submitted
     *  even if the user altered it.
     *  @return void
     */
    function lock() {

        // set the "locked" private attribute of the control
        $this->set_attributes(array('locked' => true));

    }

    /**
     *  Resets the control's submitted value (empties text fields, unchecks radio buttons/checkboxes, etc).
     *
     *  @return void
     */
    function reset()
    {

        // reference to the form submission method
        global ${'_' . $this->form_properties['method']};

        $method = & ${'_' . $this->form_properties['method']};

        // get some attributes of the control
        $attributes = $this->get_attributes(array('type', 'name', 'other'));

        // sanitize the control's name
        $attributes['name'] = preg_replace('/\[\]/', '', $attributes['name']);

        // see of what type is the current control
        switch ($attributes['type']) {

            // control is any of the types below
            case 'checkbox':
            case 'radio':

                // unset the "checked" attribute
                unset($this->attributes['checked']);

                // unset the associated superglobal
                unset($method[$attributes['name']]);

                break;

            // control is any of the types below
            case 'date':
            case 'hidden':
            case 'password':
            case 'select':
            case 'text':
            case 'textarea':

                // simply empty the "value" attribute
                $this->attributes['value'] = '';

                // unset the associated superglobal
                unset($method[$attributes['name']]);

                // if control has the "other" attribute set
                if (isset($attributes['other']))

                    // clear the associated superglobal's value
                    unset($method[$attributes['name'] . '_other']);

                break;

            // control is a file upload control
            case 'file':

                // unset the related superglobal
                unset($_FILES[$attributes['name']]);

                break;

            // for any other control types
            default:

                // as long as control is not label, note nor captcha
                if (

                    $attributes['type'] != 'label' &&
                    $attributes['type'] != 'note' &&
                    $attributes['type'] != 'captcha'

                // unset the associated superglobal
                ) unset($method[$attributes['name']]);

        }

    }

    /**
     *  Sets one or more of the control's attributes.
     *
     *  @param  array       $attributes     An associative array, in the form of <i>attribute => value</i>.
     *
     *  @param  boolean     $overwrite      Setting this argument to FALSE will instruct the script to append the values
     *                                      of the attributes to the already existing ones (if any) rather then overwriting
     *                                      them.
     *                                      Default is TRUE
     *
     *  @return void
     */
    function set_attributes($attributes, $overwrite = true)
    {

        // check if $attributes is given as an array
        if (is_array($attributes))

            // iterate through the given attributes array
            foreach ($attributes as $attribute => $value) {

                // we need to url encode the prefix as it may contain HTML entities which would produce validation errors
                if ($attribute == 'data-prefix') $value = urlencode($value);

                // if the value is to be appended to the already existing one
                // and there is a value set for the specified attribute
                // and the values do not represent an array
                if (!$overwrite && isset($this->attributes[$attribute]) && !is_array($this->attributes[$attribute]))

                    // append the value
                    $this->attributes[$attribute] = $this->attributes[$attribute] . ' ' . $value;

                // otherwise, add attribute to attributes array
                else $this->attributes[$attribute] = $value;

            }

    }

    /**
     *  Sets a single or an array of validation rules for the control.
     *  @param  array   $rules  An associative array
     *
     *  @return void
     */
    function set_rule($rules)
    {

        // continue only if argument is an array
        if (is_array($rules))

            // iterate through the given rules
            foreach ($rules as $rule_name => $rule_properties) {

                // make sure the rule's name is lowercase
                $rule_name = strtolower($rule_name);

                // if custom rule
                if ($rule_name == 'custom')

                    // if more custom rules are specified at once
                    if (is_array($rule_properties[0]))

                        // iterate through the custom rules
                        // and add them one by one
                        foreach ($rule_properties as $rule) $this->rules[$rule_name][] = $rule;

                    // if a single custom rule is specified
                    // save the custom rule to the "custom" rules array
                    else $this->rules[$rule_name][] = $rule_properties;

                // for all the other rules
                // add the rule to the rules array
                else $this->rules[$rule_name] = $rule_properties;

                // for some rules we do some additional settings
                switch ($rule_name) {

                    // we set a reserved attribute for the control by which we're telling the
                    // _render_attributes() method to append a special class to the control when rendering it
                    // so that we can also control user input from javascript
                    case 'alphabet':
                    case 'digits':
                    case 'alphanumeric':
                    case 'number':
                    case 'float':
                        break;

                    // if the rule is about the length of the input
                    case 'length':

                        // if there is a maximum of allowed characters
                        if ($rule_properties[1] > 0) {

                            // set the maxlength attribute of the control
                            $this->set_attributes(array('maxlength' => $rule_properties[1]));

                            // if there is a 5th argument to the rule, the argument is boolean true
                            if (isset($rule_properties[4]) && $rule_properties[4] === true) {

                                // add an extra class so that the JavaScript library will know to show the character counter
                                $this->set_attributes(array('class' => 'show-character-counter'), false);

                            }

                        }

                        break;

                }

            }

    }

    /**
     *  Converts the array with control's attributes to valid HTML markup interpreted by the {@link toHTML()} method
     *
     *  @return string  Returns a string with the control's attributes
     *
     *  @access private
     */
    protected function _render_attributes()
    {

        // the string to be returned
        $attributes = '';

        // if
        if (

            // control has the "disabled" attribute set
            isset($this->attributes['disabled']) &&

            $this->attributes['disabled'] == 'disabled' &&

            // control is not a radio button
            $this->attributes['type'] != 'radio' &&

            // control is not a checkbox
            $this->attributes['type'] != 'checkbox'

        // add another class to the control
        ) $this->set_attributes(array('class' => 'disabled'), false);

        // iterates through the control's attributes
        foreach ($this->attributes as $attribute => $value)

            if (

                // if control has no private attributes or the attribute is not  a private attribute
                (!isset($this->private_attributes) || !in_array($attribute, $this->private_attributes)) &&

                // and control has no private javascript attributes or the attribute is not in a javascript private attribute
                (!isset($this->javascript_attributes) || !in_array($attribute, $this->javascript_attributes))

            )

                // add attribute => value pair to the return string
                $attributes .=

                    ($attributes != '' ? ' ' : '') . $attribute . '="' . preg_replace('/\"/', '&quot;', $value) . '"';

        // returns string
        return $attributes;

    }

}

?>
