<?php

if (!function_exists('subtractPercentage')) {

    /**
     * Отнимает процент от числа.
     *
     * @param  float|int  $number
     * @param  float|int  $percent
     *
     * @return float
     */

    function subtractPercentage(float|int $number, float|int $percent): float {
        $percentValue = ($percent / 100) * $number;
        return round($number - $percentValue, 2);
    }

}