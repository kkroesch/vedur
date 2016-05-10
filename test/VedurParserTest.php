<?php namespace ch\kroesch\meteo;

require('../VedurParser.php');

class VedurParserTest extends \PHPUnit_Framework_TestCase
{
    private $parser;

    public function setUp() {
        $this->parser = new VedurParser();
    }

    public function testLoadObservations() {
        $obs = $this->parser->get_observations(1);
        $this->assertEquals(1, intval($obs['id']));
        $this->assertGreaterThanOrEqual(700, intval($obs->P));
    }

    public function testConverter() {
        $this->assertEquals(0.514, $this->parser->to_knots(1));
    }

}
