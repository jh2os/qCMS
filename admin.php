<?php
ini_set('display_errors',1); 
 error_reporting(E_ALL);
 
 $users = array(
	"admin" => "password",
	"jh2os" => "password"
 );
 
 $file = "index.php";

// This is where we display the data in the backend. If you want to extend these options, see the documentation
function displayFields($elementData) {
	switch($elementData["qcmsElement"]) {
		
		case "input":
			echo '<label for="'.$elementData["qcmsUrlTitle"].'"><strong>'.$elementData["qcmsTitle"].'</strong></label><br>';
			echo '<input type="text" name="'.$elementData["qcmsUrlTitle"].'" value="'.$elementData["tagContent"].'">';
			break;
		
		case "textarea":
			echo '<label for="'.$elementData["qcmsUrlTitle"].'"><strong>'.$elementData["qcmsTitle"].'</strong></label><br>';
			echo '<textarea  name="'.$elementData["qcmsUrlTitle"].'">'.$elementData["tagContent"].'</textarea>';
			break;
		
		case "ckedit":
			echo '<label for="'.$elementData["qcmsUrlTitle"].'"><strong>'.$elementData["qcmsTitle"].'</strong></label><br>';
			echo '<textarea  name="'.$elementData["qcmsUrlTitle"].'">'.$elementData["tagContent"].'</textarea>';
			break;
		
		case "link":
			echo '<label for="'.$elementData["qcmsUrlTitle"].'"><strong>'.$elementData["qcmsTitle"].'</strong></label><br>';
			echo '<label for="'.$elementData["qcmsUrlTitle"].'-href'.'">Location:</label>'; 
			echo '<input type="text" name="'.$elementData["qcmsUrlTitle"].'-href" value="'.$elementData["attributes"]["href"].'"><br>';
			echo '<label for="'.$elementData["qcmsUrlTitle"].'">Text:</label>';
			echo '<input type="text" name="'.$elementData["qcmsUrlTitle"].'" value="'.$elementData["tagContent"].'">';	
			break;
		
		case "image":
			echo '<label for="'.$elementData["qcmsUrlTitle"].'-src"><strong>'.$elementData["qcmsTitle"].'</strong></label>';
			echo '<input type="text" name="'.$elementData["qcmsUrlTitle"].'-src'.'" value="'.$elementData["attributes"]["src"].'">';		
			break;
	}
}

// Alright now the fun begins
session_start ();
$this_file = end(explode('/',__FILE__));
	
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
		$returnstring.=">".$tagPieces["tagContent"]."</".$tagPieces["element"].">";
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
	
	?>
	<html>
		<head>
			<meta name="viewport" content="width=device-width, initial-scale=1">
			<script src="./ckeditor/ckeditor.js"></script>
		</head>
		<body style="text-align:center;"><form method="GET" style="float:right;"><button name="logout" value="true">Log out</button></form>
			<h1>Edit your Page</h1>
			<?php
			if(isset($_GET['msg']) && $_GET['msg'] == 'success')
			{
				echo '<span style="color:green">Your page was saved!</span><br><br>';
			}
			?>
			<form method="POST" action="">
				<input type="hidden" name="qcms" value="<?php echo rand(10,100);?>">
				<?php
				foreach($data as $row)
				{
					echo "<hr><br>";
					displayFields($row);
					echo "<br><br>";
				}
				?><br><br><br><a target="_blank" href="<?php echo $file;?>"> View Current Page</a><br><br>
				<input type="submit" value="Save Page">
			</form>
			<script>
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
			<!--script src="./ckeditor/ckeditor.js"></script-->
		</head>
		<body style="text-align:center;">
			<form method="POST" action="<?php echo $this_file;?>">
				<input type="hidden" name="loggin" value="<?php echo rand(10,100);?>">
				<h2>Log in to edit your page</h2>
				<? if (isset($_POST['username'])) {?>
				<span style="color:red">The username or password you entered was incorrect</span><br>
				<? } ?>
				<label for="username"><strong>Username</stong></label><input name="username" id="username" type="text"><br>
				<label for="password"><strong>Password</stong></label><input name="password" id="password" type="password"><br>
				<input type="submit" value="Log in">
			</form>
		</body>
	</html>
<?}?>
