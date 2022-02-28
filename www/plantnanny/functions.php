<?php
   /*
   Plugin Name: Plant Nanny
   description: A plugin to handle interactions with the Plant Nanny database
   Version: 1.0
   Author: Logan Balkwill
   */
   
defined( 'ABSPATH' ) OR exit;
add_action( 'init', 'plant_nanny_add_shortcode');

include('/var/www/html/wp-content/plugins/wp-charts-and-graphs/wp-charts-and-graphs.php');

define('DB_DATA', 'plantnan_plantnanny');

function plant_nanny_add_shortcode(){    
   add_shortcode('pn-latest-datetime', 'pn_get_latest_datetime');
   add_shortcode('pn-latest-temp', 'pn_get_latest_temp');
   add_shortcode('pn-latest-humid', 'pn_get_latest_humid');
   add_shortcode('pn-latest-soilmoisture', 'pn_get_latest_soilmoisture');
   add_shortcode('pn-latest-soilmoisture-diff', 'pn_get_latest_soilmoisture_change');
   add_shortcode('pn-lighting-schedule', 'pn_get_lighting_schedule');
   add_shortcode('pn-latest-lighting-green', 'pn_get_latest_lighting_green');
   add_shortcode('pn-latest-lighting-blue', 'pn_get_latest_lighting_blue');
   add_shortcode('pn-latest-lighting-red', 'pn_get_latest_lighting_red');
   add_shortcode('pn-latest-lighting-rgb', 'pn_get_latest_lighting_rgb');
   add_shortcode('pn-latest-lighting-rgb-str', 'pn_get_latest_lighting_rgb_str');
   add_shortcode('chart-latest-lighting', 'chart_latest_lighting');
   add_shortcode('chart-latest-soilmoisture', 'chart_latest_soil_moisture');
}

function pn_get_latest_datetime(){
   global $wpdb;
   $wpdb_pn = new $wpdb(DB_USER, DB_PASSWORD, DB_DATA, DB_HOST);

   $results =  $wpdb_pn->get_row("SELECT DateTime as DT FROM airsensor_trans WHERE 1 ORDER BY DateTime DESC LIMIT 0,1");
   return $results->DT;
 }

function pn_get_latest_temp(){
   global $wpdb;
   $wpdb_pn = new $wpdb(DB_USER, DB_PASSWORD, DB_DATA, DB_HOST);
   
   $results =  $wpdb_pn->get_row("SELECT DateTime, ROUND(AirTemp_DegC,1) as AirTemp_DegC FROM airsensor_trans WHERE 1 ORDER BY DateTime DESC LIMIT 0,1");
   return $results->AirTemp_DegC;
 }

function pn_get_latest_humid(){
   global $wpdb;
   $wpdb_pn = new $wpdb(DB_USER, DB_PASSWORD, DB_DATA, DB_HOST);
   
   $results =  $wpdb_pn->get_row("SELECT DateTime, ROUND(AirHumidity_percent,1) as AirHumidity_percent FROM airsensor_trans WHERE 1 ORDER BY DateTime DESC LIMIT 0,1");
   return $results->AirHumidity_percent;
 }

function pn_get_latest_soilmoisture( $atts = '' ){
   //Retrieve Latest Soil Moisture Values
   //unpack parameters
   $num_rows = shortcode_atts( array(
         'lookback_period' => 20
         ), $atts );
   
   //condition query string
   $sql_string = "SELECT (CASE 	WHEN AVG(a.SoilMoisture_val)<985 THEN 0 WHEN AVG(a.SoilMoisture_val)>1010 THEN 100 ELSE CAST((100*((AVG(a.SoilMoisture_val)-985)/(1010-985))) AS int) END) as SoilMoisture_avg, MIN(a.DateTime) as DateTime_min, MAX(a.DateTime) as DateTime_max FROM (SELECT * FROM `soilsensor_trans` WHERE 1 ORDER BY record_id DESC LIMIT 0," 
                  . strval($num_rows['lookback_period'])
                  . ") AS a";
   
   //perform query & return result
   global $wpdb;
   $wpdb_pn = new $wpdb(DB_USER, DB_PASSWORD, DB_DATA, DB_HOST);
   
   $results =  $wpdb_pn->get_row($sql_string);
   $moisture = $results->SoilMoisture_avg;
   
   if ($moisture >= 66) {
      $res='<p style="display:inline; color:chartreuse;">' .$moisture .'%</p>';
   } elseif ($moisture >= 33) {
      $res=strval($moisture) .'%';   //don't format; use theme color
   } else {
      $res='<p style="display:inline; color:lightsalmon;">' .$moisture .'%</p>';
   }
   return $res;
 }

