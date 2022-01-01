<?php
require_once(__DIR__.'/../../config.php');
try {
    $PAGE->set_url(new moodle_url('/local/schedule/manage.php'));
} catch (coding_exception $e) {
    print_error($e);
}
$PAGE->set_context(\context_system::instance());
$PAGE->set_title('Schedule');

echo $OUTPUT->header();

$templatecontext= (object) [
    'texttodisplay'=>'Text to display'
];

echo $OUTPUT->render_from_template('local_schedule/manage', $templatecontext);

echo $OUTPUT->footer();