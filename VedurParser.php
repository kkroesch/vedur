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

    function __construct($base_url = "http://xmlweather.vedur.is/?op_w=xml&type=obs&lang=en&view=xml&params=F;FG;T;P;RH;TD&ids=",
                         $output_file = "vedur.csv")
    {
        $this->base_url = $base_url;
        $this->output_file = $output_file;
    }

    public function get_observations($station_id) {
        $obs = simplexml_load_file($this->base_url . $station_id);
        return $obs->station[0];
    }

    public function get_forecasts($station_id) {

    }

    public function write_csv($station_id) {
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
        return $m_per_sec * 0.514;
    }
}