function pn_get_latest_soilmoisture_change( $atts = '' ){
   //Retrieve Latest Soil Moisture Values
   //unpack parameters
   $num_rows = shortcode_atts( array(
         'lookback_period' => 24,
         'lookback_offset' => 24
         ), $atts );
   
   //condition query strings
   $sql_string_1 = "SELECT (CASE 	WHEN AVG(a.SoilMoisture_val)<985 THEN 0 WHEN AVG(a.SoilMoisture_val)>1010 THEN 100 ELSE CAST((100*((AVG(a.SoilMoisture_val)-985)/(1010-985))) AS int) END) as SoilMoisture_avg, MIN(a.DateTime) as DateTime_min, MAX(a.DateTime) as DateTime_max FROM (SELECT * FROM `soilsensor_trans` WHERE 1 ORDER BY record_id DESC LIMIT 0," 
                  . strval($num_rows['lookback_period'])
                  . ") AS a";
   $sql_string_2 = "SELECT (CASE 	WHEN AVG(a.SoilMoisture_val)<985 THEN 0 WHEN AVG(a.SoilMoisture_val)>1010 THEN 100 ELSE CAST((100*((AVG(a.SoilMoisture_val)-985)/(1010-985))) AS int) END) as SoilMoisture_avg, MIN(a.DateTime) as DateTime_min, MAX(a.DateTime) as DateTime_max FROM (SELECT * FROM `soilsensor_trans` WHERE 1 ORDER BY record_id DESC LIMIT " 
                  . strval($num_rows['lookback_offset'])
                  . ","
                  . strval($num_rows['lookback_period'])
                  . ") AS a";
   
   //perform queries
   global $wpdb;
   $wpdb_pn = new $wpdb(DB_USER, DB_PASSWORD, DB_DATA, DB_HOST);
   $results1 =  $wpdb_pn -> get_row($sql_string_1);
   $results2 =  $wpdb_pn -> get_row($sql_string_2);
   
   //operate on query results
   $val1 = $results1 -> SoilMoisture_avg;
   $val2 = $results2 -> SoilMoisture_avg;
   $moisture_difference = round(($val1 - $val2));

   $date1 = $results1-> DateTime_max;
   $date2 = $results2-> DateTime_max;
   
   $return_val = condition_value_html($moisture_difference, '%');
   $result_datediff = get_datediff_str($date1, $date2);
   
   return $return_val . ' ' . $result_datediff;
 }

function condition_value_html($val, $suffix_str ='')   {
   if ($val > 0){
      $return_val = '<strong style="color:chartreuse;">+' .strval($val) . $suffix_str . '</strong>';
   } elseif ($val <0){
      $return_val = '<strong style="color:lightsalmon;">' .strval($val) . $suffix_str .'</strong>';
   } else {
      $return_val = strval($val) .$suffix_str;
   }
   
   return $return_val;
 }
 
function get_datediff_str($d1, $d2){
//With 2 DateTime types supplied, outputs date difference as a string
   
   //Get date difference and dissect...
   $datediff = abs(strtotime($d1) - strtotime($d2));
   $datediff_d = floor(($datediff)/ (60*60*24));
   $datediff_hr = floor(($datediff-($datediff_d*60*60*24))/ (60*60));
   $datediff_min = floor(($datediff-($datediff_d*60*60*24)-($datediff_hr*60*60))/ (60));
   $datediff_sec = floor(($datediff-($datediff_d*60*60*24)-($datediff_hr*60*60)-($datediff_min*60)));
   
   
   //Build results string
   $result_datediff='change in ';
   if ($datediff_d > 0){
      $result_datediff = $result_datediff .strval($datediff_d) . ' Days ' .strval($datediff_hr) . ' Hours ' .strval($datediff_min) . ' Minutes';
   } elseif ($datediff_hr > 0){
      $result_datediff = $result_datediff .strval($datediff_hr) . ' Hours ' .strval($datediff_min) . ' Minutes';
   } elseif ($datediff_min > 0){
      $result_datediff = $result_datediff .strval($datediff_min) . ' Minutes';
   } else {
      $result_datediff = $result_datediff . '??? Minutes';
   }
   
   return $result_datediff;
 }

