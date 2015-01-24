<?
/*
##############################################################################
# PLEASE DO NOT REMOVE THIS HEADER!!!
#
# COPYRIGHT NOTICE
#
# FormMail.php v5.0
# Copyright 2000-2004 Ai Graphics and Joe Lumbroso (c) All rights reserved.
# Created 07/06/2000   Last Modified 10/28/2003
# Joseph Lumbroso, http://www.aigraphics.com, http://www.dtheatre.com
#                  http://www.dtheatre.com/scripts/
##############################################################################
#
# This cannot and will not be inforced but I would appreciate a link back
# to any of these sites:
# http://www.dtheatre.com
# http://www.aigraphics.com
# http://www.dtheatre.com/scripts/
#
# THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
# IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
# FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL
# THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR
# OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE,
# ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR
# OTHER DEALINGS IN THE SOFTWARE.
#
##############################################################################
*/

//KON - for compatibility with $email... my form uses Email with capital E
$Email = $_POST['Email'];//$Email;
$Message = $_POST['Message'];
$Phone = $_POST['Phone'];
$FormType = $_POST['FormType'];
$Name = $_POST['Name'];
$env_report = $_POST['env_report'];
$sort = $_POST['sort_items'];

/////TRACK THE CLIENT WHEN MAIL IS SENT
include("www_misc/contact_res/client_track.php");

$filename = 'www_misc/formmail.txt';  //the file you're writing the results to

//Get IP Address

//get all the IP addresses.. might not work since IPs keep changing
$ip_array = getIP();
$content = "----------------------NEW MESSAGE-------------------------\n\n";
if (strcmp(zonedate("D", -5, true), "Mon")==0) { //just put a newline to separate weeks
	if (file_exists($filename)) {
   		if (strcmp("Mon", date("D", filemtime($filename)))!=0) 
   			$content .= "\n";
	}
}
	   
foreach ( $ip_array as $ip_s ) {
	if( $ip_s!="" ){
	       $content .= $ip_s." - ";
	 }
}
	
//-5 because it is an eastern time zone
$content .= "Date: ". zonedate("m/d/Y - g:i:s A", -5, true)." -- "; //date('m/d/Y - G:i:s')." -- ";
//$content .= "BROWSER: ". $_SERVER['HTTP_USER_AGENT'];
$content .= "EMAIL: ". $Email . " -- NAME: " . $Name . " -- PHONE: " . $_POST['Phone'] . " -- FORM TYPE: " . $FormType . " -- MESSAGE: " . $Message;

//open the file and write the IP address and time into it
$myfile = fopen ($filename,"a");
flock ($myfile, LOCK_EX);
fwrite ($myfile, "$content\n");
flock ($myfile, LOCK_UN);
fclose ($myfile); 

///////END CLIENT TRACK


// for ultimate security, use this instead of using the form
$recipient = "dan@amplified.us"; // youremail@domain.com
$sender_email_because_some_hosts_block_email = "Contact Form <dan@amplified.us>";

// bcc emails (separate multiples with commas (,))
$bcc = "";

// referers.. domains/ips that you will allow forms to
// reside on.
$referers = array ('amplified.us','www.amplified.us');

// banned emails, these will be email addresses of people
// who are blocked from using the script (requested)
$banlist = array ('*@somedomain.com', 'user@domain.com', 'etc@domains.com');

// field / value seperator
define("SEPARATOR", ($separator)?$separator:": ");

// content newline
define("NEWLINE", ($newline)?$newline:"\n");

// formmail version (for debugging mostly)
define("VERSION", "5.0");


// our mighty error function..
function print_error($reason,$type = 0) {
   build_body($title, $bgcolor, $text_color, $link_color, $vlink_color, $alink_color, $style_sheet);
   // for missing required data
   if ($type == "missing") {
      if ($missing_field_redirect) {
         header("Location: $missing_field_redirect?error=$reason");
         exit;
      } else {
      ?>
      The form was not submitted for the following reasons:<p>
      <ul><?
      echo $reason."\n";
      ?></ul>
      Please use your browser's back button to return to the form and try again.<?
      }
   } else { // every other error
      ?>
      The form was not submitted because of the following reasons:<p>
      <?
   }
   echo "<br>\n";
//   echo "<small>This form is powered by <a href=\"http://www.dtheatre.com/scripts/\">Jack's Formmail.php ".VERSION."</a></small>\n\n";
   exit;
}

