<?php


require 'vendor/autoload.php';

use React\EventLoop\Factory;
use React\EventLoop\LoopInterface;
use React\EventLoop\StreamSelectLoop;
use React\Stream\ReadableResourceStream;

$loop = new StreamSelectLoop();


// Configuration
define('MAX_BEERS', 2);
define('MAX_DRINKS', 1);
define('DRINK_PREP_TIME', 5);

session_start();

if(!array_key_exists("queue",$_SESSION))
{
    $_SESSION['queue'] = [];
}

if(!array_key_exists("served",$_SESSION))
{
    $_SESSION['served'] = [];
}

$_SESSION['queue'] = [];


// API endpoint handling
switch ($_SERVER['REQUEST_METHOD']) {
    case 'POST':
        if ($_SERVER['PATH_INFO'] == '/order') {

            $customer_number = $_POST['customer_number'];
            $drink_type = $_POST['drink_type'];

            // Case bad drink type
            if (!in_array($drink_type, ['BEER', 'DRINK'])) {
                http_response_code(400);
                exit;
            }
            // Case $queue busy
            else if (count($_SESSION['queue']) >= ($drink_type === 'BEER' ? MAX_BEERS : MAX_DRINKS)) {
                http_response_code(429);
                exit;
            }
            // Case preparation
            else
            {
                $drink = [
                    'customerNumber' => $customer_number,
                    'drinkType' => $drink_type,
                    'orderTime' => date("Y-m-d H:i:s")
                ];
                array_push($_SESSION['queue'], $drink);


                $loop->addtimer(DRINK_PREP_TIME, function() use ($drink, $loop){
                    
                   prepare_drink($drink);
    
                });
               
            
                
                ignore_user_abort(true);
                header("HTTP/1.1 200 OK");
                header('Connection: close');
                header('Content-Length: '.ob_get_length());
                ob_end_flush();
                flush();

                $loop->run($drink);

                exit;
                
            }

        }
        break;

    case 'GET':
        if ($_SERVER['PATH_INFO'] == '/served') {
            // Respond with served drinks and customers
            header('Content-Type: application/json');
            echo json_encode($_SESSION['served']);
        }
        break;
}

function prepare_drink($drink)
{
    // add drink in served list

    array_push($_SESSION['served'], $drink);

    // retire drink from queue list
    if (($key = array_search($drink, $_SESSION['queue'])) !== false) {
        unset($_SESSION['queue'][$key]);
    }

}



