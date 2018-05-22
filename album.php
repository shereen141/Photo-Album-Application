<?php

// display all errors on the browser
error_reporting(E_ALL);
ini_set('display_errors','On');
?>

<!--
Developed By: Shereen Hasan
UTA ID:				1001437130
Project:			Dropbox API
-->

<html>
	<head>
		<meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="">

<!-- Bootstrap CSS -->
		<script src="https://code.jquery.com/jquery-3.2.1.slim.min.js" integrity="sha384-KJ3o2DKtIkvYIK3UENzmM7KCkRr/rE9/Qpg6aAZGJwFDMVNA/GpGFF93hXpG5KkN" crossorigin="anonymous"></script>
		<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.3/umd/popper.min.js" integrity="sha384-vFJXuSJphROIrBnz7yo7oB41mKfc8JzQZiCq4NCceLEaO4IHwicKwpJf9c9IpFgh" crossorigin="anonymous"></script>
		<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-beta.2/js/bootstrap.min.js" integrity="sha384-alpBpkh1PFOepccYVYDB4do5UnbKysX5WZXm3XxPqe5iKTfUKjNkCk9SaVuEZflJ" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-beta.2/css/bootstrap.min.css" integrity="sha384-PsH8R72JQ3SOdhVi3uxftmaW6Vc51MKb0q5P2rRUpPvrszuE4W1povHYgTpBfshb" crossorigin="anonymous">

    <link rel="icon" type="image/png" href="images/icon.png">
    <title>ImageDrop</title>

	</head>
	<body>
		<nav class="navbar navbar-light bg-light justify-content-between">
  <a class="navbar-brand" href="#">
    <img src="images/icon.png" width="10%" height="7%" class="d-inline-block align-top" alt="">
    <h1 class="text-muted">Welcome to ImageDrop</h1>

  </a>
</nav>
<?php
/**
 * DropPHP Demo
 *
 * http://fabi.me/en/php-projects/dropphp-dropbox-api-client/
 *
 * @author     Fabian Schlieper <fabian@fabi.me>
 * @copyright  Fabian Schlieper 2012
 * @version    1.1
 * @license    See license.txt
 *
 */


require_once 'demo-lib.php';
//demo_init(); // this just enables nicer output

// if there are many files in your Dropbox it can take some time, so disable the max. execution time
set_time_limit( 0 );

require_once 'DropboxClient.php';

/** you have to create an app at @see https://www.dropbox.com/developers/apps and enter details below: */
/** @noinspection SpellCheckingInspection */
$dropbox = new DropboxClient( array(
	'app_key' => "c8sc59wx0l4b6xm",      // Put your Dropbox API key here
	'app_secret' => "b9ugfhuqs0yas1v",   // Put your Dropbox API secret here
	'app_full_access' => false,
) );


/**
 * Dropbox will redirect the user here
 * @var string $return_url
 */
$return_url = "https://" . $_SERVER['HTTP_HOST'] . $_SERVER['SCRIPT_NAME'] . "?auth_redirect=1";

// first, try to load existing access token
$bearer_token = demo_token_load( "bearer" );

if ( $bearer_token ) {
	$dropbox->SetBearerToken( $bearer_token );
	// echo "loaded bearer token: " . json_encode( $bearer_token, JSON_PRETTY_PRINT ) . "\n";
} elseif ( ! empty( $_GET['auth_redirect'] ) ) // are we coming from dropbox's auth page?
{
	// get & store bearer token
	$bearer_token = $dropbox->GetBearerToken( null, $return_url );
	demo_store_token( $bearer_token, "bearer" );
} elseif ( ! $dropbox->IsAuthorized() ) {
	// redirect user to Dropbox auth page
	$auth_url = $dropbox->BuildAuthorizeUrl( $return_url );
	die( "Authentication required. <a href='$auth_url'>Continue.</a>" );
}
?>

	<div class="container-fluid">
		<center>
			<div class="jumbotron">
			<form method="POST" action="<?php echo $_SERVER['PHP_SELF']; ?>" enctype="multipart/form-data">
				<h3>Upload Files to Dropbox</h3>
				<fieldset>
					<legend>Choose Files: </legend>
					<label><b>Upload: </b></label>
					<input type="file" name="img_file" id="img_file"/><br/>
					<input type="submit" class="btn btn-primary" name="submit" id="submit"/>
          <input type="reset" class="btn btn-danger" name="reset" id="reset"/>
				</fieldset>
			</form>
			</div>
		</center>

