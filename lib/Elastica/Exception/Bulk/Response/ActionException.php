<?php

namespace Elastica\Exception\Bulk\Response;

use Elastica\Exception\BulkException;
use Elastica\Bulk\Action;
use Elastica\Bulk\Response;

class ActionException extends BulkException
{
    /**
     * @var \Elastica\Response
     */
    protected $_response;

    /**
     * @param \Elastica\Bulk\Response $response
     */
    public function __construct(Response $response)
    {
        $this->_response = $response;

        parent::__construct($this->getErrorMessage($response));
    }

    /**
     * @return \Elastica\Bulk\Action
     */
    public function getAction()
    {
        return $this->getResponse()->getAction();
    }

    /**
     * @return \Elastica\Bulk\Response
     */
    public function getResponse()
    {
        return $this->_response;
    }

    /**
     * @param \Elastica\Bulk\Response $response
     * @return string
     */
    public function getErrorMessage(Response $response)
    {
        $error = $response->getError();
        $opType = $response->getOpType();

        $path = '';
        if ($response->hasData('_index')) {
            $path.= '/' . $response->getData('_index');
        }
        if ($response->hasData('_type')) {
            $path.= '/' . $response->getData('_type');
        }
        if ($response->hasData('_id')) {
            $path.= '/' . $response->getData('_id');
        }
        $message = "$opType: $path caused $error";

        return $message;
    }
}
