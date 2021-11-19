<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Prints a particular instance of jitsi
 *
 * You can have a rather longer description of the file as well,
 * if you like, and it can span multiple lines.
 *
 * @package    mod_jitsi
 * @copyright  2021 Sergio Comerón <sergiocomeron@icloud.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(dirname(dirname(dirname(__FILE__))).'/config.php');
require_once(dirname(dirname(dirname(__FILE__))).'/lib/moodlelib.php');
require_once(dirname(__FILE__).'/lib.php');
require_once("$CFG->libdir/formslib.php");

$id = required_param('id', PARAM_INT);

$timestamp = required_param('t', PARAM_INT);
$codet = required_param('c', PARAM_INT);

$cm = get_coursemodule_from_id('jitsi', $id, 0, false, MUST_EXIST);
$sessionid = $cm->instance;

$code = $codet - $timestamp;
global $DB;

class name_form extends moodleform {
    public function definition() {
        global $CFG, $USER;

        $mform = $this->_form;
        $mform->addElement('text', 'name', get_string('name'));
        $mform->addElement('text', 'mail', get_string('email'));
        $mform->setType('name', PARAM_TEXT);
        $mform->setType('mail', PARAM_EMAIL);
        $mform->addRule('name', get_string('required'), 'required', '', 'client', false, false);
        $mform->addRule('mail', get_string('missingemail'), 'email', null, 'client');

        $buttonarray = array();
        $buttonarray[] = $mform->createElement('submit', 'submitbutton', get_string('continue'));
        $mform->addGroup($buttonarray, 'buttonar', '', ' ', false);
    }
    public function validation($data, $files) {
        return array();
    }
}

$PAGE->set_url($CFG->wwwroot.'/mod/jitsi/formuniversal.php');
$sesion = $DB->get_record('jitsi', array('id' => $sessionid));
$PAGE->set_cm($cm);
$PAGE->set_context(context_module::instance($id));

$PAGE->set_title(get_string('accesstotitle', 'jitsi', $sesion->name));
$PAGE->set_heading(get_string('accesstotitle', 'jitsi', $sesion->name));

echo $OUTPUT->header();

$event = \mod_jitsi\event\jitsi_session_guest_form::create(array(
  'objectid' => $PAGE->cm->instance,
  'context' => $PAGE->context,
));
$event->add_record_snapshot('course', $PAGE->course);
$event->add_record_snapshot($PAGE->cm->modname, $sesion);

$event->trigger();

if (isoriginal($code, $sesion)) {
    if (!istimedout($timestamp, $sesion)) {
        if ($CFG->jitsi_invitebuttons == 1) {
            if (!isloggedin()) {
                echo get_string('accessto', 'jitsi', $sesion->name);
                $today = getdate();
                if ($today[0] < $sesion->timeclose || $sesion->timeclose == 0) {
                    if ($today[0] > (($sesion->timeopen) - ($sesion->minpretime * 60))||
                        (in_array('editingteacher', $rolestr) == 1)) {
                        $mform = new name_form($CFG->wwwroot.'/mod/jitsi/universal.php?ses='.
                                            $sessionid.'&id='.$id.'&t='.$timestamp.'&c='.$codet);

                        if ($mform->is_cancelled()) {
                            echo "";
                        } else if ($fromform = $mform->get_data()) {
                            echo "";
                        } else {
                            $mform->display();
                        }
                        echo get_string('mailprivacy', 'jitsi');
                    } else {
                        echo $OUTPUT->box(get_string('nostart', 'jitsi', $session->minpretime));
                    }
                } else {
                    echo $OUTPUT->box(get_string('finish', 'jitsi'));
                }
            } else {
                echo get_string('accesstowithlogin', 'jitsi', $sesion->name);
                $today = getdate();
                if ($today[0] > (($sesion->timeopen) - ($sesion->minpretime * 60))||
                    (in_array('editingteacher', $rolestr) == 1)) {
                    $nom = null;
                    switch ($CFG->jitsi_id) {
                        case 'username':
                            $nom = $USER->username;
                            break;
                        case 'nameandsurname':
                            $nom = $USER->firstname.' '.$USER->lastname;
                            break;
                        case 'alias':
                            break;
                    }
                    $avatar = $CFG->wwwroot.'/user/pix.php/'.$USER->id.'/f1.jpg';
                    $mail = '';
                    $urlparams = array('avatar' => $avatar, 'name' => $nom, 'ses' => $sessionid,
                                    'mail' => $mail, 'id' => $id, 't' => $timestamp, 'c' => $codet);
                    echo $OUTPUT->box(get_string('instruction', 'jitsi'));
                    echo $OUTPUT->single_button(new moodle_url('/mod/jitsi/universal.php', $urlparams),
                        get_string('access', 'jitsi'), 'post');
                } else {
                    echo $OUTPUT->box(get_string('nostart', 'jitsi', $sesion->minpretime));
                }
            }
        } else {
            echo get_string('noinviteaccess', 'jitsi');
        }
    } else {
        $time = $sesion->validitytime;
        echo "This link expired on ";
        $limit = $timestamp + $time;
        echo userdate($limit);
    }
} else {
    echo "Invalid URL";
}

echo $OUTPUT->footer();
