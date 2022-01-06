<?php
require_once(__DIR__ . '/../../config.php');
require_once(dirname(__FILE__) . '/lib.php');

require_login(null, false);

if (isguestuser()) {
    redirect($CFG->wwwroot);
}



try {
    $PAGE->set_url(new moodle_url('/local/news/manage.php'));
} catch (coding_exception $e) {
    print_error($e);
}
$PAGE->set_context(\context_system::instance());
$PAGE->set_title('News');

$canwrite = false;

if (is_siteadmin($USER->id))
    $canwrite = true;

$newsdata = local_news_get_all_news();



echo $OUTPUT->header();

$templatecontext = (object) [
    'newsdata' => array_values($newsdata),
    'createurl' => new moodle_url('/local/news/create.php'),
    'user' => $USER,
    'canwrite' => $canwrite,
];

echo $OUTPUT->render_from_template('local_news/manage', $templatecontext);

echo $OUTPUT->footer();
