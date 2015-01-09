<?php
//ini_set('display_errors',1); 
// error_reporting(E_ALL);
 
 $users = array(
	"user" => "password",
	"user2" => "password2"
 );
 
 $file = "test.php";

// This is where we display the data in the backend. If you want to extend these options, see the documentation
function displayFields($elementData) {
	switch($elementData["qcmsElement"]) {
		
		case "input":
			echo '<label for="'.$elementData["qcmsUrlTitle"].'"><h3>'.$elementData["qcmsTitle"].'</h3></label><br><hr><br>';
			echo '<input type="text" name="'.$elementData["qcmsUrlTitle"].'" value="'.$elementData["tagContent"].'">';
			break;
		
		case "textarea":
			echo '<label for="'.$elementData["qcmsUrlTitle"].'"><h3>'.$elementData["qcmsTitle"].'</h3></label><br><hr><br>';
			echo '<textarea  name="'.$elementData["qcmsUrlTitle"].'">'.$elementData["tagContent"].'</textarea>';
			break;
		
		case "ckedit":
			echo '<label for="'.$elementData["qcmsUrlTitle"].'"><h3>'.$elementData["qcmsTitle"].'</h3></label><br><hr><br>';
			echo '<textarea  name="'.$elementData["qcmsUrlTitle"].'">'.$elementData["tagContent"].'</textarea>';
			break;
		
		case "link":
			echo '<label for="'.$elementData["qcmsUrlTitle"].'"><h3>'.$elementData["qcmsTitle"].'</h3></label><br><hr><br>';
			echo '<label for="'.$elementData["qcmsUrlTitle"].'-href'.'">Location:</label><br>'; 
			echo '<input type="text" name="'.$elementData["qcmsUrlTitle"].'-href" value="'.$elementData["attributes"]["href"].'"><br><br>';
			echo '<label for="'.$elementData["qcmsUrlTitle"].'">Text:</label><br>';
			echo '<input type="text" name="'.$elementData["qcmsUrlTitle"].'" value="'.$elementData["tagContent"].'">';	
			break;
		
		case "image":
			echo '<label for="'.$elementData["qcmsUrlTitle"].'-src"><h3>'.$elementData["qcmsTitle"].'</h3></label><hr><br>';
			echo '<input type="text" name="'.$elementData["qcmsUrlTitle"].'-src'.'" value="'.$elementData["attributes"]["src"].'">';		
			break;
	}
}

// Alright now the fun begins
session_start ();
$filepath = __FILE__;
$filepatharray = explode('/',$filepath);
$this_file = end($filepatharray);
	
// Instance of logging out
if(isset($_GET['logout']) && $_GET['logout'] == 'true')
{
	$_SESSION['loggedin'] = '';
}
	
