<?php namespace ch\kroesch\meteo;

require('../VedurParser.php');

class VedurParserTest extends \PHPUnit_Framework_TestCase
{

    public function testLoadObservations() {
        $parser = new VedurParser();
        $obs = $parser->get_observations(1);
        $this->assertEquals(1, intval($obs['id']));
        $this->assertGreaterThanOrEqual(700, intval($obs->P));
    }

    public function testConverter() {
        $parser = new VedurParser();
        $this->assertEquals(0.514, $parser->to_knots(1));
    }

}
