<?php
require_once(__DIR__ . '/../../config.php');

global $DB;

try {
    $PAGE->set_url(new moodle_url('/local/news/manage.php'));
} catch (coding_exception $e) {
    print_error($e);
}
$PAGE->set_context(\context_system::instance());
$PAGE->set_title('News');

$newsdata = $DB->get_records('news');

echo $OUTPUT->header();

$templatecontext = (object) [
    'newsdata' => array_values($newsdata),
    'createurl' => new moodle_url('/local/news/create.php'),
];

echo $OUTPUT->render_from_template('local_news/manage', $templatecontext);

echo $OUTPUT->footer();
