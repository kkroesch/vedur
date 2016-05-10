<?php namespace ch\kroesch\meteo;

/**
 * Parser for weather data from the Icelandic Meteorological Office (IMO).
 *
 * See http://www.vedur.is/um-vi/vefurinn/xml/
 *
 * User: karsten
 */


class VedurParser
{
    private $current_station_id = 40011;

    private $headers = array();

    function __construct($base_url = "http://xmlweather.vedur.is/?op_w=xml&type=obs&lang=en&view=xml&params=F;FG;D;T;P;SND;RH;TD&ids=",
                         $output_file = "vedur.csv")
    {
        $this->base_url = $base_url;
        $this->output_file = $output_file;

        $this->headers = explode(";", "stationid;unixtime;year;month;day;hour;minute;windspeed;gust1h;winddir;tx1h;tn1h;tl;t5cm;geo700;geo850;qfe;glob1h;sun1h;rr1h;rh;td;");
    }

    public function get_observations($station_id) {
        $obs = simplexml_load_file($this->base_url . $station_id);
        return $obs->station[0];
    }

    public function write_csv($obs) {
        $time = \DateTime::createFromFormat( 'Y-m-d H:i:s', $obs->time);

        $content = array(
          $this->headers, array(
                $obs['id'],
                // Observation date and time:
                $time->format('U'), $time->format('Y'), $time->format('m'), $time->format('d'),
                $time->format('H'), $time->format('i'), $time->format('s'),
                // Wind:
                $this->to_knots($obs->F), $this->to_knots($obs->FG), $obs->D,
                // Temperature
                null, null, $obs->T, null,
                // Pressure
                null, null, $obs->P,
                // Radiation
                null, null,
                // Precipitation
                null, // -> Maybe $obs->SND, they only have values for snow height?
                // Humidity
                $obs->RH, $obs->TD
            )
        );

        $fp = fopen($this->output_file, 'w');
        foreach ($content as $line) {
            fputcsv($fp, $line, $delimiter = ';');
        }
        fclose($fp);
    }

    /**
     * Calculates the next station ID for internal use.
     *
     * @return int
     */
    public function next_station_id() {
        $this->current_station_id += 1;
        if ($this->current_station_id % 10 == 0)
            $this->current_station_id += 1;

        return $this->current_station_id;
    }

    /**
     * Calculate speed from m/s to knots.
     *
     * @param $m_per_sec
     * @return float Speed in knots
     */
    public function to_knots($m_per_sec) {
        return round($m_per_sec / 0.514444444444, 1);
    }
}
