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

    public function date_is_today()
    {
        // Date is required.
        $date = ee()->TMPL->fetch_param('date');
        if (! $date)
        {
            return $this->noResults();
        }

        // Default input format is Ymd.
        $format = ee()->TMPL->fetch_param('format', 'Ymd');
        // The string "timestamp" is a special case.
        if (strtolower($format) == 'timestamp')
        {
            $format = 'U';
        }

        $now = ee()->localize->format_date("%Y%m%d");
        if ($format == 'Ymd')
        {
            $then = $date;
        }
        else
        {
            try {
                $then = DateTime::createFromFormat($format, $date);
                // If $then === false, creating the date failed.
                if ($then === false)
                {
                    return $this->noResults();
                }

                $then = $then->format('Ymd');
            } catch (Exception $e) {
                return $this->noResults();
            }
        }

        // If we have a tag pair, output the innards or no_results content, as appropriate.
        if (ee()->TMPL->tagdata)
        {
            return ($now == $then) ? ee()->TMPL->tagdata : ee()->TMPL->no_results();
        }
        // Otherwise output true/false.
        else
        {
            return ($now == $then);
        }
    }

    public function date_is_this_week()
    {

    }

    public function date_is_last_week()
    {

    }

    public function date_is_next_week()
    {

    }

    public function date_is_this_month()
    {

    }

    public function date_is_this_year()
    {

    }

    private function log($message)
    {
        ee()->load->library('logger');

        // Only log once per day.
        ee()->logger->developer($message, false, 86400);
    }

    private function noResults($noTagDataOutput = false)
    {
        return (ee()->TMPL->tagdata) ? ee()->TMPL->no_results() : $noTagDataOutput;
    }

    // ----------------------------------------
    //  Plugin Usage
    // ----------------------------------------

    // This function describes how the plugin is used.
    //  Make sure and use output buffering.

    static public function usage() {
        return;
    }
    /* END */


}
// END CLASS
?>
