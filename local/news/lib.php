<?php

// Every file should have GPL and copyright in the header - we skip it in tutorials but you should not skip it for real.

// This line protects the file from being accessed by a URL directly.
// We will add callbacks here as we add features to our theme.

//function local_news_before_footer() {
//    echo("hello, world!");
//}

/**
 * Get icon mapping for font-awesome.
 */
function local_news_get_fontawesome_icon_map()
{
    return [
        'mod_forum:i/pinned' => 'fa-map-pin',
        'mod_forum:t/selected' => 'fa-check',
        'mod_forum:t/subscribed' => 'fa-envelope-o',
        'mod_forum:t/unsubscribed' => 'fa-envelope-open-o',
    ];
}

function nicetime($date)
{
    if (empty($date)) {
        return "No date provided";
    }

    $periods         = array("second", "minute", "hour", "day", "week", "month", "year", "decade");
    $lengths         = array("60", "60", "24", "7", "4.35", "12", "10");

    $now             = time();
    $unix_date         = strtotime($date);

    // check validity of date
    if (empty($unix_date)) {
        return "Bad date";
    }

    // is it future date or past date
    if ($now > $unix_date) {
        $difference     = $now - $unix_date;
        $tense         = "ago";
    } else {
        $difference     = $unix_date - $now;
        $tense         = "from now";
    }

    for ($j = 0; $difference >= $lengths[$j] && $j < count($lengths) - 1; $j++) {
        $difference /= $lengths[$j];
    }

    $difference = round($difference);

    if ($difference != 1) {
        $periods[$j] .= "s";
    }

    return "$difference $periods[$j] {$tense}";
}

$date = "2009-03-04 17:45";
$result = nicetime($date); // 2 days ago