// function to check the banlist
// suggested by a whole lot of people.. Thanks
function check_banlist($banlist, $Email) {
   if (count($banlist)) {
      $allow = true;
      foreach($banlist as $banned) {
         $temp = explode("@", $banned);
         if ($temp[0] == "*") {
            $temp2 = explode("@", $Email);
            if (trim(strtolower($temp2[1])) == trim(strtolower($temp[1])))
               $allow = false;
         } else {
            if (trim(strtolower($Email)) == trim(strtolower($banned)))
               $allow = false;
         }
      }
   }
   if (!$allow) {
      print_error("You are using from a <b>banned email address.</b>");
   }
}

// function to check the referer for security reasons.
// contributed by some one who's name got lost.. Thanks
// goes out to him any way.
function check_referer($referers) {
   if (count($referers)) {
      $found = false;

      $temp = explode("/",getenv("HTTP_REFERER"));
      $referer = $temp[2];
      
      if ($referer=="") {$referer = $_SERVER['HTTP_REFERER'];
         list($remove,$stuff)=split('//',$referer,2);
         list($home,$stuff)=split('/',$stuff,2);
         $referer = $home;
      }
      
      for ($x=0; $x < count($referers); $x++) {
         if (eregi ($referers[$x], $referer)) {
            $found = true;
         }
      }
      if ($referer =="")
         $found = false;
      if (!$found){
         print_error("You are coming from an <b>unauthorized domain.</b>");
         error_log("[FormMail.php] Illegal Referer. (".getenv("HTTP_REFERER").")", 0);
      }
         return $found;
      } else {
         return true; // not a good idea, if empty, it will allow it.
   }
}
if ($referers)
   check_referer($referers);

if ($banlist)
   check_banlist($banlist, $Email);

// This function takes the sorts, excludes certain keys and 
// makes a pretty content string.
function parse_form($array, $sort = "") {
   // build reserved keyword array
   $reserved_keys[] = "MAX_FILE_SIZE";
   $reserved_keys[] = "required";
   $reserved_keys[] = "redirect";
   $reserved_keys[] = "require";
   $reserved_keys[] = "path_to_file";
   $reserved_keys[] = "recipient";
   $reserved_keys[] = "subject";
   $reserved_keys[] = "sort_items";
   $reserved_keys[] = "style_sheet";
   $reserved_keys[] = "bgcolor";
   $reserved_keys[] = "text_color";
   $reserved_keys[] = "link_color";
   $reserved_keys[] = "vlink_color";
   $reserved_keys[] = "alink_color";
   $reserved_keys[] = "title";
   $reserved_keys[] = "missing_fields_redirect";
   $reserved_keys[] = "env_report";
   $reserved_keys[] = "submit";
   if (count($array)) {
      if (is_array($sort)) {
         foreach ($sort as $field) {
            $reserved_violation = 0;
            for ($ri=0; $ri<count($reserved_keys); $ri++)
               if ($array[$field] == $reserved_keys[$ri]) $reserved_violation = 1;

            if ($reserved_violation != 1) {
               if (is_array($array[$field])) {
                  for ($z=0;$z<count($array[$field]);$z++)
                     $content .= $field.SEPARATOR.$array[$field][$z].NEWLINE;
               } else
                  $content .= $field.SEPARATOR.$array[$field].NEWLINE;
            }
         }
      }
      while (list($key, $val) = each($array)) {
         $reserved_violation = 0;
         for ($ri=0; $ri<count($reserved_keys); $ri++)
            if ($key == $reserved_keys[$ri]) $reserved_violation = 1;

         for ($ri=0; $ri<count($sort); $ri++)
            if ($key == $sort[$ri]) $reserved_violation = 1;

         // prepare content
         if ($reserved_violation != 1) {
            if (is_array($val)) {
               for ($z=0;$z<count($val);$z++)
                  $content .= $key.SEPARATOR.$val[$z].NEWLINE;
            } else
               $content .= $key.SEPARATOR.$val.NEWLINE;
         }
      }
   }
   return $content;
}

