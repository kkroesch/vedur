<?php

/**
 *  Settings for Vedur service.
 */

return array(

    'vedur_base_url' => 'http://xmlweather.vedur.is/?op_w=xml&type=obs&lang=en&view=xml&params=F;FG;D;T;P;SND;RH;TD&ids=',

    'output_file'    => "vedur.csv",

    'logfile'        => 'vedur.log',

    'csv_headers'    => explode(";", "stationid;unixtime;year;month;day;hour;minute;windspeed;gust1h;winddir;tx1h;tn1h;tl;t5cm;geo700;geo850;qfe;glob1h;sun1h;rr1h;rh;td;"),

    
);