// Instance of logging in
if(isset($_POST['username']) && $_POST['username'] != '')
{
	if ( array_key_exists($_POST['username'], $users) && $_POST['password'] == $users[$_POST['username']])
	{
		$_SESSION['loggedin'] = 'true';
	}
	
}
if (isset($_SESSION['loggedin']) && ($_SESSION['loggedin'] == 'true'))
{
	
	if ($_GET['listfiles'] == 'yes') {
	
		$jsonfiles = array();
		$files = scandir('uploads');
	
		foreach($files as $file) {
			if ($file != "." && $file != ".."){
				$jsonfiles[] = array( "image" => "/uploads/".$file);
			}
		}
		header('Content-Type: application/json');
		echo json_encode($jsonfiles, JSON_UNESCAPED_SLASHES);
		exit();
	}

	if( isset( $_POST['resources'])){
		$returnstatus = '';
		$target_dir = "uploads/";
		$target_file = $target_dir . basename($_FILES["fileToUpload"]["name"]);
		$uploadOk = 1;
		$imageFileType = pathinfo($target_file,PATHINFO_EXTENSION);
		// Check if image file is a actual image or fake image
		if(isset($_POST["submit"])) {
		    $check = getimagesize($_FILES["fileToUpload"]["tmp_name"]);
		    if($check !== false) {
		        $uploadOk = 1;
		    } else {
		        $returnstatus .= "File is not an image.";
		        $uploadOk = 0;
		    }
		}
		// Check if file already exists
		if (file_exists($target_file)) {
		    $returnstatus .= "File already exists.\n";
		    $uploadOk = 0;
		}
		// Check file size
		if ($_FILES["fileToUpload"]["size"] > 1200000) {
		    $returnstatus .= "Your file is too large.\n";
		    $uploadOk = 0;
		}
		// Allow certain file formats
		if($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg"
		&& $imageFileType != "gif" ) {
		    $returnstatus .= "Only JPG, JPEG, PNG & GIF files are allowed.\n";
		    $uploadOk = 0;
		}
		// Check if $uploadOk is set to 0 by an error
		if ($uploadOk == 0) {
		    echo "Your file was not uploaded.\n";
			echo $returnstatus;
		// if everything is ok, try to upload file
		} else {
		    if (move_uploaded_file($_FILES["fileToUpload"]["tmp_name"], $target_file)) {
		        echo "The file ". basename( $_FILES["fileToUpload"]["name"]). " \nhas been uploaded!\n";
		    } else {
		        echo "Sorry, there was an error uploading your file.\n";
				echo $returnstatus;
		    }
		}
		exit();
	}
	
	$html =  file_get_contents($file);
	
	// Find all our dom elements with our qcms-* data element
	preg_match_all( '~<[^>]*?data-qcms-[^>]*>~i', $html, $match, PREG_OFFSET_CAPTURE);
	
	//var_dump($match[0]);
	
	$index = 0;
	$pieces = array();
	$data = array();
	$thing = array();
	$alldata = array();
	
	for ($a = 0; $a < count($match[0]); $a++) 
	{
		$row = $match[0][$a];
		//var_dump('0',$row[0],'1',$row[1]);
		// Get all the html after our matched element
	
		$pieces[] = substr($html, $index, $row[1] - $index);
		$tempstring = substr($html, $row[1]);
		// Get the element type h1, h1, p, a, img etc...
		$element = substr($tempstring, 1, strpos($tempstring,' ') -1 );
	
	
		// Check against all self closing elements (18 June 2014)
		if (!in_array($element, array(
									'area', 'base', 'br', 'col','command',
									'embed','hr','img','input','keygen',
									'link','meta','param','source','track',
									'wbr')))
		{
			
			// Declare some stuff
			$ends = array();
			$begins = array();
	
			// Find all the ending tags of that element in the remaining html
			$sPos = strlen($element);	
			while(($sPos = strpos($tempstring, "</".$element.">", $sPos)) !== false)
			{
				$ends [] = $sPos;
				$sPos += strlen($element);
			}
			
			// Find extra beginning tags in the remaining html
			$sPos = strlen($element);
			while(($sPos = strpos($tempstring, "<".$element, $sPos)) !== false)
			{
				$begins [] = $sPos;
				$sPos += strlen($element);
			}
		
			$begins[] = 0;
			$end = -1;
	
			// Check if more elements of the same type are in our data element
			for ($m = 0; $m < count($ends); $m++ )
			{
				if ( $ends[$m] < $begins[$m] )
				{
					$end = ($end == -1) ? $ends[$m] : $end;
				}
			}
		
			$end = ( $end == -1) ? $ends [(count($ends) -1)] : $end;
			
			$elementstring = substr($tempstring, 0, $end + strlen($element) + 3);
			$index = $end + $row[1] + strlen($element) + 3;
		} 
		
		else 
		
		{
			
			// else: these are our self closing tags
			$index = strlen($row[0]) + $row[1];
			$elementstring = $row[0];
			//var_dump($index);
		}
		
		$thing[] = array( 	"element" => $element,
							"elementString" => $elementstring);
	}
	
	$pieces[] = substr($html, $index);
	
	
	for($c = 0; $c < count($thing); $c++) //huray for c++
	{
		$qcmsElement = '';
		//lets get our attributes
		$attributeStringEnd = strpos($thing[$c]["elementString"], '>') + 1;
		$attributeString = substr($thing[ $c ]["elementString"],0,$attributeStringEnd);
		
		$rel = '/.*?((?:[a-z\-][a-z\-]+))\=([\'"].*?[\'"])/is';
		preg_match_all( $rel, $attributeString, $attribMatch);
		
		$attribs = array();
		for($d = 0; $d < count($attribMatch[1]); $d++) 
		{
			$attribs[ $attribMatch[1][$d] ] = substr($attribMatch[2][$d], 1, strlen($attribMatch[2][$d]) - 2);
			if (strpos($attribMatch[1][$d],'data-qcms-') !== false){
				$qcmsElement = substr($attribMatch[1][$d],10);
				$qcmsTitle = substr($attribMatch[2][$d],1,strlen($attribMatch[2][$d]) -2);
				$qcmsUrlTitle = preg_replace('/\\s+/', '-', $qcmsTitle);
			}
		}
		
		if (!in_array($thing[$c]["element"], array(
									'area', 'base', 'br', 'col','command',
									'embed','hr','img','input','keygen',
									'link','meta','param','source','track',
									'wbr')))
		{
			$endContent = strrpos( $thing[$c]["elementString"], '<');
			$tagContent = substr($thing[$c]["elementString"], $attributeStringEnd, $endContent - $attributeStringEnd); 
		}
		else
		{
			$tagContent = '';
		}
		
		$alldata[] =  $qcmsUrlTitle;
		
		$data[$qcmsUrlTitle] = array(
			"element" 		=> $thing[$c]["element"],
			"qcmsElement" 	=> $qcmsElement,// Something goes here
			"qcmsTitle" 	=> $qcmsTitle,
			"qcmsUrlTitle" 	=> $qcmsUrlTitle,
			"elementString" => $thing[$c]["elementString"],
			"attributes" 	=> $attribs,
			"tagContent" 	=> $tagContent
		);
		
	}
	
	function newData($data) {
	
		//We set up a duplicate data array to modify
		$tmpdata = $data;
			
		foreach( $_POST as $key => $value)
		{
			if ($key != 'qcms' )
			{
				if ( isset($tmpdata[$key]) )
				{
					$tmpdata[$key]['tagContent'] = $value;
				}
				else
				{
					$divider = strrpos( $key, '-');
					$attributeString = substr( $key, 0, $divider);
					$attributeType = substr($key, $divider + 1);
					  //var_dump( $attributeString, $attributeType);
					 $tmpdata[$attributeString]['attributes'][$attributeType] = $value;
				}
			}
		}
			
		return $tmpdata;
	}
	
	Function pieceTogether($tagPieces){
		$returnstring="";
		$returnstring .="<".$tagPieces["element"]." ";
		foreach($tagPieces["attributes"] as $key => $value){
			$returnstring.= $key.'="'.$value.'" ';
		}
		if ($tagPieces["element"] == "img"){
			$returnstring.=">";
		}
		else {
			$returnstring.=">".$tagPieces["tagContent"]."</".$tagPieces["element"].">";	
		}
		return $returnstring;
	}
	
	// we check to see if data has been posted
	if ( isset( $_POST['qcms'] ) ) 
	{
		$data = newData($data);
		$newhtml = '';
		for($b = 0; $b < count($alldata); $b++) {
			$newhtml .= $pieces[$b];
			$newhtml .= pieceTogether($data[ $alldata[$b] ]);
		}
		$newhtml .= end($pieces);
		
		file_put_contents($file,$newhtml);
	}
	
	$color1 = "#ECECEA";
	$color2 = "#424242";
	$color3 = "#74AFAD";
	$color4 = "#585858";
	$color5 = "#D9853B";
	
	?>
	<html>
		<head>
			<meta name="viewport" content="width=device-width, initial-scale=1">
			<script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.3/jquery.min.js"></script>
			<script src="./ckeditor/ckeditor.js"></script>
			<script src="http://malsup.github.com/jquery.form.js"></script>
			<link rel="stylesheet" href="http://yui.yahooapis.com/pure/0.5.0/base-min.css">
			<link href='http://fonts.googleapis.com/css?family=Roboto' rel='stylesheet' type='text/css'>
			<link href='http://fonts.googleapis.com/css?family=Bree+Serif' rel='stylesheet' type='text/css'>
			<style>
			html, body {
				width: 100%;
				max-width: 100%;
				margin: 0;
				padding: 0;
				background-color: <?=$color1?>;/*#ECECEA;*/
				color: <?=$color2?>;/*#424242;*/
			}
			h2, h3, h4, label{
				color: #558C89;
				font-family: 'Roboto', sans-serif;
				font-family: 'Bree Serif', serif;
			}
			h1 {
				margin: 0;
				font-size: 5em;
				font-family: 'Bree Serif', serif;
			}
			h2 {
				font-size: .75em;
			}
			#main {
				max-width: 900px;
				margin: auto;
				margin-top: 0;
				background-color: <?=$color1;?>;/*#ECECEA;*/

			}
			#header {
				margin-top: 0;
				background-color: <?=$color3?>;/*#74AFAD;*/
				color: <?=$color1;?>;/*#ECECEA;*/
			}
			#header-buttons {
				margin-top: 0;
				margin-bottom: 40px;
				padding-top: 1em;
				background-color: <?=$color4?>;/*#558C89;*/
				-webkit-box-shadow: 0px 6px 40px 5px rgba(0,0,0,0.25);
				-moz-box-shadow: 0px 6px 40px 5px rgba(0,0,0,0.25);
				box-shadow: 0px 6px 40px 5px rgba(0,0,0,0.25);
			}
			#footer-buttons {
				margin-top: 0;
				padding-top: 1em;
				background-color: <?=$color4?>;/*#558C89;*/
			}
			#footer-space {
				margin-top: 0;
				padding-top: 2em;
				background-color: <?=$color3?>;/*#74AFAD;*/
			}
			#main-center {
				padding-top: 1em;
				padding: 1em;
				max-width: 800px;
				margin: auto;
				background-color: <?=$color1;?>;/*#ECECEA;*/
			}
			.material {
				width: 94%;
				padding: 1em;
				padding-bottom: 2em;
				margin: auto;
				background-color: white;
				-webkit-box-shadow: 2px 2px 12px 0px rgba(50, 50, 50, 0.3);
				-moz-box-shadow:    2px 2px 12px 0px rgba(50, 50, 50, 0.3);
				box-shadow:         2px 2px 12px 0px rgba(50, 50, 50, 0.3);
			}
			textarea, .cke {
				width: 100%;
				 max-width: 800px;
				 margin-left: auto !important;
				 margin-right: auto !important;
			 }
			 .cke_reset {
				 margin: auto;
			 }
			 a {
				 background-color: <?=$color5?>;/*#D9853B;*/
				 color: <?=$color1;?>;/*#ECECEA;*/
				 border: none;
				 text-decoration: none;
				 padding: 0.5em;
				 -webkit-border-radius: 5px;
				 -moz-border-radius: 5px;
				 border-radius: 5px;
				 -webkit-box-shadow: 0px 0px 15px 0px rgba(0,0,0,0.1);
				 -moz-box-shadow: 0px 0px 15px 0px rgba(0,0,0,0.1);
				 box-shadow: 0px 0px 15px 0px rgba(0,0,0,0.1);
			 }
			 a:visited {
				 background-color: <?=$color5?>;/*#D9853B;*/
				 color: #FFF;
			 }
			 hr {
			 }
			 button {
				 background-color: <?=$color5?>;/*#D9853B;*/
				 color: #FFF;/*#E9E9E9;*/
				 border: none;
				 padding: 0.5em;
				 -webkit-border-radius: 5px;
				 -moz-border-radius: 5px;
				 border-radius: 5px;
				 -webkit-box-shadow: 0px 0px 15px 0px rgba(0,0,0,0.1);
				 -moz-box-shadow: 0px 0px 15px 0px rgba(0,0,0,0.1);
				 box-shadow: 0px 0px 15px 0px rgba(0,0,0,0.1);
			 }
			 #resourceupload {
			 }
			 .clear {
				 clear: both;
			 }
			 label {
				 /*background-color: #424242;
				 color: #FF9900;*/
				 margin-bottom: 1em;
			 }
			</style>
		</head>
		<body style="text-align:center;">
			<div id="header">
				<h1>Qcms</h1>				<br>
			</div>
			<div id="header-buttons">
				<form method="GET" style="float:right;margin-right: 1em;"><button name="logout" value="true" class="pure-button">Log out</button></form>	
				<form><button id="AddImage" class="pure-button">Upload Images</button></form>
				<form action="" method="post" enctype="multipart/form-data" style="display:none;" id="resourceupload" class="pure-form">
					<input type="hidden" name="resources" value="1">
					<input type="file" name="fileToUpload" id="fileToUpload"><br><br>
					<button type="submit" name="submit" class="pure-form">Upload Image</button>
				</form>
									<div class="clear"></div><br>
			</div>
			<form method="POST" action="" class="pure-form pure-form-stacked">
			<div id="main">
				<div id="main-center">
					<?php
					if(isset($_GET['msg']) && $_GET['msg'] == 'success')
					{
						echo '<span style="color:green">Your page was saved!</span><br><br>';
					}
					?>
						<input type="hidden" name="qcms" value="<?php echo rand(10,100);?>">
						<?php
						foreach($data as $row)
						{
							echo "<div class='material'>";
							displayFields($row);
							echo "</div><br>";
						}
						?><br>
				</div>
			</div>
			</form>
			<div id="footer-buttons">
				<br>
				<a target="_blank" class="pure-button pure-button-primary" href="<?php echo $file;?>"> View Current Page</a>
				<br>
				<br>
				<button type="submit" value="Save Page" class="pure-button pure-button-primary pure-button-active">Save Page</button>
				<br><br><br>
			</div>
			<div id="footer-space">
				<br><br><br><br>
			</div>
			<script>
		 	CKEDITOR.env.isCompatible = true;
		    $(document).ready(function() { 
				$('#resourceupload').ajaxForm({success: showResponse});
				$('#AddImage').on('click', function(e){
					e.preventDefault();
					$('#resourceupload').show();
					$(this).hide();
				});
		    });
			function showResponse(responseText, statusText, xhr, $form) {
				alert(responseText);
			}
			
			<?php
			foreach($data as $row)
			{
				if ($row['qcmsElement'] == "ckedit")
				{
					echo "CKEDITOR.replace( '".$row['qcmsUrlTitle']."' );";
				}
			}?>
			</script>
		</body>
	</html>
<?} else {?>
	<html>
		<head>
			<meta name="viewport" content="width=device-width, initial-scale=1">
			<link rel="stylesheet" href="http://yui.yahooapis.com/pure/0.5.0/base-min.css">
			<!--script src="./ckeditor/ckeditor.js"></script-->
		</head>
		<body style="text-align:center;">
			<form method="POST" action="<?php echo $this_file;?>">
				<input type="hidden" name="loggin" value="<?php echo rand(10,100);?>"><br><br>
				<h2>qCMS</h2><br>
				<h5>An easy lightweight content management system</h5>
				<? if (isset($_POST['username'])) {?>
				<span style="color:red">The username or password you entered was incorrect</span><br>
				<? } ?>
				<br><br>
				<label for="username"><strong>Username: </stong></label><input name="username" id="username" type="text"><br><br>
				<label for="password"><strong>Password: </stong></label><input name="password" id="password" type="password"><br><br><br>
				<input type="submit" value="Log in">
			</form>
		</body>
	</html>
<?}?>
