<?php
require_once(__DIR__ . '/../../config.php');
require_once($CFG->dirroot . '/local/news/classes/form/create.php');

global $DB;

try {
    $PAGE->set_url(new moodle_url('/local/news/create.php'));
} catch (coding_exception $e) {
    print_error($e);
}
$PAGE->set_context(\context_system::instance());
$PAGE->set_title('Create News');

$mform = new create();


//Form processing and displaying is done here
if ($mform->is_cancelled()) {
    redirect($CFG->wwwroot . '/local/news/manage.php', 'You cancelled the create news form');
    //Handle form cancel operation, if cancel button is present on form
} else if ($fromform = $mform->get_data()) {
    //In this case you process validated data. $mform->get_data() returns data posted in form.

    $title = $fromform->title;
    $content = $fromform->content;
    $status = $fromform->status;

    $recordtoinsert = new StdClass();

    $recordtoinsert->author = 1;
    $recordtoinsert->type = 1;
    $recordtoinsert->title = $title;
    $recordtoinsert->content = $content['text'];
    $recordtoinsert->status = $status;
    $recordtoinsert->timecreated = time();
    $recordtoinsert->timemodified = time();

    $DB->insert_record('news', $recordtoinsert);

    redirect($CFG->wwwroot . '/local/news/manage.php', 'You created a news with title "' . $title . '"');
}


echo $OUTPUT->header();

$mform->display();

echo $OUTPUT->footer();
