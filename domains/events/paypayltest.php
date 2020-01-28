<?php
require_once(__DIR__ . '/../../dependances/vendor/autoload.php');
use PayPalCheckoutSdk\Core\PayPalHttpClient;
use PayPalCheckoutSdk\Core\SandboxEnvironment;

$clientId = "AXNmhCK1ykyCjlirRHw9XaQYrs_3W9TIDWfritlTewjPj2YBwiHVA8oqTVWSNQ5xp-uE0yJNs7ICr6By";
$clientSecret = "EBzR_tC_IOF1HIrWKLlSqLgl4M6rYWIOQh1TZOUaDl6ONAJc95m094aB9rtvQ2XF3A0qQKgadJj0cACI";


$environment = new SandboxEnvironment($clientId, $clientSecret);
$client = new PayPalHttpClient($environment);

use PayPalCheckoutSdk\Payments\AuthorizationsCaptureRequest;
use PayPalCheckoutSdk\Payments\CapturesGetRequest;


$request = new AuthorizationsCaptureRequest();
$request->prefer('return=representation');
$request->body = [
                    "intent" => "CAPTURE",
                    "purchase_units" => [[
                        "reference_id" => "adh_1",
                        "description" => "Description du tarif",
                        "custom_id" => "123456789",
                        "amount" => [
                            "value" => "10.00",
                            "currency_code" => "EUR"
                        ]
                    ]],
                    "application_context" => [
                        "cancel_url" => "https://example.com/cancel",
                        "return_url" => "https://events.bde-bp.fr/paypaltest.php",
                        "user_action" => "PAY_NOW"
                    ] 
                ];















try
{
    // Call API with your client and get a response for your call
    $response = $client->execute($request);
    
    // If call returns body in response, you can get the deserialized version from the result attribute of the response
    print_r($response);
}
catch (HttpException $ex)
{
    echo $ex->statusCode;
    print_r($ex->getMessage());
}





















// use PayPalCheckoutSdk\Orders\OrdersCaptureRequest;
// use PayPalCheckoutSdk\Orders\OrdersCreateRequest;


// function getFee(float $prix)
// {
//     return (($prix * 0.29) + 0.3)/0.971;
// }
// function getTotal(float $prix)
// {
//     return ($prix + 0.3)/0.971;
// }
// function getOrder(float $prix)
// {
//     // $fee = (($prix * 0.29) + 0.3)/0.971;
//     // $fee = round($fee, 2);
//     $total = ($prix + 0.3)/0.971;
//     $total = round($total, 2);

//     $request = new OrdersCreateRequest();
//     $request->prefer('return=representation');
//     $request->body = [
//                          "intent" => "CAPTURE",
//                          "purchase_units" => [[
//                              "reference_id" => "adh_1",
//                              "description" => "Description du tarif",
//                              "custom_id" => "123456789",
//                              "amount" => [
//                                  "value" => (string) $total,
//                                  "currency_code" => "EUR"
//                                 ]
//                             //  ],
//                             //  "payment_instruction" => [
//                             //      "platform_fees" => [
//                             //         "amount" => [
//                             //             "value" => (string) $fee,
//                             //             "currency_code" => "EUR"
//                             //         ],
//                             //      ]
//                             //  ]
//                          ]],
//                          "application_context" => [
//                               "cancel_url" => "https://example.com/cancel",
//                               "return_url" => "https://events.bde-bp.fr/paypaltest.php",
//                               "user_action" => "PAY_NOW"
//                          ] 
//                      ];
//     return $request;
// }


// if (isset($_GET['token']))
// {


//     // Here, OrdersCaptureRequest() creates a POST request to /v2/checkout/orders
//     // $response->result->id gives the orderId of the order created above
//     $request = new OrdersCaptureRequest($_GET['token']);
//     $request->prefer('return=representation');
//     try {
//         // Call API with your client and get a response for your call
//         $response = $client->execute($request);
        
//         // If call returns body in response, you can get the deserialized version from the result attribute of the response
//         print_r($response);
//     }catch (HttpException $ex) {
//         echo $ex->statusCode;
//         print_r($ex->getMessage());
//     }
// }
// else
// {
//     $request = getOrder(10.00);
    
//     try {
//         // Call API with your client and get a response for your call
//         $response = $client->execute($request);
        
//         // If call returns body in response, you can get the deserialized version from the result attribute of the response
//         print_r($response);
//     }catch (HttpException $ex) {
//         echo $ex->statusCode;
//         print_r($ex->getMessage());
//     }
// }




?>