<?php
// Michael Rajotte - 2017 Avril
// epson.php
// Epson printer and pos cash drawer testing.

require_once (__DIR__ . "/../fluid.required.php");
require_once (__DIR__ . "/../fluid.class.php");

use Mike42\Escpos\Printer;
use Mike42\Escpos\PrintConnectors\NetworkPrintConnector;
use Mike42\Escpos\EscposImage;

$connector = new NetworkPrintConnector("192.168.1.187", 9100);

$printer = new Printer($connector);

try {
    // ... Print stuff
    //$printer -> cut(); // Cut the paper
    //$printer -> pulse(); // Open cash drawer.
} finally {
    $printer -> close();
}
?>
