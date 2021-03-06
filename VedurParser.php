<?php namespace ch\kroesch\meteo;

require __DIR__ . '/vendor/autoload.php';
$config = include __DIR__ . '/config.php';

use Monolog\Logger;
use Monolog\Handler\StreamHandler;


/**
 * Parser for weather data from the Icelandic Meteorological Office (IMO).
 *
 * See http://www.vedur.is/um-vi/vefurinn/xml/
 */
class VedurParser
{
    private $wind_directions = array(
        'N'   => 0,
        'NNE' => 22.5,
        'NE'  => 45,
        'ENE' => 67.5,
        'E'   => 90,
        'ESE' => 112.5,
        'SE'  => 135,
        'SSE' => 157.5,
        'S'   => 180,
        'SSW' => 202.5,
        'SW'  => 225,
        'WSW' => 247.5,
        'W'   => 270,
        'WNW' => 292.5,
        'NW'  => 315,
        'NNW' => 337.5
    );

    private $current_station_id = 40011;

    private $dataset_hashes = array();

    private $logger;

    function __construct($base_url = "http://xmlweather.vedur.is/?op_w=xml&type=obs&lang=en&view=xml&params=F;FG;D;T;P;SND;RH;TD&ids=",
                         $output_file = "vedur.csv")
    {
        $this->base_url = $base_url;
        $this->output_file = $output_file;

        $headers = explode(";", "stationid;unixtime;year;month;day;hour;minute;windspeed;gust1h;winddir;tx1h;tn1h;tl;t5cm;geo700;geo850;qfe;glob1h;sun1h;rr1h;rh;td;");

        if (!file_exists($this->output_file)) {
            $fp = fopen($this->output_file, 'w');
            fputcsv($fp, $headers, $delimiter=';');
            fclose($fp);
        } else {
            // Read file and fill hash table
            $fp = fopen($this->output_file, 'r');
            while (($row = fgetcsv($fp, 1000, ';')) !== FALSE) {
                $key = $row[0] . ';' . $row[1];
                array_push($this->dataset_hashes, $key);
            }
        }

        date_default_timezone_set('UTC');

        $this->logger = new Logger('vedur');
        $this->logger->pushHandler(new StreamHandler('vedur.log', Logger::INFO));
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

        $this->logger->info("Storing observations from " . $time->format('Y-m-d H:i:s'));

        // Check if dataset already stored.
        $key = $internal_id . ';' . $time->format('U');
        if (in_array($key, $this->dataset_hashes)) {
            $this->logger->warn("Already stored. Aborting.");
            return;
        }

        // Normalize data
        $content = array(
            $internal_id,
            // Observation date and time:
            $time->format('U'), $time->format('Y'), $time->format('m'), $time->format('d'),
            $time->format('H'), $time->format('i'), $time->format('s'),
            // Wind:
            $this->to_knots($obs->F), $this->to_knots($obs->FG), $this->wind_directions[trim($obs->D)],
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