<?php
	if (isset($_FILES['img_file']['tmp_name'])) {
		$img_upload = $_FILES['img_file']['tmp_name'];
		$img_name = $_FILES['img_file']['name'];
		$dropbox->UploadFile($img_upload,$img_name);
		$file = $dropbox->GetFiles("",false);
	}

	if (isset($_POST['img_download'])) {
		$files = $dropbox->GetFiles("",false);

		foreach ($files as $key => $file) {
			$destination = "downloaded_".basename($file->path);
			if ((string)$file->path == (string)$_POST['img_download']) {
				$dropbox->DownloadFile($file,$destination);
			}
		}
	}


	if (isset($_POST['img_delete'])) {
		$files = $dropbox->GetFiles("",false);

		foreach ($files as $img_name => $file) {
			if ((string)$file->path == (string)$_POST["img_delete"]) {
				$dropbox->Delete($file);
			}
		}
	}
  $img_fetch = array();
	$files = $dropbox->GetFiles("",false);
  if (!empty($files)) {
 ?>

 <br/><br/>
 <h3>Uploaded Files:</h3>
		<table class="table" border=1 >
<?php

	foreach ($files as $img_name => $file) {
		$img_fetch = $dropbox->GetLink($file,false);
    $img_data = base64_encode( $dropbox->GetThumbnail( $file->path ));

?>

			<tr>
        <td><img src="data:image/jpeg;base64,<?php echo $img_data; ?>" alt="Generating PDF thumbnail failed!"/></td>
				<td><a href="<?php echo $img_fetch; ?>" onclick ="showImg('<?php echo $img_fetch; ?>')"> <?php echo $img_name;?></a></td>
			<!-- <form method="post" action="album.php">

				<td><input type="submit" value="Download"/></td>
				<input type="hidden" value="" name="img_download" />
			</form> -->
			<form method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>">
				<td><input type="submit" value="Delete"/></td>
				<input type="hidden" value="<?php echo $file->path;?>" name="img_delete" />
			</form>
		</tr>

<?php
    }
?>
	</table>

<br/><br/>
<h3>Current Image: </h3>

<?php
$jpg_files = $dropbox->Search( "/", ".jpg", 5 );
if (!empty( $jpg_files ) ) {
  $jpg_file = reset( $jpg_files );
  $img_url = $dropbox->GetLink($jpg_file);
  list($url,$querystring) = explode('?', $img_url, 2);

  echo "\n\n<i>$jpg_file->name:</i>\n";
  $img_data = base64_encode( $dropbox->GetThumbnail( $jpg_file->path ) );
  echo "<img src=\"data:image/jpeg;base64,$img_data\" alt=\"Generating PDF thumbnail failed!\" style=\"border: 1px solid black;\" class='img-responsive thumbnail' data-toggle='modal' data-target='#myModal'/>";
?>
<br/><br/>
<div class='modal fade' id='myModal' tabindex=-1 role='dialog'><div class='modal-dialog' role='document' style='width:33%; height:auto;'>
<div class='modal-content'><div class='modal-header'>
<button type='button' class='close' data-dismiss='modal' aria-label='close'><span aria-hidden = 'true'>&times;</span></button>
<h4 class='modal-title'><?php echo $jpg_file->name ?></h4></div>
<div class='modal-body'><img src='<?php echo $url ?>?raw=1' style='height:auto;width:95%;'></div>
<div class='modal-footer'><button type='button' class='btn btn-default' data-dismiss='modal'>Close</button></div>
</div></div></div>

<?php
  }
}
?>
</div>

<footer class="footer">
      <div class="container-fluid">
        <p class="text-muted navbar-right"><br/>&copy;&nbsp;Shereen Hasan ID:1001437130 &nbsp;&nbsp;</p>
      </div>
    </footer>
	</body>
  <style>

  .navbar-static-top {
    margin-bottom: 20px;
  }

  html {
    position: relative;
    min-height: 100%;
  }
  body {
    /* Margin bottom by footer height */
    margin-bottom: 50px;
  }
  .footer {
    position: absolute;
    bottom: 0;
    width: 100%;
    /* Set the fixed height of the footer here */
    height: 50px;
    background-color: #f5f5f5;
  }
  </style>
</html>
