<?php namespace ch\kroesch\meteo;

require __DIR__ . '/vendor/autoload.php';
use InfluxDB\Client;
use InfluxDB\Database;
use InfluxDB\Point;

/**
 * Parser for weather data from the Icelandic Meteorological Office (IMO).
 *
 * See http://www.vedur.is/um-vi/vefurinn/xml/
 */
class VedurParser
{
    private $current_station_id = 40011;

    private $dataset_hashes = array();

    private $database;

    function __construct($base_url = "http://xmlweather.vedur.is/?op_w=xml&type=obs&lang=en&view=xml&params=F;FG;D;T;P;SND;RH;TD&ids=",
                         $output_file = "vedur.csv")
    {
        $this->base_url = $base_url;
        $this->output_file = $output_file;

        $headers = explode(";", "stationid;unixtime;year;month;day;hour;minute;windspeed;gust1h;winddir;tx1h;tn1h;tl;t5cm;geo700;geo850;qfe;glob1h;sun1h;rr1h;rh;td;");

        if (!file_exists($this->output_file)) {
            $fp = fopen($this->output_file, 'w');
            fputcsv($fp, $headers);
            fclose($fp);
        } else {
            // Read file and fill hash table
            $fp = fopen($this->output_file, 'r');
            while (($row = fgetcsv($fp, 1000, ';')) !== FALSE) {
                $key = $row[0] . ';' . $row[1];
                array_push($this->dataset_hashes, $this->hash_djb2($key));
            }
        }

        $db_client = new Client('localhost');
        $this->database = $db_client->selectDB('vedur');
    }

    /**
     * Retrieve the observations for only one station.
     *
     * @param $station_id int Station ID as defined by IMO.
     * @return mixed
     */
    public function get_observations($station_id)
    {
        $obs = simplexml_load_file($this->base_url . $station_id);
        return $obs->station[0];
    }

    /**
     * Write observation data in normalized form.
     *
     * @param $internal_id
     * @param $obs
     */
    public function write_csv($internal_id, $obs)
    {
        $time = \DateTime::createFromFormat('Y-m-d H:i:s', $obs->time);

        // Check if dataset already stored.
        $key = $this->hash_djb2($internal_id . ';' . $time->format('U'));
        if (in_array($key, $this->dataset_hashes)) {
            echo "Already stored. Aborting.";
            return;
        }

        // Normalize data
        $content = array(
            $internal_id,
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
        );

        $fp = fopen($this->output_file, 'a');
        fputcsv($fp, $content, $delimiter = ';');
        fclose($fp);
    }

    /**
     * Writes the observation data to time series database.
     *
     * @param $id int Station ID used internally.
     * @param $obs mixed Observation data structure.
     */
    public function write_db($id, $obs)
    {
        $time = \DateTime::createFromFormat('Y-m-d H:i:s', $obs->time);
        $origin_id = intval($obs->id);

        $points = array(
            new Point(
                'windspeed',
                floatval($this->to_knots($obs->F)),
                array('id' => $id, 'origin_id' => $origin_id), array(),
                intval($time->format('U'))
            ),
            new Point(
                'tl',
                floatval($obs->T),
                array('id' => $id, 'origin_id' => $origin_id), array(),
                intval($time->format('U'))
            ),
            new Point(
                'qfe',
                floatval($obs->P),
                array('id' => $id, 'origin_id' => $origin_id), array(),
                intval($time->format('U'))
            ),
            new Point(
                'td',
                floatval($obs->TD),
                array('id' => $id, 'origin_id' => $origin_id), array(),
                intval($time->format('U'))
            )
        );

        // we are writing unix timestamps, which have a second precision
        $newPoints = $this->database->writePoints($points, Database::PRECISION_SECONDS);
    }

    /**
     * Calculates the next station ID for internal use.
     *
     * @return int
     */
    public function next_station_id()
    {
        $this->current_station_id += 1;
        if ($this->current_station_id % 10 == 0)
            $this->current_station_id += 1;

        return $this->current_station_id;
    }

    /**
     * Calculate hash for data deduplication.
     *
     * @param $str
     * @return int
     */
    public function hash_djb2($str)
    {
        $hash = 5381;
        $length = strlen($str);
        for ($i = 0; $i < $length; $i++) {
            $hash = (($hash << 5) + $hash) + $str[$i];
        }
        return ($hash & 0xFFFFFFFF);
    }

    /**
     * Calculate speed from m/s to knots.
     *
     * @param $m_per_sec float Speed in SI units
     * @return float Speed in knots
     */
    public function to_knots($m_per_sec)
    {
        return round($m_per_sec / 0.514444444444, 1);
    }
}