function pn_get_latest_lighting_rgb(){
   global $wpdb;
   $wpdb_pn = new $wpdb(DB_USER, DB_PASSWORD, DB_DATA, DB_HOST);
   $results = $wpdb_pn->get_row("SELECT DateTime, colour_red, colour_green, colour_blue, colour_clear FROM lightsensor_trans WHERE 1 ORDER BY DateTime DESC LIMIT 1");
   return $results;
 }

function pn_get_lighting_schedule( $atts = '' ){
   //Returns string outlining summary of lighting schedule
   
   global $wpdb;
   $wpdb_pn = new $wpdb(DB_USER, DB_PASSWORD, DB_DATA, DB_HOST);
   $results = $wpdb_pn->get_results("SELECT lh.DT_Hour, lh.DT_Min, AVG(lh.lighting_avg) AS light_avg FROM (SELECT (lt.colour_red + lt.colour_green + lt.colour_blue + lt.colour_clear/4) AS lighting_avg, HOUR(lt.DateTime) AS DT_Hour, (CASE WHEN MINUTE(lt.DateTime)<=15 THEN 0 WHEN MINUTE(lt.DateTime)<=30 THEN 15 WHEN MINUTE(lt.DateTime)<=45 THEN 30 WHEN MINUTE(lt.DateTime)<=60 THEN 45 END) AS DT_Min FROM lightsensor_trans AS lt WHERE DATE(lt.DateTime)=(CURRENT_DATE()-1)) AS lh GROUP BY lh.DT_Hour, lh.DT_Min");
   
   $light_schedule = determine_lighting_schedule($results);
   
   return $light_schedule;
 }
 
function determine_lighting_schedule($light_sched){
    //Takes an array of lighting history (hr, min, lighting intensity),
    //Returns string summarizing schedule
    $return_str='';
    
    if ( $light_sched ){
       $total_value = 0;
       foreach ( $light_sched as $rw){
          $total_value += $rw->light_avg;}

       $hr=0;
       $min=0;
       $off_counter=0;
       foreach ( $light_sched as $rw){
          if (($rw -> light_avg)/$total_value<0.001){
             //Light if OFF, log it
             $off_counter+=1; 
          }
       }
       
       $off_hrs=$off_counter/4;
       $return_str = 'Total On Time: ' .strval(24-$off_hrs) .' Hours<br>Total Off Time: ' .strval($off_hrs) .' Hours';
    } else $return_str= 'No Data Available';
    
    
    return $return_str;
 }
function pn_get_latest_lighting_rgb_str(){
   $results = pn_get_latest_lighting_rgb();
   
   $result_str=strval($results->colour_red) . ', ' . strval($results->colour_green) . ', ' . strval($results->colour_blue);
   return $result_str;
}
 
function pn_get_latest_lighting_green(){
   
   $lighting = pn_get_latest_lighting_rgb();
   $g = $lighting->colour_green;
   $r = $lighting->colour_red;
   $b = $lighting->colour_blue;
   $result = pn_get_colour_percent($g, array($r, $b));
   return $result;
 }
 
 function pn_get_latest_lighting_blue(){
   
   $lighting = pn_get_latest_lighting_rgb();
   $g = $lighting->colour_green;
   $r = $lighting->colour_red;
   $b = $lighting->colour_blue;
   $result = pn_get_colour_percent($b, array($g, $r));
   return $result;
 }
 
function pn_get_latest_lighting_red(){
   
   $lighting = pn_get_latest_lighting_rgb();
   $g = $lighting->colour_green;
   $r = $lighting->colour_red;
   $b = $lighting->colour_blue;
   $result = pn_get_colour_percent($r, array($g, $b));
   return $result;
 }

function pn_get_colour_percent($input_colour, $other_colours){
   /*Takes in a reading of the target colour and an array of the other colour values (excluding target colour)
    *Returns value between 0-100 of percentage value
   */
   $total_reading=0;
   
   foreach ($other_colours as $colourval){
      $total_reading = $total_reading + $colourval;
   }
   
   $total_reading = $total_reading + $input_colour;
   
   $colourpercent = round(1000*($input_colour / $total_reading))/10;
   return $colourpercent;
 }
 
function pn_rgb_raw_to_relative_str($arr){
   $total_reading = ($arr->colour_red) + ($arr->colour_green) + ($arr->colour_blue);
   
   $str_relative =   strval(pn_get_colour_percent($arr->colour_red, array($arr->colour_red, $arr->colour_blue))) . ', ' .
                     strval(pn_get_colour_percent($arr->colour_green, array($arr->colour_green, $arr->colour_blue))) . ', ' .
                     strval(pn_get_colour_percent($arr->colour_blue, array($arr->colour_green, $arr->colour_red)));

   return $str_relative;
}


