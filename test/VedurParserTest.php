<?php namespace ch\kroesch\meteo;

require('../VedurParser.php');

class VedurParserTest extends \PHPUnit_Framework_TestCase
{
    private $parser;

    public function setUp() {
        $this->parser = new VedurParser($base_url='fixture.xml');
    }

    public function testLoadObservations() {
        $obs = $this->parser->get_observations(1);
        $this->assertEquals(1, intval($obs['id']));
        $this->assertGreaterThanOrEqual(700, intval($obs->P));
    }

    public function testConverter() {
        $this->assertEquals(1.9, $this->parser->to_knots(1));
        $this->assertEquals(7.8, $this->parser->to_knots(4));
        
    }



}
