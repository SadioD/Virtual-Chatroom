<?php
if (!defined('BASEPATH')) exit('No direct script access allowed');
class Time
{
	// Permet d'obtenir letemps de pubication sous form - Posted about 3 days ago
	public function getTimeago( $ptime )
    {
        $estimate_time = time() - $ptime;
        if( $estimate_time < 1 )
        {
            return 'less than 1 second ago';
        }
        $condition = array(
                12 * 30 * 24 * 60 * 60  =>  'year',
                30 * 24 * 60 * 60       =>  'month',
                24 * 60 * 60            =>  'day',
                60 * 60                 =>  'hour',
                60                      =>  'minute',
                1                       =>  'second');
        foreach( $condition as $secs => $str )
        {
            $d = $estimate_time / $secs;
            if( $d >= 1 )
            {
                $r = round( $d );
                return 'about ' . $r . ' ' . $str . ( $r > 1 ? 's' : '' ) . ' ago';
            }
        }
    }
}
