<?php


require 'vendor/autoload.php';



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
                    'idDrink' => uniqid(),
                    'drinkType' => $drink_type,
                    'orderTime' => date("Y-m-d H:i:s")
                ];
                array_push($_SESSION['queue'], $drink);

               


                // part curl
                $prepTime= "DRINK_PREP_TIME";
                $drinkId = $drink["idDrink"];
                $session_id = session_id();
                
                $data = array('PrepTime' => $prepTime, 'drinkId' => $drinkId,'session_id'=> $session_id);
                $curlHandle = curl_init('http://localhost:8888/prepare-drink.php');
                curl_setopt($curlHandle, CURLOPT_TIMEOUT_MS, 1);
                curl_setopt($curlHandle, CURLOPT_POSTFIELDS, $data);
                curl_setopt($curlHandle, CURLOPT_RETURNTRANSFER, true);

                $curlResponse = curl_exec($curlHandle);
                curl_close($curlHandle);
                

                // endpart curl

                http_response_code(200);

                //exit;
                
            }

        }
        if ($_SERVER['PATH_INFO'] == '/remove') {
            $drinkId = $_REQUEST['drinkId'];
            //session_write_close();
            session_id($_REQUEST['session_id']);
            session_start();

            foreach ($drinks as $drink) {
                
                if ($drink['idDrink'] === $id_to_find) {
                    
                    $found_drink = $drink;
                    break; 
                }
            }
            
            
            if (isset($found_drink)) {
               
                prepare_drink($found_drink);
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

    prepare_drink($drink);

    array_push($_SESSION['served'], $drink);

    // retire drink from queue list
    if (($key = array_search($drink, $_SESSION['queue'])) !== false) {
        unset($_SESSION['queue'][$key]);
    }
    
}



