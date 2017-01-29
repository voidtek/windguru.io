<?php
/**
 * File demo.php
 *
 * PHP Version 5
 *
 * @category PHP
 * @package  WindguruIO
 * @author   Voidtek <vdacosta@voidtek.com>
 * @license  http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * @link     https://github.com/voidtek/windguru.io
 */

require_once __DIR__ . "/vendor/autoload.php";

use voidtek\WindguruIO\WindguruAPI;

if (PHP_SAPI === 'cli') {
    $idspot = $argv[1];
} else {
    $idspot = $_GET['idspot'];
}

if (empty($idspot)) {
    return;
}

$windguruAPI = new WindguruAPI();
$windguruAPI->setSpot($idspot);
$windguruAPI->getData();
