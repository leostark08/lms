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
 * @copyright  2019 Sergio Comerón <sergiocomeron@icloud.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(dirname(dirname(dirname(__FILE__))).'/config.php');
require_once(dirname(__FILE__).'/lib.php');

// Allow CORS requests.
header('Access-Control-Allow-Origin: *');

global $USER;

$id = optional_param('id', 0, PARAM_INT);
$n  = optional_param('n', 0, PARAM_INT);
$state = optional_param('state', null, PARAM_TEXT);
$deletejitsirecordid = optional_param('deletejitsirecordid', 0, PARAM_INT);

if ($id) {
    $cm = get_coursemodule_from_id('jitsi', $id, 0, false, MUST_EXIST);
    $course = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);
    $jitsi = $DB->get_record('jitsi', array('id' => $cm->instance), '*', MUST_EXIST);
    $sesskey = optional_param('sesskey', null, PARAM_TEXT);
} else if ($n) {
    $jitsi  = $DB->get_record('jitsi', array('id' => $n), '*', MUST_EXIST);
    $course = $DB->get_record('course', array('id' => $jitsi->course), '*', MUST_EXIST);
    $cm = get_coursemodule_from_instance('jitsi', $jitsi->id, $course->id, false, MUST_EXIST);
} else if ($state) {
    $paramdecode = base64urldecode($state);
    $parametrosarray = explode("&", $paramdecode);
    $idarray = $parametrosarray[0];
    $deletejitsirecordidarray = $parametrosarray[1];
    $sesskeyarray = $parametrosarray[2];
    $statesesarray = $parametrosarray[3];
    $ida = explode("=", $idarray);
    $deletejitsirecordida = explode("=", $deletejitsirecordidarray);
    $sesskeya = explode("=", $sesskeyarray);
    $statesesa = explode("=", $statesesarray);
    $id = $ida[1];
    $deletejitsirecordid = $deletejitsirecordida[1];
    $sesskey = $sesskeya[1];
    $stateses = $statesesa[1];
    $cm = get_coursemodule_from_id('jitsi', $id, 0, false, MUST_EXIST);
    $course = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);
    $jitsi = $DB->get_record('jitsi', array('id' => $cm->instance), '*', MUST_EXIST);
} else {
    print_error('missingparam');
}

require_login($course, true, $cm);
$event = \mod_jitsi\event\course_module_viewed::create(array(
  'objectid' => $PAGE->cm->instance,
  'context' => $PAGE->context,
));

$event->add_record_snapshot('course', $PAGE->course);
$event->add_record_snapshot($PAGE->cm->modname, $jitsi);
$event->trigger();
$PAGE->set_url('/mod/jitsi/view.php', array('id' => $cm->id));

$PAGE->set_title(format_string($jitsi->name));
$PAGE->set_heading(format_string($course->fullname));

if ($deletejitsirecordid && confirm_sesskey($sesskey)) {
    deleterecordyoutube($deletejitsirecordid);
    redirect($PAGE->url, get_string('deleted'));
}

$context = context_module::instance($cm->id);
if (!has_capability('mod/jitsi:view', $context)) {
    notice(get_string('noviewpermission', 'jitsi'));
}
$courseid = $course->id;
$context = context_course::instance($courseid);
$roles = get_user_roles($context, $USER->id);

$rolestr[] = null;
foreach ($roles as $role) {
    $rolestr[] = $role->shortname;
}

$moderation = false;
if (has_capability('mod/jitsi:moderation', $context)) {
    $moderation = true;
}

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
$sessionoptionsparam = ['$course->shortname', '$jitsi->id', '$jitsi->name'];
$fieldssessionname = $CFG->jitsi_sesionname;

$allowed = explode(',', $fieldssessionname);
$max = count($allowed);

