<?php
/**
 * File WindguruAPI.php
 * 
 * PHP Version 5
 *
 * @category PHP
 * @package  WindguruIO
 * @author   Voidtek <vdacosta@voidtek.com>
 * @license  http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * @link     https://github.com/voidtek/windguru.io
 */

namespace voidtek\WindguruIO;

use Http\Client\Common\Plugin;
use Http\Client\HttpClient;
use Http\Message\UriFactory;
use Http\Discovery\HttpClientDiscovery;
use Http\Discovery\MessageFactoryDiscovery;
use Psr\Http\Message\ResponseInterface;

/**
 * WindguruAPI Class
 *
 * @category Class
 * @package  WindguruIO
 * @author   Voidtek <vdacosta@voidtek.com>
 * @license  http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * @link     https://github.com/voidtek/windguru.io
 */
class WindguruAPI
{

    /**
     * The default Windguru endpoint template.
     * Example: 'https://www.windguru.cz/int/iapi.php?q=ads_spot&id_spot%5B%5D=42'
     * 
     * @var string;
    */
    const ENDPOINT='http://www.windguru.cz';
    
    /**
     * Private HttpClient
     * 
     * @var HttpClient
     */
    private $_httpClient;

    /**
     * Private MessageFactory
     * 
     * @var MessageFactory
     */
    private $_messageFactory;

    /**
     * Constructor WindguruAPI
     * 
     * @param HttpClient|null     $httpClient     The HttpClient parameter.
     * @param MessageFactory|null $messageFactory The messageFactory parameter.
     */
    public function __construct(
        HttpClient $httpClient = null, 
        MessageFactory $messageFactory = null
    ) {
    
        $this->_httpClient = $httpClient ?: HttpClientDiscovery::find();
        $this->_messageFactory = $messageFactory ?: MessageFactoryDiscovery::find();
    }

    /**
     * Call an API endpoint.
     *
     * @param string $someArgument The Argument for this call.
     *
     * @return string
     */
    public function call($someArgument)
    {
        $request = $this->_messageFactory
            ->createRequest(
                'GET', 
                self::ENDPOINT.'/some_operation?argument='.$someArgument
            );

        try {
            $response = $this->_httpClient->sendRequest($request);
        } catch (\Http\Client\Exception $e) {
            throw new \RuntimeException('Something happened during HTTP request');
        }

        return (string) $response->getBody();
    }
}