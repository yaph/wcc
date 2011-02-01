<?php
require_once 'PHPUnit/Framework.php';

require_once 'bootstrap.php';

/**
 * Test class for SPARQL.
 * Generated by PHPUnit on 2011-02-01 at 21:15:26.
 */
class SPARQLTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var SPARQL
     */
    protected $object;

    /**
     * @var string
     */
    protected $xml;

    /**
     * @var array
     */
    protected $response;
    
    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        $this->object = new SPARQL;
        $this->xml = file_get_contents(PATH_TEST_DATA . '/sparql.xml');
        $this->response = $this->object->getParsedResponse($this->xml);
    }

    /**
     * testGetParsedResponse().
     */
    public function testGetParsedResponse()
    {
        $this->assertType('array', $this->response);
    }
    
    /**
     * testGetParsedResponseHasLabels().
     */
    public function testGetParsedResponseHasLabels()
    {
        $this->assertArrayHasKey('labels', $this->response);
    }

    /**
     * testGetParsedResponseHasResults().
     */
    public function testGetParsedResponseHasResults()
    {
        $this->assertArrayHasKey('results', $this->response);
    }
}