// mail the content we figure out in the following steps
function mail_it($content, $subject, $Email, $recipient) {
   global $attachment_chunk, $attachment_name, $attachment_type, $attachment_sent, $bcc, $sender_email_because_some_hosts_block_email;

   $ob = "----=_OuterBoundary_000";
   $ib = "----=_InnerBoundery_001";
   
   $headers  = "MIME-Version: 1.0\r\n"; 
   $headers .= "From: ".$sender_email_because_some_hosts_block_email."\n"; 
   $headers .= "To: ".$recipient."\n"; 
   $headers .= "Reply-To: ".$Email."\n";
   if ($bcc) $headers .= "Bcc: ".$bcc."\n"; 
   $headers .= "X-Priority: 1\n"; 
   $headers .= "X-Mailer: DT Formmail".VERSION."\n"; 
   $headers .= "Content-Type: multipart/mixed;\n\tboundary=\"".$ob."\"\n";
   
          
   $message  = "This is a multi-part message in MIME format.\n";
   $message .= "\n--".$ob."\n";
   $message .= "Content-Type: multipart/alternative;\n\tboundary=\"".$ib."\"\n\n";
   $message .= "\n--".$ib."\n";
   $message .= "Content-Type: text/plain;\n\tcharset=\"iso-8859-1\"\n";
   $message .= "Content-Transfer-Encoding: quoted-printable\n\n";
   $message .= $content."\n\n";
   $message .= "\n--".$ib."--\n";
//KON MOD - NO FRIGGIN ATTACHMENTS... EVER
/*   if ($attachment_name && !$attachment_sent) {
      $message .= "\n--".$ob."\n";
      $message .= "Content-Type: $attachment_type;\n\tname=\"".$attachment_name."\"\n";
      $message .= "Content-Transfer-Encoding: base64\n";
      $message .= "Content-Disposition: attachment;\n\tfilename=\"".$attachment_name."\"\n\n";
      $message .= $attachment_chunk;
      $message .= "\n\n";
      $attachment_sent = 1;
   }
*/
   $message .= "\n--".$ob."--\n";
   
 //  mail($recipient, $subject, $message, $headers);
	
   if (@mail($recipient, $subject, $message, $headers)) {	
	   if ($redirect) {
	   	header("Location: $redirect");
	        exit;
	   } else {
	   	echo "Thank you for your message.<br>We will reply to your request within 12 hours.";
	   }	
   } else {
   	   echo "Error: Mail could not be sent. Please contact us through regular <a href=\"javascript:var e1='&#116;a%73tu%64%69o.%63&#111;&#109;',e2='&#109;&#097;&#105;&#108;&#116;&#111;&#058;%20', e3='i&#110;%66&#111;';var e0=e2+e3+'%40'+e1;(window.location?window.location.replace(e0):document.write(e0));\">&#069;&#045;&#077;&#097;&#105;&#108;</a>.\n";
   }
}

// take in the body building arguments and build the body tag for page display
function build_body($title, $bgcolor, $text_color, $link_color, $vlink_color, $alink_color, $style_sheet) {
   if ($style_sheet)
      echo "<LINK rel=STYLESHEET href=\"$style_sheet\" Type=\"text/css\">\n";
   if ($title)
      echo "<title>$title</title>\n";
   if (!$bgcolor)
      $bgcolor = "#FFFFFF";
   if (!$text_color)
      $text_color = "#000000";
   if (!$link_color)
      $link_color = "#0000FF";
   if (!$vlink_color)
      $vlink_color = "#FF0000";
   if (!$alink_color)
      $alink_color = "#000088";
   if ($background)
      $background = "background=\"$background\"";
   echo "<body bgcolor=\"$bgcolor\" text=\"$text_color\" link=\"$link_color\" vlink=\"$vlink_color\" alink=\"$alink_color\" $background>\n\n";
}

