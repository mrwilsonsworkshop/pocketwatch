<?php

class Pocketwatch {

    private $variables = [];
    private $variablesRow = 0;

    public function __construct()
    {

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

        // If we have a tag pair, output the innards or no_results content, as appropriate.
        if (ee()->TMPL->tagdata)
        {
            $this->addVariable('date', $now->getTimestamp());
            return $this->getParsedTagdata();
        }
        // Otherwise output true/false.
        else
        {
            return ($format) ? $now->format($format) : $now->getTimestamp();
        }
    }

    public function date_is_today()
    {
        $now = ee()->localize->format_date("%Y%m%d");
        $then = $this->getDateTimeFromTagParameters('date', 'format');

        return ($then === false) ? ee()->TMPL->no_results() : $this->getDateMatchOutput($now, $then->format('Ymd'));
    }

    public function date_is_this_week()
    {
        $now = ee()->localize->format_date("%o%W");
        $then = $this->getDateTimeFromTagParameters('date', 'format');

        return ($then === false) ? ee()->TMPL->no_results() : $this->isDateThisWeek($now, $then);
    }

    public function date_is_last_week()
    {
        $now = new DateTime();
        $now = $now->setTimestamp(ee()->localize->now)->sub(DateInterval::createFromDateString('7 days'))->format('oW');

        $then = $this->getDateTimeFromTagParameters('date', 'format');

        return ($then === false) ? ee()->TMPL->no_results() : $this->isDateThisWeek($now, $then);
    }

    public function date_is_next_week()
    {
        $now = new DateTime();
        $now = $now->setTimestamp(ee()->localize->now)->add(DateInterval::createFromDateString('7 days'))->format('oW');

        $then = $this->getDateTimeFromTagParameters('date', 'format');

        return ($then === false) ? ee()->TMPL->no_results() : $this->isDateThisWeek($now, $then);
    }

    public function date_is_this_month()
    {
        $now = ee()->localize->format_date("%Y%m");
        $then = $this->getDateTimeFromTagParameters('date', 'format');

        return ($then === false) ? ee()->TMPL->no_results() : $this->getDateMatchOutput($now, $then->format('Ym'));
    }

    public function date_is_last_month()
    {
        $now = new DateTime();
        $now = $now->setTimestamp(ee()->localize->now)->sub(DateInterval::createFromDateString('1 month'))->format('Ym');
        $then = $this->getDateTimeFromTagParameters('date', 'format');

        return ($then === false) ? ee()->TMPL->no_results() : $this->getDateMatchOutput($now, $then->format('Ym'));
    }

    public function date_is_next_month()
    {
        $now = new DateTime();
        $now = $now->setTimestamp(ee()->localize->now)->add(DateInterval::createFromDateString('1 month'))->format('Ym');
        $then = $this->getDateTimeFromTagParameters('date', 'format');

        return ($then === false) ? ee()->TMPL->no_results() : $this->getDateMatchOutput($now, $then->format('Ym'));
    }

    public function date_is_this_year()
    {
        $now = ee()->localize->format_date("%Y");
        $then = $this->getDateTimeFromTagParameters('date', 'format');

        return ($then === false) ? ee()->TMPL->no_results() : $this->getDateMatchOutput($now, $then->format('Y'));
    }

    public function date_is_last_year()
    {
        $now = new DateTime();
        $now = $now->setTimestamp(ee()->localize->now)->sub(DateInterval::createFromDateString('1 year'))->format('Y');
        $then = $this->getDateTimeFromTagParameters('date', 'format');

        return ($then === false) ? ee()->TMPL->no_results() : $this->getDateMatchOutput($now, $then->format('Y'));
    }

    public function date_is_next_year()
    {
        $now = new DateTime();
        $now = $now->setTimestamp(ee()->localize->now)->add(DateInterval::createFromDateString('1 year'))->format('Y');
        $then = $this->getDateTimeFromTagParameters('date', 'format');

        return ($then === false) ? ee()->TMPL->no_results() : $this->getDateMatchOutput($now, $then->format('Y'));
    }

    private function getDateTimeFromTagParameters($dateParamName = 'date', $formatParamName = 'format')
    {
        // Date is required.
        $date = ee()->TMPL->fetch_param($dateParamName);
        if (! $date)
        {
            return false;
        }

        // Default input format is Ymd.
        $format = ee()->TMPL->fetch_param($formatParamName, 'Ymd');
        // The string "timestamp" is a special case.
        if (strtolower($format) == 'timestamp')
        {
            $format = 'U';
        }

        try {
            $date = DateTime::createFromFormat($format, $date);
        } catch (Exception $e) {
            return false;
        }

        return $date;
    }

    private function isDateThisWeek($now, $then)
    {
        // If $then is false, something went wrong while getting the datetime object.
        if ($then === false)
        {
            return ee()->TMPL->no_results();
        }

        // Prep our comparison. We use the ISO-8601 definition of week number, which begins on Monday.
        // ...Unless the user wants the week to start on Sunday. Then we just need to add one day.
        if (strtolower(ee()->TMPL->fetch_param('week_start_day')) == 'sunday')
        {
            $then = $then->add(DateInterval::createFromDateString('1 day'))->format('oW');
        }
        else
        {
            $then = $then->format('oW');
        }

        return $this->getDateMatchOutput($now, $then);
    }

    private function getDateMatchOutput($now, $then)
    {
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

    private function addVariable($name, $value)
    {
        $this->variables[$this->variablesRow]['pocketwatch:'.$name] = $value;
    }

    private function addVariables($variables)
    {
        foreach ($variables as $name => $value)
        {
            $this->addVariable($name, $value);
        }
    }

    private function newVariablesRow()
    {
        $this->variablesRow++;
    }

    private function getParsedTagdata($tagdata = false)
    {
        $tagdata = $tagdata ?: ee()->TMPL->tagdata;
        return ee()->TMPL->parse_variables($tagdata, $this->variables);
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
