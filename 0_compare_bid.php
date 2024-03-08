<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="es-es" lang="es-es">
<head>
<?php
error_reporting(E_ALL);
ini_set("display_errors", 1);

ob_start();
require_once 'Credentials.php';
require_once 'Adwords.php';


function sql_function($consulta) {
		
		$my_server="localhost";
		$my_userid="campaigndb";
		$my_pass="Campign#1000";
		$my_database="campaigndb";
		$linkdb=mysqli_connect($my_server,$my_userid,$my_pass,$my_database);
		mysqli_select_db($linkdb, $my_database);

		$copia = $consulta;
		$consulta = trim(strtolower($consulta));

		if (!$query = mysqli_query($linkdb,$copia)){
			return false;
		}
		

		switch(strtok($consulta," ")) {
			case "select":

					$result = array();
					

					while($resultado = mysqli_fetch_array($query)) {
						$nombres = array_keys($resultado);
							
						foreach ($nombres as $act) {
							if(is_int($act)) {
								$t[] = $resultado[$act];
							}
						}
						$result[] = $t;
						unset($t);
					}
					

					return $result;
					break;
					
			case "insert":
			case "delete":
			case "update":

					return mysqli_affected_rows($linkdb);
					break;
			default:
				return false;
				break;
		}
}
?>
<meta name='robots' content='noindex, follow' />
</head>

<body>
<?php


