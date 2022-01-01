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
function local_news_get_fontawesome_icon_map() {
    return [
        'mod_forum:i/pinned' => 'fa-map-pin',
        'mod_forum:t/selected' => 'fa-check',
        'mod_forum:t/subscribed' => 'fa-envelope-o',
        'mod_forum:t/unsubscribed' => 'fa-envelope-open-o',
    ];
}