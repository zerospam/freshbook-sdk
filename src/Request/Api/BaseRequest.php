<?php
/**
 * Created by PhpStorm.
 * User: aaflalo
 * Date: 30/05/18
 * Time: 4:35 PM
 */

namespace ZEROSPAM\Freshbooks\Request\Api;


use GuzzleHttp\RequestOptions;
use Psr\Http\Message\UriInterface;
use ZEROSPAM\Freshbooks\Request\Arguments\IArgument;
use ZEROSPAM\Freshbooks\Request\Arguments\Mergeable\ArgMerger;
use ZEROSPAM\Freshbooks\Request\Arguments\Mergeable\IMergeableArgument;
use ZEROSPAM\Freshbooks\Request\Type\RequestType;
use ZEROSPAM\Freshbooks\Response\IResponse;
use ZEROSPAM\Freshbooks\Utils\Reflection\ReflectionUtil;

abstract class BaseRequest implements IRequest
{

    /**
     * @var IArgument[]
     */
    private $arguments = [];

    /**
     * @var ArgMerger[]
     */
    private $mergeableArguments = [];


    /**
     * @var IResponse
     */
    private $response;

    /** @var int Number of tries of this request */
    private $tries = 0;

    /**
     * @var RequestType
     */
    private $requestTypeOverride;

    /**
     * Add a request argument
     *
     * @param IArgument $arg
     *
     * @return $this
     */
    public function addArgument(IArgument $arg): IRequest
    {
        if ($arg instanceof IMergeableArgument) {
            if (!isset($this->mergeableArguments[$arg->getKey()])) {
                $this->mergeableArguments[$arg->getKey()] = new ArgMerger();
            }

            $this->mergeableArguments[$arg->getKey()]->addArgument($arg);

            return $this;
        }

        $this->arguments[$arg->getKey()] = $arg;

        return $this;
    }

    /**
     * Remove a request argument
     *
     * @param IArgument $arg
     *
     * @return $this
     */
    public function removeArgument(IArgument $arg): IRequest
    {
        if ($arg instanceof IMergeableArgument) {
            if (!isset($this->mergeableArguments[$arg->getKey()])) {
                throw new \InvalidArgumentException("This argument doesn't exists");
            }
            $this->mergeableArguments[$arg->getKey()]->removeArgument($arg);

            if ($this->mergeableArguments[$arg->getKey()]->isEmpty()) {
                unset($this->mergeableArguments[$arg->getKey()]);
            }
        } else {
            unset($this->arguments[$arg->getKey()]);
        }

        return $this;
    }

    /**
     * Get all the request arguments
     *
     * @return IArgument[]
     */
    public function getArguments(): array
    {
        return $this->arguments;
    }


    /**
     * Get the data used for the route
     *
     * @return array
     * @throws \ReflectionException
     */
    public function toArray(): array
    {
        return ReflectionUtil::objToSnakeArray($this, $this->blacklistedProperties());
    }

    /**
     * List of properties to not serialize
     *
     * @return array
     */
    protected function blacklistedProperties()
    {
        static $blacklist = [
            'arguments',
            'response',
            'mergeableArguments',
            'reflectionProperties',
            'tries',
            'requestTypeOverride',
        ];

        return $blacklist;
    }

    /**
     * Return the URI of the request
     *
     * @return UriInterface
     */
    public function toUri(): UriInterface
    {
        return \GuzzleHttp\Psr7\uri_for($this->routeUrl());
    }

    /**
     * Request type to use for the request
     *
     * @return RequestType
     */
    final public function requestType(): RequestType
    {
        return $this->requestTypeOverride ?: $this->httpType();
    }


    /**
     * Options for this request to be used by the client
     *
     * @return array
     * @throws \ReflectionException
     */
    public function requestOptions()
    {
        $query = [];
        /**
         * @var $value IArgument
         */
        foreach ($this->arguments as $key => $value) {
            $query[$key] = $value->toPrimitive();
        }

        foreach ($this->mergeableArguments as $key => $value) {
            $query[$key] = $value->toPrimitive();
        }


        $options                       = [
            RequestOptions::QUERY => $query,
        ];
        $requestContent                = $this->toArray();
        $options[RequestOptions::JSON] = $requestContent;


        return $options;
    }


    /**
     * Getter for response
     *
     * @return IResponse
     */
    public function getResponse(): IResponse
    {
        return $this->response;
    }

    /**
     * @param  IResponse $response
     */
    public function setResponse(IResponse $response): void
    {
        $this->response = $response;
    }

    /**
     * Number of tries of the request
     *
     * @return int
     */
    public function tries()
    {
        return $this->tries;
    }

    /**
     * Increment the number of tries and returns it
     *
     * @return int
     */
    public function incrementTries()
    {
        return ++$this->tries;
    }
}