function chart_latest_lighting(){
	//shortcode parameters
	$chart_type = "piechart";
	$legend = "false";
	$titles = "Red, Green, Blue";
	$values = pn_get_latest_lighting_rgb();
   $values = pn_rgb_raw_to_relative_str($values);
	$bgcolor = "red:gray:#FF5454,green:gray:#91FF98,blue:gray:#33C4FF";
	
	//build shortcode string
	$shortcode_str = '[wpcharts type="<type>" bgcolor="<bgcolor>" legend="<legend>" titles="<titles>" values="<values>" lazy="yes"]';
	$shortcode_str = str_replace('<type>', $chart_type, $shortcode_str);
	$shortcode_str = str_replace('<bgcolor>', $bgcolor, $shortcode_str);
	$shortcode_str = str_replace('<legend>', $legend, $shortcode_str);
	$shortcode_str = str_replace('<titles>', $titles, $shortcode_str);
	$shortcode_str = str_replace('<values>', $values, $shortcode_str);
	return $shortcode_str;
	
	//echo $shortcode_str;
 }
 
function chart_latest_soil_moisture( $atts = '' ){
//Generates soil moisture graph

/*Optional Parameters:
 * plant_id - Filter data by plant ID (type enum); default = (ALL)
 * datetime_end - When to generate data up to; default = latest data
 * lookback_period - Time period (hours) to look back at (relative to datetime_end); default = 24hrs
 * avg_rows - Rows to include in the moving average calculation; default = 30 rows
*/
   
   //Unpack parameters
   extract( shortcode_atts( array(
      'plant_id' => '', 
      'datetime_end' => '', 
      'lookback_period' => '24', 
      'avg_rows' => '10',
      'width' => '800px',
      'height' => '500',
      ), $atts ));
   
   //Generate query string
   if ($datetime_end == ''){
      $datestop = 'CURRENT_TIMESTAMP()';
   } else {
      $datestop = strval($datetime_end);
   }
      
   $qry_str=   'SELECT sst.DateTime, sst.SoilMoisture_val, AVG(sst.SoilMoisture_val) OVER (ORDER BY sst.record_id ASC ROWS '
               .strval(($avg_rows)-1) 
               . ' PRECEDING) AS SoilMoisture_avg FROM soilsensor_trans AS sst 
               WHERE sst.DateTime>=ADDDATE(' .$datestop .', INTERVAL ' .strval(($lookback_period)*(-1)) . ' HOUR) ' ;
   if ($plant_id != ''){
      $qry_str= $qry_str . 'AND plant_id=' . strval($plant_id) . ' ';
   }
   
   
   //Run Query
   global $wpdb;
   $wpdb_pn = new $wpdb(DB_USER, DB_PASSWORD, DB_DATA, DB_HOST);
   $results = $wpdb_pn->get_results($qry_str);
   
   //Unpack Query Results
   $titles = "";
   $values = "";
   
   foreach ($results as $rw){
      $titles .= ", " .strval($rw->DateTime);
      $values .= ", " .strval(100*(($rw->SoilMoisture_avg)-985)/(1010-985));
   }
   
   //Build Graph
   $g_type = "linechart";
	$g_legend = "false";
	$g_titles = ltrim($titles, ', ');
	$g_values = ltrim($values, ", ");
   $g_bgcolor = "";
   
   //Build Shortcode String
   $shortcode_str = '[wpcharts type="<type>" bgcolor="<bgcolor>" legend="<legend>" titles="<titles>" values="<values>" width="<width>" height="<height>"]';
	$shortcode_str = str_replace('<type>', $g_type, $shortcode_str);
	$shortcode_str = str_replace('<bgcolor>', $g_bgcolor, $shortcode_str);
	$shortcode_str = str_replace('<legend>', $g_legend, $shortcode_str);
	$shortcode_str = str_replace('<titles>', $g_titles, $shortcode_str);
	$shortcode_str = str_replace('<values>', $g_values, $shortcode_str);
	$shortcode_str = str_replace('<width>', $width, $shortcode_str);
	$shortcode_str = str_replace('<height>', $height, $shortcode_str);
   //return $g_values;
   return $shortcode_str;
}
?>