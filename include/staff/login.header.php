<?php
defined( 'OSTSCPINC' ) or die( 'Invalid path' );
?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
	<head>
		<meta http-equiv="content-type" content="text/html; charset=utf-8" />
		<meta http-equiv="refresh" content="7200" />
		<title><?php echo __( 'Agent Login' ); ?></title>
		<link rel="stylesheet" href="css/login.css" type="text/css" />
		<link type="text/css" rel="stylesheet" href="<?php echo ROOT_PATH; ?>css/font-awesome.min.css?8b927a0"/>
		<meta name="robots" content="noindex" />
		<meta http-equiv="cache-control" content="no-cache" />
		<meta http-equiv="pragma" content="no-cache" />
		<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0"/>
		<link rel="shortcut icon" href="http://www.take01.com.br/wp-content/uploads/2015/08/icon.png"/>
		<link rel="shortcut icon" href="http://www.take01.com.br/wp-content/uploads/2015/02/ico.ico"/>
		<link rel="apple-touch-icon" href="http://www.take01.com.br/wp-content/uploads/2015/08/icon-small.png"/>
		<link rel="apple-touch-icon" sizes="72x72" href="http://www.take01.com.br/wp-content/uploads/2015/08/icon-medium.png"/>
		<link rel="apple-touch-icon" sizes="114x114" href="http://www.take01.com.br/wp-content/uploads/2015/08/icon-large.png"/>
		<script type="text/javascript" src="<?php echo ROOT_PATH; ?>js/jquery-1.8.3.min.js?8b927a0"></script>
		<script type="text/javascript">
            $(document).ready(function () {
                $("input:not(.dp):visible:enabled:first").focus();
            });
		</script>
	</head>
	<body id="loginBody">

