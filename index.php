<?php
/*
THIS SCRIPT WAS CREATED BY: JOHNATHAN ARROYO
PROBLEM:
We have attached a csv file exported out of a MySQL database.  The task is to remove duplicates records/buildings and below are the tasks.

    Ignore and keep the records as is if they do not have strata_no
    To remove duplicates you have to find unique records based on strata_no, lat, lng
    Once you find the duplicate you need to merge the data from each column.  If there are 2 buildings and one has suites and other has levels.  The end result is to have one record with the suites and levels. 

The tasks can be performed in any order!  We are looking on your methodology and code efficiency.  

You need to do this task using PHP and MySQL and time yourself to perform this task. Once completed please send us the time required for the taks along with the code for review.  We strongly recommend you reply to us soon as we will hire once we find a suitable candidate. 
*/

ini_set("display_errors",1);
include_once __DIR__ . '/db.php';

$connect = new DB();

try{
	$connect->connect();
	
	$handle = fopen($csvFile, "r");
	if($handle){
		$id = "";
		$name = "";
		$street_no = "";
		$street_dir = "";
		$street_name = "";
		$street_type = "";
		$city = "";
		$subarea = "";
		$postcode = "";
		$suites = "";
		$levels = "";
		$strata_no = "";
		$slug = "";
		$lat = "";
		$lng = "";
		while (($data = fgetcsv($handle)) !== FALSE) {
			//var_dump($data);
			$id = $data[0];
			$name = $data[1];
			$street_no = $data[2];
			$street_dir = $data[3];
			$street_name = $data[4];
			$street_type = $data[5];
			$city = $data[6];
			$subarea = $data[7];
			$postcode = $data[8];
			$suites = $data[9];
			$levels = $data[10];
			$strata_no = $data[11];
			$slug = $data[12];
			$lat = $data[13];
			$lng = $data[14];
			$levelsUpdate = 0;
			$suitesUpdate = 0;
			$continue = false;
			//validate strata_no if empty
			if(is_numeric($id)){
				if(trim($strata_no)!=""){
					/*VERIFY IS THE UNIQUE KEY EXIST IN THE DATA BASE*/
					$sql =  "SELECT * FROM $tableName WHERE strata_no= :strataNo AND lat=:lat AND lng= :lng LIMIT 1";
					$arrParams = array(
						':strataNo' => $strata_no,
						':lat' =>  $lat,
						':lng' =>  $lng
					);
					$result = $connect->selectRow($sql, $arrParams);
					if(!empty($result)){
						//VERIFY IS THE CURRENT SAVED REGISTER HAVE VALUE, IF NOT FILL IT WITH THE NEW LEVEL VALUE
						if($result['levels'] == 0 && $levels > 0){
							$levelsUpdate = $levels;
						}
						//VERIFY IS THE CURRENT SAVED REGISTER HAVE VALUE, IF NOT FILL IT WITH THE NEW SUITES VALUE
						if($result['suites'] == 0 && $suites > 0){
							$suitesUpdate =  $suites;
						}
						
						if($levelsUpdate>0 || $suitesUpdate>0){
							$continue = true;
						}
					}else{
						$continue = true;
					}
				}else{
					//VERIFY IS THE ID WAS INSERTED IN DB BEFORE
					$sql =  "SELECT * FROM $tableName WHERE id= :id LIMIT 1";
					$arrParams = array(':id' => $id);
					$result = $connect->selectRow($sql, $arrParams);
					if(empty($result)){
						$continue = true;
					}
				}
				//end
				
				//INSERT IN DB
				if($continue){
					//IF THERE IS A NEW VALUE FOR LEVEL OR SUITES THEM UPDATE THE REGISTER
					if($levelsUpdate>0 || $suitesUpdate>0){
							//IDENTIFY WICH FIELD IS GOING TO BE UPDATED
							$fields=array();
							if($levelsUpdate>0) $fields[]=" levels=:levels";
							if($suitesUpdate>0) $fields[]=" suites=:suites";
							
							$sqlUpdate = "
								UPDATE $tableName SET 
									".implode($fields,",")."
								WHERE id=:id
							) ";
							$paramArr = array(':id' =>$result['id']);
							if($levelsUpdate>0) $paramArr[':levels']=$levelsUpdate;
							if($suitesUpdate>0) $paramArr[':suites']=$levelsUpdate;
							$connect->execute($sqlInsert,$paramArr);
					}else{
						//INSERT THE NEW REGISTER
							$sqlInsert = "INSERT INTO $tableName (id,name,street_no,street_dir,street_name,street_type,city,subarea,postcode,suites,levels,strata_no,slug,lat,lng) ";
							$sqlInsert .= "VALUES (:id,:name,:street_no,:street_dir,:street_name,:street_type,:city,:subarea,:postcode,:suites,:levels,:strata_no,:slug,:lat,:lng)";
							$paramArr = array(
								':id' =>$id,
								':name' => $name,
								':street_no' => $street_no,
								':street_dir' => $street_dir,
								':street_name' => $street_name,
								':street_type' => $street_type,
								':city' => $city,
								':subarea' => $subarea,
								':postcode' => $postcode,
								':suites' => $suites,
								':levels' => $levels,
								':strata_no' => $strata_no,
								':slug' => $slug,
								':lat' => $lat,
								':lng' => $lng
							);
							$connect->execute($sqlInsert,$paramArr);
					}
				}
			}
		}
	}
	
	//PRINTING RESULT
	echo "Process Finished! please check yout table <b>$DbName</b> on your database <b>$tableName</b> the next is the uploaded data<br>";
	
	$sql =  "SELECT * FROM $tableName";
	$arrParams = array();
	$result = $connect->selectRows($sql, $arrParams);
	if($result){
		echo "<br><b>Total:".count($result)."</b><br>";
		echo "id,name,street_no,street_dir,street_name,street_type,city,subarea,postcode,suites,levels,strata_no,slug,lat,lng";
		foreach($result[0] as $k=>$r){
			echo $k.",";
		}
		echo "<br>";
		foreach($result as $res){
			foreach($res as $r){
				echo $r.",";
			}
			echo "<br>";
		}
	}
	//END
	
}catch(PDOException $e) {
	echo "Connection failed: " . $e->getMessage();
}


