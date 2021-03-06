<?php
require_once 'bootstrap.php';

/**
 * Test class for YouTube.
 * Generated by PHPUnit on 2011-02-01 at 21:15:31.
 */
class YouTubeTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var YouTube
     */
    protected $object;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        $this->object = new YouTube;
        $this->xml = file_get_contents(PATH_TEST_DATA . '/youtube.xml');
        $this->response = $this->object->getParsedSearchResponse($this->xml);
    }

    /**
     * testGetParsedResponse().
     */
    public function testGetParsedResponse()
    {
        $this->assertTrue(is_array($this->response));
    }

    /**
     * testGetParsedResponseHasItems().
     */
    public function testGetParsedResponseHasItems()
    {
        $this->assertArrayHasKey('items', $this->response);
    }
}