$usuarios_sql = sql_function("SELECT usuarios_id, user_pass, user_name, user_surname, user_email, user_url, company_name, vat_num, billing_adress1, billing_adress2, city, postal_code, pais_id, user_registered, user_status, user_verified, tel_num, state, clientid, usuarios_master_id, ai_verified FROM usuarios WHERE usuarios_id = '$_REQUEST[usuarioid]'");
$usuarios_value = $usuarios_sql[0];
if($usuarios_value[14] == "1"){
		
	class Processor {
			protected $advertising;
			
			
			public function __construct()
			{           
				$arr = [
					'OAUTH2' => [
									'developerToken' => Credentials::$DEVELOPER_TOKEN,
									'clientId' => Credentials::$CLIENT_ID,
									'clientSecret' => Credentials::$CLIENT_SECRET,
									'refreshToken' => Credentials::$REFRESH_TOKEN,
								]
					];
					$this->advertising = new Adwords($arr,  Credentials::$MASTER_ID);
			}
			public function get_keyword_position_estimates($customerId, $adGroupId)
			{
			   return $this->advertising->ListKeywordPositionEstimates($this->advertising->createClient(Credentials::$MASTER_ID), $customerId, $adGroupId);
			}
			public function listkeyword($customerId, $adGroupId)
			{
			   return $this->advertising->ListKeyword($this->advertising->createClient(Credentials::$MASTER_ID), $customerId, $adGroupId);
			}
			public function updatekeywordbid($customerId, $adGroupId, $criterionId, $bidModifierAmount)
			{
			   return $this->advertising->UpdateKeywordBid($this->advertising->createClient(Credentials::$MASTER_ID), $customerId, $adGroupId, $criterionId, $bidModifierAmount);
			}
	   
			public function get_customer_account()
			{
			   return $this->advertising->GetAccountInfo($this->advertising->createClient(Credentials::$MASTER_ID), Credentials::$ACCOUNT_ID);
			}
			public function generate_keyword_ideas($keywords, $country, $language)
			{        
			   return $this->advertising->KeywordMetrics($this->advertising->createClient(Credentials::$MASTER_ID), Credentials::$ACCOUNT_ID, $country, $language, $keywords, null);
			}

	}
	
	$googleaprocessor = new Processor();

	$usuarios_google_account_sql = sql_function("SELECT usuarios_google_account_id, usuarios_id, google_account_code FROM usuarios_google_account WHERE usuarios_id = '$_REQUEST[usuarioid]'");
	$usuarios_google_account_value = $usuarios_google_account_sql[0];
	echo $usuarios_google_account_value[2]."<br><br>";
			
	$usuario_parameters_sql = sql_function("SELECT usuarios_parameters_id, usuarios_id, max_puja FROM usuarios_parameters WHERE usuarios_id = '$_REQUEST[usuarioid]'");
	$usuario_parameters_value = $usuario_parameters_sql[0];
	$tope_puja = $usuario_parameters_value[2];
	echo $tope_puja."<br><br>";

	$customerId = str_replace("-","",$usuarios_google_account_value[2]);
	
	$campaign_sql = sql_function("SELECT campaign_id, usuarios_google_account_id, campaign_identifier, campaign_nombre, estado FROM campaign WHERE usuarios_google_account_id = '$usuarios_google_account_value[0]' and estado = '1'");
	foreach($campaign_sql as $cs){
		$campaignId = $cs[2];
		$bidModifierValue = 0.5;
		$pais = '';
		$idioma = '';		
	
		$campaign_adgroup_sql = sql_function("SELECT campaign_adgroup_id, campaign_id, adgroup_identifier, adgroup_nombre, estado FROM campaign_adgroup WHERE campaign_id = '$cs[0]' and estado = '1'");
		foreach($campaign_adgroup_sql as $cas){
			$adGroupId = $cas[2];
			$bidding = $googleaprocessor->get_keyword_position_estimates($customerId, $adGroupId);
			foreach($bidding as $bids){
				foreach($bids as $bid){
					$bid_identifier = $bid['id'];
					$bid_name = $bid['name'];
					$bid_status = $bid['status'];
					$bid_adgroup_id = $bid['adgroup_id'];
					$bid_first_page_cpc = $bid['first_page_cpc'];
					$first_page_cpc = $bid_first_page_cpc / 1000000;
					$bid_first_position_cpc = $bid['first_position_cpc'];
					$first_position_cpc = $bid_first_position_cpc / 1000000;
					$bid_top_page_cpc = $bid['top_page_cpc'];
					$top_page_cpc = $bid_top_page_cpc / 1000000;
					$bid_cpc = $bid['bid_cpc'];
					$cpc = $bid_cpc / 1000000;
					echo $bid_identifier." - ".$bid_name." - ".$bid_status." - ".$cpc." - ".$first_page_cpc." - ".$top_page_cpc." - ".$first_position_cpc." -- ".$tope_puja."<br>";
					
					if($first_position_cpc > 0 && $first_position_cpc < $tope_puja){
						$nueva_puja = round(floatval($first_position_cpc),2) + 0.01;
						$nueva_puja_cal = round(floatval($nueva_puja * 1000000),2); $nueva_puja_cal = intval($nueva_puja_cal); echo $customerId." - ".$bid_adgroup_id." - ".$bid_identifier." - ".$nueva_puja." - ".$nueva_puja_cal."<br>";
						//update puja actual
						$updatebid = $googleaprocessor->updatekeywordbid($customerId, $bid_adgroup_id, $bid_identifier, $nueva_puja_cal);
						echo "---<br>"; //action when updated
					}
					elseif($top_page_cpc > 0 && $top_page_cpc < $tope_puja){
						$nueva_puja = round(floatval($top_page_cpc),2) + 0.03;
						$nueva_puja_cal = round(floatval($nueva_puja * 1000000),2); $nueva_puja_cal = intval($nueva_puja_cal); echo $customerId." - ".$bid_adgroup_id." - ".$bid_identifier." - ".$nueva_puja." - ".$nueva_puja_cal."<br>";
						//update puja actual
						$updatebid = $googleaprocessor->updatekeywordbid($customerId, $bid_adgroup_id, $bid_identifier, $nueva_puja_cal);
						echo "--<br>"; //action when updated
					}
					elseif($first_page_cpc > 0 && $first_page_cpc < $tope_puja){
						//echo $first_page_cpc."<br>";
						$nueva_puja = round(floatval($first_page_cpc),2) + 0.03;
						$nueva_puja_cal = round(floatval($nueva_puja * 1000000),2); $nueva_puja_cal = intval($nueva_puja_cal); echo $customerId." - ".$bid_adgroup_id." - ".$bid_identifier." - ".$nueva_puja." - ".$nueva_puja_cal."<br>";
						$updatebid = $googleaprocessor->updatekeywordbid($customerId, $bid_adgroup_id, $bid_identifier, $nueva_puja_cal);
						echo "-<br>"; //action when updated
					}
					else{
						//echo $first_page_cpc."<br>";
						$nueva_puja = round(floatval($tope_puja),2);
						$nueva_puja_cal = round(floatval($nueva_puja * 1000000),2); $nueva_puja_cal = intval($nueva_puja_cal); echo $customerId." - ".$adGroupId." - ".$bid_identifier." - ".$nueva_puja." - ".$nueva_puja_cal."<br>";
						$updatebid = $googleaprocessor->updatekeywordbid($customerId, $bid_adgroup_id, $bid_identifier, $nueva_puja_cal);
						echo "x<br>"; //action when updated
					}
						
				}
			}


			/*$palabra_clave_sql = sql_function("SELECT palabra_clave_id, campaign_id, campaign_adgroup_id, campaign_identifier, adgroup_identifier, keywords, keywords_identifier, actual_bid, first_page_cpc, top_page_cpc, first_position_cpc, status FROM palabra_clave WHERE campaign_identifier = '$campaignId'");
			$palabra_clave_value = $palabra_clave_sql[0];

			$keywords = $googleaprocessor->searchterms($customerId, $campaignId);
			echo '<pre>';
			print_r($keywords);*/

			/*$keywords = $googleaprocessor->listkeyword($customerId, $adGroupId);
			echo '<pre>';
			print_r($keywords);*/

			/*$bid_identifier = 323571562273;
			$nueva_puja = 340000;
			$updatebid = $googleaprocessor->updatekeywordbid($customerId, $adGroupId, $bid_identifier, $nueva_puja);
			echo '<pre>';
			print_r($updatebid);*/
		}
	}

}
?>

</body>
</html>
 