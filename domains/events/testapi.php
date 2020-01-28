<?php

// id_organisme = 000001019441
// https://api.helloasso.com/v3/actions/000049129383.json
// https://api.helloasso.com/v3/payments/000026835783.json

// marche pas:
// https://api.helloasso.com/v3/campaigns/000000195876.json
// https://api.helloasso.com/v3/campaigns/000000195876/actions.json

/*
This little snippet can be used in wordpress with plugin like "Insert PHP Code Snippet" or in pure PHP web site
in order to add your helloasso statistics on your personnal/organization website
*/
$APIKEY = 'bde-de-blaise-pascal';
$APIPASSWORD = 'h8eMftCq7oSbfDiYJo69q';

// test-api

if (isset($_POST['url']))
{
    $curl = curl_init();
    // Optional Authentication:
    curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
    curl_setopt($curl, CURLOPT_USERPWD, $APIKEY . ":" . $APIPASSWORD);
    curl_setopt($curl, CURLOPT_URL, $_POST['url']);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    $json = json_decode(curl_exec($curl), true);
    curl_close($curl); 
}
// else if (isset($_GET['slug']))
// {
//     curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
//     curl_setopt($curl, CURLOPT_USERPWD, $APIKEY . ":" . $APIPASSWORD);
//     curl_setopt($curl, CURLOPT_URL, "https://api.helloasso.com/v3/campaigns.json?results_per_page=1000");
//     curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
//     $result = json_decode(curl_exec($curl), true);
//     foreach ($result["resources"] as $campagne)
//     {
//         if ($campagne["slug"])
//     }

// }

// $curl = curl_init();
// // Optional Authentication:
// curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
// curl_setopt($curl, CURLOPT_USERPWD, $APIKEY . ":" . $APIPASSWORD);
// // curl_setopt($curl, CURLOPT_URL, "https://api.helloasso.com/v3/actions.json");
// // curl_setopt($curl, CURLOPT_URL, "https://api.helloasso.com/v3/organizations/000001019441/campaigns/000000000486/actions.json");
// // curl_setopt($curl, CURLOPT_URL, "https://api.helloasso.com/v3/organizations/bde-de-blaise-pascal/campaigns/000000000484/payments.json");
// // curl_setopt($curl, CURLOPT_URL, "https://api.helloasso.com/v3/organizations/000001019441/campaigns.json?results_per_page=1000");
// // 000000195876
// curl_setopt($curl, CURLOPT_URL, "https://api.helloasso.com/v3/organizations/000001019441/campaigns/000000195876/payments.json");
// // curl_setopt($curl, CURLOPT_URL, "https://api.helloasso.com/v3/payments.json");
// curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
// $json = json_decode(curl_exec($curl));
// curl_close($curl); 
?>

org 000001019441<br>cmp 000000195876<br>pay 000026835783<br>
<hr>
https://api.helloasso.com/v3/organizations/000001019441/campaigns/000000195876/
<hr>

<form action="" method="post">
    <input type="text" name="url" value="https://api.helloasso.com/v3/" style="width: 500px;"></input>
    <button type="submit" value="go">GO</button>
</form>

<hr>
<pre>
<?php
var_dump($_POST['url']);
echo "<hr>";
var_dump($json);

// // Display all information from request
// foreach ($json->resources as $action) {
// 	echo "<h3>action de type $action->type d'un montant $action->montant effectuée le $action->date</h3><br/>";
// 	if(count($action->custom_infos) > 0) {
// 		echo "Liste des informations additionnelles:<br/>";
// 		echo "<ul>";
// 		foreach ($action->custom_infos as $info) {
// 			echo "<li>$info->label : $info->value</li>";
// 		}
// 		echo "</ul>";
// 	}
// 	else {
// 		echo "Sans information additionnelle<br/>";
// 	}
// }
// // Display only some specific custom informations
// foreach ($json->resources as $action) {
// 	// We get only action with custom informations
// 	if(count($action->custom_infos) > 0) {
// 		// First we have to get index
// 		$phoneIndex = findIndex($action->custom_infos, "Numéro de téléphone");
// 		$emailIndex = findIndex($action->custom_infos, "Email");
// 		$photoIndex = findIndex($action->custom_infos, "Photo d'identité");
//     		// Then value
// 		$phoneValue = $phoneIndex == null ? "" : $action->custom_infos[$phoneIndex]->value;
// 		$emailValue = $emailIndex == null ? "" : $action->custom_infos[$emailIndex]->value;
// 		$photoValue = $photoIndex == null ? "" : $action->custom_infos[$photoIndex]->value;
// 		// Now display summary with only id picture
// 		echo "<h3>action de type $action->type d'un montant $action->montant effectuée le $action->date avec cette photo $photoValue</h3><br/>";
// 	}
// }
// /*
//  * Small function to find index of label value in custom_infos array
//  */
// function findIndex($array, $value) {
// 	$index = 0;
// 	foreach ($array as $item) {
// 		if($item->label == $value){
// 			return $index;
// 		}
// 		$index++;
// 	}
// 	return null;
// }
?>
</pre>