// check for a recipient email address and check the validity of it
// Thanks to Bradley miller (bradmiller@accesszone.com) for pointing
// out the need for multiple recipient checking and providing the code.
$recipient_in = split(',',$recipient);
for ($i=0;$i<count($recipient_in);$i++) {
   $recipient_to_test = trim($recipient_in[$i]);
   if (!eregi("^[_\\.0-9a-z-]+@([0-9a-z][0-9a-z-]+\\.)+[a-z]{2,6}$", $recipient_to_test)) {
      print_error("<b>I NEED VALID RECIPIENT EMAIL ADDRESS ($recipient_to_test) TO CONTINUE</b>");
   }
}

// This is because I originally had it require but too many people
// were used to Matt's Formmail.pl which used required instead.
if ($required)
   $require = $required;
// handle the required fields
if ($require) {
   // seperate at the commas
   $require = ereg_replace( " +", "", $require);
   $required = split(",",$require);
   for ($i=0;$i<count($required);$i++) {
      $string = trim($required[$i]);
      // check if they exsist
      if((!(${$string})) || (!(${$string}))) {
         // if the missing_fields_redirect option is on: redirect them
         if ($missing_fields_redirect) {
            header ("Location: $missing_fields_redirect");
            exit;
         }
         $require;
         $missing_field_list .= "<b>Missing: $required[$i]</b><br>\n";
      }
   }
   // send error to our mighty error function
   if ($missing_field_list)
      print_error($missing_field_list,"missing");
}

//KON MOD - DO NOT ALLOW HTML IN MESSAGE. SPAMMERS USE IT
if ($Message) {
   if (stristr($Message, "</a>") != FALSE || stristr($Message, "/url") != FALSE || stristr($Message, "rankings") != FALSE || (stristr($Message, "marketing") != FALSE && (stristr($Message, "traffic") != FALSE || stristr($Message, "internet") != FALSE)) )
      print_error("HTML code and marketing SPAM are not allowed in the message.");
}
else
   print_error("Please fill in the message field and try again.");

/*
if ($Phone) {
   if (stristr($Phone, "12345") != FALSE || strlen($Phone) <= 6)
      print_error("Please provide a valid phone number.");
}
*/

//KON MOD - ALREADY CHECK THIS WITH JAVASCRIPT
/*
// check the email fields for validity
if (($email) || ($EMAIL)) {
   $email = trim($email);
   if ($EMAIL) $email = trim($EMAIL);
   if (!eregi("^[_\.0-9a-z-]+@([0-9a-z][0-9a-z-]+\.)+[a-z]{2,6}$", $email))
      print_error("your <b>email address</b> is invalid");
   $EMAIL = $email;
}
*/
//KON MOD - WHAT FREAKING ZIP CODE
/*
// check zipcodes for validity
if (($ZIP_CODE) || ($zip_code)) {
   $zip_code = trim($zip_code);
   if ($ZIP_CODE) $zip_code = trim($ZIP_CODE);
   if (!ereg("(^[0-9]{5})-([0-9]{4}$)", trim($zip_code)) && (!ereg("^[a-zA-Z][0-9][a-zA-Z][[:space:]][0-9][a-zA-Z][0-9]$", trim($zip_code))) && (!ereg("(^[0-9]{5})", trim($zip_code))))
      print_error("your <b>zip/postal code</b> is invalid");
}
*/

//KON MOD - NO PHONE CHECK - What if European phone?
/*
// check phone for validity
if (($PHONE_NO) || ($phone_no)) {
   $phone_no = trim($phone_no);
   if ($PHONE_NO) $phone_no = trim($PHONE_NO);
   if (!ereg("(^(.*)[0-9]{3})(.*)([0-9]{3})(.*)([0-9]{4}$)", $phone_no))
      print_error("your <b>phone number</b> is invalid");
}
*/
//KON MOD - NO PHONE CHECK - What if European phone?
/*
// check phone for validity
if (($FAX_NO) || ($fax_no)) {
   $fax_no = trim($fax_no);
   if ($FAX_NO) $fax_no = trim($FAX_NO);
   if (!ereg("(^(.*)[0-9]{3})(.*)([0-9]{3})(.*)([0-9]{4}$)", $fax_no))
      print_error("your <b>fax number</b> is invalid");
}
*/
// sort alphabetic or prepare an order
if ($sort == "alphabetic") {
   uksort($_POST, "strnatcasecmp");
} elseif ((ereg('^order:.*,.*', $sort)) && ($list = explode(',', ereg_replace('^order:', '', $sort)))) {
   $sort = $list;
}
   
