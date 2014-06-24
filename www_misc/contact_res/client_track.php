<?php

function getIP() {
       $tmparr = array();
       $tmparr[] = $_SERVER['REMOTE_ADDR'];
       if  (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
           $tmparr +=  explode(',',$_SERVER['HTTP_X_FORWARDED_FOR']);
       }
       
       return $tmparr;
   }


/* Country Zone : Time Zone Name
-12 : Dateline Standard
-11 : Samoa Standard Time
-10 : Hawaiian Standard Time
-8 : Pacific Standard Time
-7 : Mexican Standard Time, Mountain Standard Time
-6 : Central Standard Time, Mexico Standard Time
-5 : Eastern Standard Time Eastern Time, SA Pacific Standard Time
-4 : Atlantic Standard Time, SA Western Standard Time, Pacific SA Standard Time
-3.5 : Newfoundland Standard Time
-3 : SA Eastern Standard Time, E. South America Standard Time
-2 : Mid:Atlantic Standard Time
-1 : Azores Standard Time, Cape Verde Standard Time
0 : Universal Coordinated Time, Greenwich Mean Time
1 : Romance Standard Time, Central Africa Standard Time, Central European Standard Time
2 : Egypt Standard Time, South Africa Standard Time, E. Europe Standard Time, FLE Standard Time, GTB Standard Time
3 : Arab Standard Time, E. Africa Standard Time, Arabic Standard Time, Russian Standard Time
3.5 : Iran Standard Time
4 : Arabian Standard Time, Caucasus Standard Time, Afghanistan Standard Time
5 : West Asia Standard Time
5.5 : India Standard Time
5.75 : Nepal Standard Time
6 : Central Asia Standard Time
6.5 : Myanmar Standard Time
7 : SE Asia Standard Time, North Asia Standard Time
8 : China Standard Time, W. Australia Standard Time, Singapore Standard Time, Taipei Standard Time, North Asia East Standard Time
9 : Tokyo Standard Time, Korea Standard Time, Yakutsk Standard Time
9.5 : AUS Central Standard Time, Cen. Australia Standard Time
10 : AUS Eastern Standard Time, E. Australia Standard Time
West Pacific Standard Time, Tasmania Standard Time, Vladivostok Standard Time
11 : Central Pacific Standard Time
12 : Fiji Standard Time, New Zealand Standard Time
13 : Tonga Standard Time

* How to use

   $layout = 
       Same function as date : http://uk2.php.net/manual/en/function.date.php
   $countryzone = 
       Country Zone from Above Eg: 0 ,for Greenwich Mean Time
   $daylightsaving = 
       Set true if the Country has daylight saving it will auto change. 
       Set false if the Country dose not have daylight saving or wish to it Disabled.
       (About Daylight Saving go here : http://www.timeanddate.com/time/aboutdst.html)
   Call Function:
       zonedate($layout, $countryzone, $daylightsaving);
   
   E.g.
   If GMT = Friday 25th of August 2006 10:23:17 AM
   When Function called:
   // West Asia Standard Time (Country Uses daylight saving)
       echo zonedate("l dS \of F Y h:i:s A", 5, true); 
   //Output : Friday 25th of August 2006 03:23:17 PM
*/
function zonedate($layout, $countryzone, $daylightsaving)
{
	if ($daylightsaving) {
		$daylight_saving = date('I');
		if ($daylight_saving)
           		$zone=3600*($countryzone+1);
           	else
           		$zone=3600*$countryzone; 
	}	
	else {
	    if ($countryzone>0)
		$zone=3600*$countryzone;
            else 
            	$zone=0;
	}
	$date1=gmdate($layout, time() + $zone);
	return $date1;
} 
?>