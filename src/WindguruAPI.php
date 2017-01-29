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

use Http\Discovery\HttpClientDiscovery;
use Http\Discovery\MessageFactoryDiscovery;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

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
    const ENDPOINT='https://www.windguru.cz';
    const LOGFOLDER='logs';
    const CACHEFOLDER='cache';
    const CACHETIME=10*60;   // secondes

    /**
     * Private Id of the Spot
     *
     * @var string
     */
    private $_idSpot;

    /**
     * Private data of the Spot
     *
     * @var SimpleXMLElement
     */
    private $_data;

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
     * Private MessageFactory
     *
     * @var MessageFactory
     */
    private $_log;

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
        date_default_timezone_set('UTC');

        $this->_httpClient = $httpClient ?: HttpClientDiscovery::find();
        $this->_messageFactory = $messageFactory ?: MessageFactoryDiscovery::find();

        if(!file_exists(self::CACHEFOLDER)) {
          mkdir(self::CACHEFOLDER, 0777, true);
        }

        if(!file_exists(self::LOGFOLDER)) {
          mkdir(self::LOGFOLDER, 0777, true);
        }

        $this->_log = new Logger('WindguruAPI');
        $this->_log->pushHandler(new StreamHandler(self::LOGFOLDER . '/' . date('Ymd') . '.log', Logger::WARNING));
    }

    /**
     * Set the Id of the Spot.
     *
     * @param string $someArgument The Argument for this call.
     */
    public function setSpot($idSpot)
    {
        $this->_idSpot = $idSpot;
    }

    /**
     * Get the Id of the Spot.
     *
     * @return string
     */
    public function getSpot()
    {
        return $this->_idSpot;
    }

    /**
     * Set Data from Online source.
     *
     * @return DOMDocument
     */
    public function getData()
    {
      if(file_exists(self::CACHEFOLDER.'/'.$this->_idSpot)) {
        $this->getCacheData();
      } else {
        $this->getOnlineData();
      }
    }

    /**
     * Set Data from Online source.
     *
     * @return DOMDocument
     */
    public function getOnlineData()
    {
        $request = $this->_messageFactory
            ->createRequest(
                'GET',
                self::ENDPOINT.'/'.$this->_idSpot
            );

        try {
            $response = $this->_httpClient->sendRequest($request);
        } catch (\Http\Client\Exception $e) {
            throw new \RuntimeException('Something happened during HTTP request');
        }

        $domDoc = new \DOMDocument();
        libxml_use_internal_errors(true);
        $domDoc->loadHtml($response->getBody());
        libxml_use_internal_errors(false);
        $element = $domDoc->getElementById('forecasts-page');
        $nodes = $element->childNodes;

        $xml = new \SimpleXMLElement('<xml/>');
        $xml->addAttribute('updated', time());
        $number = 1;

        foreach ($nodes as $node) {
          if($node->nodeName == "script") {
            $nodeData = $xml->addChild('tab');
            $nodeData->addChild('number',$number);
            $nodeData->addChild(
              'wg_fcst_tab_data',
              $this->extractVariableIntoScript(
                $node->nodeValue,
                'wg_fcst_tab_data_'.$number
              )
            );
            $nodeData->addChild(
              'wgopts',
              $this->extractVariableIntoScript(
                $node->nodeValue,
                'wgopts_'.$number
              )
            );
            $number++;
          }
        }

        $this->_data = $xml;
        $this->setCacheData();

        $this->_log->warning($this->_idSpot . ": Load online data.");
    }

    /**
     * Extract value of a variable into the JS.
     *
     * @param string $script The String.
     * @param string $variable The variable.
     *
     * @return string
     */
    private function extractVariableIntoScript($script, $variable)
    {
      preg_match('/var ' . $variable . ' = (.*);/', $script, $m );
      return $m[1];
    }

    /**
     * Set the data of the Spot on cache.
     *
     * @param string $data The Data.
     */
    private function setCacheData()
    {
      // todo assert notNull $this->_data.

      file_put_contents(self::CACHEFOLDER.'/'.$this->_idSpot, $this->_data->asXML());
    }

    /**
     * Get the data of the Spot of cache.
     *
     * @return string/NULL.
     */
    private function getCacheData()
    {
      $current = file_get_contents(self::CACHEFOLDER.'/'.$this->_idSpot);
      $xml = new \SimpleXMLElement($current);
      if(time() - $xml['updated'] <= self::CACHETIME) {
        $this->_data = $xml;
        $this->_log->warning($this->_idSpot . ": Load cache data.");
      } else {
        $this->getOnlineData();
      }
    }

}