// prepare the content
$content = parse_form($_POST, $sort);

//KON MOD - NO NEED FOR FILES, HACKABLE
/*
// check for an attachment if there is a file upload it
if ($attachment_name) {
   if ($attachment_size > 0) {
      if (!$attachment_type) $attachment_type =  "application/unknown";
      $content .= "Attached File: ".$attachment_name."\n";
      $fp = fopen($attachment,  "r");
      $attachment_chunk = fread($fp, filesize($attachment));
      $attachment_chunk = base64_encode($attachment_chunk);
      $attachment_chunk = chunk_split($attachment_chunk);
   }
}
*/
//KON MOD - NO NEED FOR FILES, HACKABLE
/*
// check for a file if there is a file upload it
if ($file_name) {
   if ($file_size > 0) {
      if (!ereg("/$", $path_to_file))
         $path_to_file = $path_to_file."/";
      $location = $path_to_file.$file_name;
      if (file_exists($path_to_file.$file_name))
         $location = $path_to_file.rand(1000,3000).".".$file_name;
      copy($file,$location);
      unlink($file);
      $content .= "Uploaded File: ".$location."\n";
   }
}
*/
//KON MOD - NO NEED FOR FILES, HACKABLE
/*
// second file (see manual for instructions on how to add more.)
if ($file2_name) {
   if ($file_size > 0) {
      if (!ereg("/$", $path_to_file))
         $path_to_file = $path_to_file."/";
      $location = $path_to_file.$file2_name;
      if (file_exists($path_to_file.$file2_name))
         $location = $path_to_file.rand(1000,3000).".".$file2_name;
      copy($file2,$location);
      unlink($file2);
      $content .= "Uploaded File: ".$location."\n";
   }
}
*/

// if the env_report option is on: get eviromental variables
if ($env_report) {
   $env_report = ereg_replace( " +", "", $env_report);
   $env_reports = split(",",$env_report);
   $content .= "\n------ eviromental variables ------\n";
   for ($i=0;$i<count($env_reports);$i++) {
      $string = trim($env_reports[$i]);
      if ($env_reports[$i] == "REMOTE_HOST")
         $content .= "REMOTE HOST: ". $_SERVER['REMOTE_HOST']."\n";
      if ($env_reports[$i] == "REMOTE_USER")
         $content .= "REMOTE USER: ". $_SERVER['REMOTE_USER']."\n";
      if ($env_reports[$i] == "REMOTE_ADDR")
         $content .= "REMOTE ADDR: ". $_SERVER['REMOTE_ADDR']."\n";
      if ($env_reports[$i] == "HTTP_USER_AGENT")
         $content .= "BROWSER: ". $_SERVER['HTTP_USER_AGENT']."\n";
   }
}

// send it off
mail_it(stripslashes($content), ($FormType)?stripslashes($FormType)." - ".$Name:"Form Submission", $Email, $recipient);
//KON MOD
/*
if (file_exists($ar_file)) {
   $fd = fopen($ar_file, "rb");
   $ar_message = fread($fd, filesize($ar_file));
   fclose($fd);
   mail_it($ar_message, ($ar_subject)?stripslashes($ar_subject):"RE: Form Submission", ($ar_from)?$ar_from:$recipient, $email);
}
*/
// if the redirect option is set: redirect them
/*if ($redirect) {
   header("Location: $redirect");
   exit;
} else {
   echo "Thank you for your submission.<br>We will reply to your request within 24 hours.\n";
}
*/
// <----------    THE END    ----------> //  