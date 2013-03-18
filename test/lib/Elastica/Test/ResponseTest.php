<?php

namespace Elastica\Test;

use Elastica\Document;
use Elastica\Exception\NotFoundException;
use Elastica\Query;
use Elastica\Query\MatchAll;
use Elastica\Response;
use Elastica\Type\Mapping;
use Elastica\Test\Base as BaseTest;

class ResponseTest extends BaseTest
{
    public function testResponse()
    {
        $index = $this->_createIndex();
        $type = $index->getType('helloworld');

        $mapping = new Mapping($type, array(
                'name' => array('type' => 'string', 'store' => 'no'),
                'dtmPosted' => array('type' => 'date', 'store' => 'no', 'format' => 'yyyy-MM-dd HH:mm:ss')
            ));
        $type->setMapping($mapping);

        $doc = new Document(1, array('name' => 'nicolas ruflin', 'dtmPosted' => "2011-06-23 21:53:00"));
        $type->addDocument($doc);
        $doc = new Document(2, array('name' => 'raul martinez jr', 'dtmPosted' => "2011-06-23 09:53:00"));
        $type->addDocument($doc);
        $doc = new Document(3, array('name' => 'rachelle clemente', 'dtmPosted' => "2011-07-08 08:53:00"));
        $type->addDocument($doc);
        $doc = new Document(4, array('name' => 'elastica search', 'dtmPosted' => "2011-07-08 01:53:00"));
        $type->addDocument($doc);

        $query = new Query();
        $query->setQuery(new MatchAll());
        $index->refresh();

        $resultSet = $type->search($query);

        $response = $resultSet->getResponse();

        $engineTime = $response->getEngineTime();
        $shardsStats = $response->getShardsStatistics();

        $this->assertInternalType('int', $engineTime);
        $this->assertTrue(is_array($shardsStats));
        $this->assertArrayHasKey('total', $shardsStats);
        $this->assertArrayHasKey('successful', $shardsStats);

        $this->assertTrue($response->hasData('took'));
        $this->assertInternalType('int', $response->getData('took'));
        $this->assertTrue($response->hasData('_shards'));
        $this->assertInternalType('array', $response->getData('_shards'));
        $this->assertTrue($response->hasData('hits'));
        $this->assertInternalType('array', $response->getData('hits'));

        $this->assertFalse($response->hasData('invalid'));

        try {
            $response->getData('invalid');
            $this->fail('Invalid data get should fail');
        } catch (NotFoundException $e) {
            $this->assertContains('Unable to find field', $e->getMessage());
        }
    }

    public function testIsOk()
    {
        $index = $this->_createIndex();
        $type = $index->getType('test');

        $doc = new Document(1, array('name' => 'ruflin'));
        $response = $type->addDocument($doc);

        $this->assertTrue($response->isOk());
    }

    public function testIsOkMultiple()
    {
        $index = $this->_createIndex();
        $type = $index->getType('test');

        $docs = array(
            new Document(1, array('name' => 'ruflin')),
            new Document(2, array('name' => 'ruflin'))
        );
        $response = $type->addDocuments($docs);

        $this->assertTrue($response->isOk());
    }

    public function testConstructRawResponseDataJson()
    {
        $rawData = '{"error":"error message"}';
        $response = new Response($rawData);

        $this->assertTrue($response->hasError());
        $this->assertEquals('error message', $response->getError());

        $this->assertFalse($response->isOk());

        try {
            $response->getEngineTime();
            $this->fail('getEngineTime should fail');
        } catch (NotFoundException $e) {
            $this->assertEquals("Unable to find field [took] in response", $e->getMessage());
        }

        try {
            $response->getShardsStatistics();
            $this->fail('getShardsStatistics should fail');
        } catch (NotFoundException $e) {
            $this->assertEquals("Unable to find field [_shards] in response", $e->getMessage());
        }
    }

    public function testConstructRawResponseDataEmpty()
    {
        $rawData = '';
        $response = new Response($rawData);

        $this->assertFalse($response->isOk());
        $this->assertEquals(array(), $response->getData());
    }

    public function testConstructRawResponseDataString()
    {
        $rawData = 'string';
        $response = new Response($rawData);

        $this->assertFalse($response->isOk());

        $this->assertFalse($response->hasError());
        $this->assertEquals('', $response->getError());

        $this->assertTrue($response->hasData('message'));
        $this->assertEquals('string', $response->getData('message'));
    }

    public function testQueryTime()
    {
        $queryTime = 2.3;

        $response = new Response(array('ok' => 1));

        $response->setQueryTime($queryTime);
        $this->assertEquals($queryTime, $response->getQueryTime());
    }

    public function testTransferInfo()
    {
        $transferInfo = array('status' => 'ok');

        $response = new Response(array('ok' => 1));
        $response->setTransferInfo($transferInfo);
        $this->assertEquals($transferInfo, $response->getTransferInfo());
    }
}
