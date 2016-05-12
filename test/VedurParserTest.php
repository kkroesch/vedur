<?php namespace ch\kroesch\meteo;

require('../VedurParser.php');

class VedurParserTest extends \PHPUnit_Framework_TestCase
{
    private $parser;

    public function setUp() {
        $this->parser = new VedurParser($base_url='fixture.xml');
    }

    public function testLoadObservations() {
        $obs = $this->parser->get_observations('');
        $this->assertEquals(1, intval($obs['id']));
        $this->assertGreaterThanOrEqual(700, intval($obs->P));
    }

    public function testConverter() {
        $this->assertEquals(1.9, $this->parser->to_knots(1));
        $this->assertEquals(7.8, $this->parser->to_knots(4));
    }

    public function testHash() {
        $this->assertEquals(3725084672, $this->parser->hash_djb2('4422;1462892400'));
        $this->assertNotEquals(
            $this->parser->hash_djb2('4001;1463040000'),
            $this->parser->hash_djb2('4001;1463640000')
        );
    }
}
