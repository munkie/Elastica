<?php

namespace Elastica;

use Elastica\Exception\NotFoundException;

/**
 * Elastica Response object
 *
 * Stores query time, and result array -> is given to result set, returned by ...
 *
 * @category Xodoa
 * @package Elastica
 * @author Nicolas Ruflin <spam@ruflin.com>
 */
class Response
{
    /**
     * Query time
     *
     * @var float Query time
     */
    protected $_queryTime;

    /**
     * Transfer info
     *
     * @var array transfer info
     */
    protected $_transferInfo = array();

    /**
     * Response
     *
     * @var \Elastica\Response Response object
     */
    protected $_response;

    /**
     * Construct
     *
     * @param string|array $response Response string (json)
     */
    public function __construct($response)
    {
        if (is_array($response)) {
            $this->_response = $response;
        } else {
            $this->_response = $this->_parseResponse($response);
        }
    }

    /**
     * Error message
     *
     * @return string Error message
     */
    public function getError()
    {
        if ($this->hasData('error')) {
            return $this->getData('error');
        } else {
            return '';
        }
    }

    /**
     * True if response has error
     *
     * @return bool True if response has error
     */
    public function hasError()
    {
        if ($this->hasData('error')) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Checks if the query returned ok
     *
     * @return bool True if ok
     */
    public function isOk()
    {
        if ($this->hasData('ok') && $this->getData('ok')) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Get whole response as array or one field by field name
     *
     * @param string $field
     * @return array|mixed
     * @throws \Elastica\Exception\NotFoundException
     */
    public function getData($field = null)
    {
        if (null === $field) {
            return $this->_response;
        } elseif (isset($this->_response[$field])) {
            return $this->_response[$field];
        } else {
            throw new NotFoundException('Unable to find field [' . $field . '] in response');
        }
    }

    /**
     * Check if field exists in response data
     *
     * @param string $field
     * @return bool
     */
    public function hasData($field)
    {
        if (isset($this->_response[$field])) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * @param string $response
     * @return array
     */
    protected function _parseResponse($response)
    {
        if (is_string($response)) {
            $tempResponse = json_decode($response, true);
            // If error is returned, json_decode makes empty string of string
            if (!empty($tempResponse)) {
                $response = $tempResponse;
            }
        }

        if (is_string($response) && '' !== $response) {
            $response = array('message' => $response);
        } elseif (!is_array($response)) {
            $response = array();
        }

        return $response;
    }

    /**
     * Gets the transfer information if in DEBUG mode.
     *
     * @return array Information about the curl request.
     */
    public function getTransferInfo()
    {
        return $this->_transferInfo;
    }

    /**
     * Sets the transfer info of the curl request. This function is called
     * from the \Elastica\Client::_callService only in debug mode.
     *
     * @param  array             $transferInfo The curl transfer information.
     * @return \Elastica\Response Current object
     */
    public function setTransferInfo(array $transferInfo)
    {
        $this->_transferInfo = $transferInfo;

        return $this;
    }

    /**
     * This is only available if DEBUG constant is set to true
     *
     * @return float Query time
     */
    public function getQueryTime()
    {
        return $this->_queryTime;
    }

    /**
     * Sets the query time
     *
     * @param  float             $queryTime Query time
     * @return \Elastica\Response Current object
     */
    public function setQueryTime($queryTime)
    {
        $this->_queryTime = $queryTime;

        return $this;
    }

    /**
     * Time request took
     *
     * @return int Time request took
     */
    public function getEngineTime()
    {
        return $this->getData('took');
    }

    /**
     * Get the _shard statistics for the response

     * @return array
     */
    public function getShardsStatistics()
    {
        return $this->getData('_shards');
    }
}
