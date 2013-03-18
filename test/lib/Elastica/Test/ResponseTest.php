<?php

namespace Elastica\Test;

use Elastica\Document;
use Elastica\Exception\NotFoundException;
use Elastica\Facet\DateHistogram;
use Elastica\Query;
use Elastica\Query\MatchAll;
use Elastica\Type\Mapping;
use Elastica\Test\Base as BaseTest;

class ResponseTest extends BaseTest
{
    public function testClassHierarchy()
    {
        $facet = new DateHistogram('dateHist1');
        $this->assertInstanceOf('Elastica\Facet\Histogram', $facet);
        $this->assertInstanceOf('Elastica\Facet\AbstractFacet', $facet);
        unset($facet);
    }

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
}
