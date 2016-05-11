# Vedur

Parses Weather Observation Data From Icelandic Meteorological Office (IMO) and writes them to a CSV file.

## Requirements

+ PHP >= 5.3

## Usage

Run the `import.php` from the command line. The program writes the file `vedur.csv`; its format is described in `format.txt`.
The import script reads station identifiers from `stations.csv`.

The script runs idempotent: it prevents data from being duplicated by calculating a hash from timestamp and station identifier. So, if anything breaks during data gathering, just run again.

## Testing

To test the parser class, change to test directory and run
```
phpunit VedurParserTest.php
```
