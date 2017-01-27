<?php

namespace voidtek\WindguruIO;

use Http\Client\Common\Plugin;
use Http\Client\HttpClient;
use Http\Message\UriFactory;
use Http\Discovery\HttpClientDiscovery;
use Http\Discovery\MessageFactoryDiscovery;
use Psr\Http\Message\ResponseInterface;


/**
 * Class WindguruAPIClient
 *
 * @package voidtek\WindguruIO
 */
class WindguruAPI {

    /**
     * The default Windguru endpoint template.
     * Example: 'https://www.windguru.cz/int/iapi.php?q=ads_spot&id_spot%5B%5D=42'
     * 
     * @var string;
    */
    const ENDPOINT='http://www.windguru.cz';
    
    /**
     * @var HttpClient
     */
    private $httpClient;

    /**
     * @var MessageFactory
     */
    private $messageFactory;

    /**
     * @param HttpClient|null     $httpClient
     * @param MessageFactory|null $messageFactory
     */
    public function __construct(HttpClient $httpClient = null, MessageFactory $messageFactory = null)
    {
        $this->httpClient = $httpClient ?: HttpClientDiscovery::find();
        $this->messageFactory = $messageFactory ?: MessageFactoryDiscovery::find();
    }

    /**
     * Call an API endpoint.
     *
     * @param string $someArgument
     *
     * @return string
     */
    public function call($someArgument)
    {
        $request = $this->messageFactory->createRequest('GET', self::ENDPOINT.'/some_operation?argument='.$someArgument);

        try {
            $response = $this->httpClient->sendRequest($request);
        } catch (\Http\Client\Exception $e) {
            throw new \RuntimeException('Something happened during HTTP request');
        }

        return (string) $response->getBody();
    }
}