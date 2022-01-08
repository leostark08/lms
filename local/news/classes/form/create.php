<?php
//moodleform is defined in formslib.php
require_once("$CFG->libdir/formslib.php");

class create extends moodleform
{
    //Add elements to form
    public function definition()
    {
        global $CFG;

        $mform = $this->_form; // Don't forget the underscore!

        $mform->addElement('text', 'title', 'Title'); // Add elements to your form
        $mform->setType('title', PARAM_NOTAGS); //Set type of element
        $mform->setDefault('title', ''); //Default value

        $mform->addElement('editor', 'content', 'Content');
        $mform->setType('content', PARAM_CLEANHTML); //Set type of element
        $mform->setDefault('content', ''); //Default value

        $mform->addElement('checkbox', 'status', 'Status');

        $this->add_action_buttons();
    }
    //Custom validation should be added here
    function validation($data, $files)
    {
        return array();
    }
}
