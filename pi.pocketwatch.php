<?php

class Pocketwatch {

    public function __construct() {

    }


    public function date_offset()
    {
        // Gather parameters.
        $startDate = ee()->TMPL->fetch_param('start_date', ee()->localize->now);
        $dateInterval = ee()->TMPL->fetch_param('interval');
        $format = ee()->TMPL->fetch_param('format');

        // $dateInterval -- or rather, the interval="" parameter -- is required.
        if (! $dateInterval)
        {
            return ee()->TMPL->no_results();
        }

        // $now is our start date. It may not actually be "now".
        $now = new DateTime();
        $now->setTimestamp($startDate);

        // Apply the date interval. See https://www.php.net/manual/en/dateinterval.createfromdatestring.php
        $now->add(DateInterval::createFromDateString($dateInterval));

        // Give 'em their output.
        return ($format) ? $now->format($format) : $now->getTimestamp();
    }


// ----------------------------------------
//  Plugin Usage
// ----------------------------------------

// This function describes how the plugin is used.
//  Make sure and use output buffering

    static public function usage() {
        return;
    }
    /* END */


}
// END CLASS
?>