$sesparam = '';
$optionsseparator = ['.', '-', '_', ''];
for ($i = 0; $i < $max; $i++) {
    if ($i != $max - 1) {
        if ($allowed[$i] == 0) {
            $sesparam .= string_sanitize($course->shortname).$optionsseparator[$CFG->jitsi_separator];
        } else if ($allowed[$i] == 1) {
            $sesparam .= $jitsi->id.$optionsseparator[$CFG->jitsi_separator];
        } else if ($allowed[$i] == 2) {
            $sesparam .= string_sanitize($jitsi->name).$optionsseparator[$CFG->jitsi_separator];
        }
    } else {
        if ($allowed[$i] == 0) {
            $sesparam .= string_sanitize($course->shortname);
        } else if ($allowed[$i] == 1) {
            $sesparam .= $jitsi->id;
        } else if ($allowed[$i] == 2) {
            $sesparam .= string_sanitize($jitsi->name);
        }
    }
}

$avatar = $CFG->wwwroot.'/user/pix.php/'.$USER->id.'/f1.jpg';
$urlparams = array('avatar' => $avatar, 'nom' => $nom, 'ses' => $sesparam,
    'courseid' => $course->id, 'cmid' => $id, 't' => $moderation);

$today = getdate();
if (!$deletejitsirecordid) {
    echo $OUTPUT->header();
    echo $OUTPUT->heading($jitsi->name);
}

if ($today[0] < $jitsi->timeclose || $jitsi->timeclose == 0) {
    if ($today[0] > (($jitsi->timeopen) - ($jitsi->minpretime * 60))||
        (in_array('editingteacher', $rolestr) == 1)) {
        echo $OUTPUT->box(get_string('instruction', 'jitsi'));
        echo $OUTPUT->single_button(new moodle_url('/mod/jitsi/session.php', $urlparams), get_string('access', 'jitsi'), 'post');
    } else {
        echo $OUTPUT->box(get_string('nostart', 'jitsi', $jitsi->minpretime));
    }
} else {
    echo $OUTPUT->box(get_string('finish', 'jitsi'));
}

$records  = $DB->get_records('jitsi_record', array('jitsi' => $jitsi->id));

if ($records) {
    echo " ";
    echo "<button class=\"btn btn-secondary\" type=\"button\" data-toggle=\"collapse\" data-target=\"#collapseExample\"
         aria-expanded=\"false\" aria-controls=\"collapseExample\">";
    echo get_string('records', 'jitsi');
    echo "</button>";

    echo "<div class=\"collapse\" id=\"collapseExample\">";
    echo "<div class=\"card card-body\">";

    echo "<div class=\"row\">";
    foreach ($records as $record) {
        // Para borrar grabaciones.
        $deleteurl = new moodle_url('/mod/jitsi/view.php?id='.$cm->id.'&deletejitsirecordid=' .
                 $record->id . '&sesskey=' . sesskey());
        $deleteicon = new pix_icon('t/delete', get_string('delete'));
        $deleteaction = $OUTPUT->action_icon($deleteurl, $deleteicon, new confirm_action('Delete?'));

        echo "<div class=\"col-sm-6\">";
        echo "<div class=\"card\">";
        echo "<div class=\"card-body\">";
        echo "<div class=\"embed-responsive embed-responsive-16by9\">";
        echo "<iframe class=\"embed-responsive-item\" src=\"https://youtube.com/embed/".$record->link."\"
             allowfullscreen></iframe>";
        echo "</div>";
        echo "<div class=\"row\">";
        echo "<div class=\"col-sm\">";
        echo "<a href=\"".$record->link."\" class=\"btn btn-primary\">".get_string('fullscreen', 'jitsi')."</a>";
        echo "</div>";
        echo "  <div class=\"col-sm\">";
        if (has_capability('mod/jitsi:record', $context)) {
            echo "<span class=\"align-middle text-right\"><p>".$deleteaction."</p></span>";
        }
        echo "</div>";
        echo "</div>";
        echo "</div>";
        echo "</div>";
        echo "</div>";
    }
    echo "</div>";

    echo "</div>";
    echo "</div>";
}
if ($jitsi->intro) {
    echo $OUTPUT->box(format_module_intro('jitsi', $jitsi, $cm->id), 'generalbox mod_introbox', 'jitsiintro');
}
echo $CFG->jitsi_help;
echo "<hr>";
echo $OUTPUT->footer();
