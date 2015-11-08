<?php
$content = ob_get_clean();
header("Content-Type: text/html; charset=UTF-8");
ob_start();
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- The above 3 meta tags *must* come first in the head; any other head content must come *after* these tags -->

  	<!-- Latest compiled and minified CSS -->
  	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/css/bootstrap.min.css">

    <title><?php echo @$title ?></title>
    
    <?php if (!empty($description)): ?>
      <meta name="description" content="<?php echo $description ?>">
    <?php endif ?>
    
    <?php if (!empty($canonical)): ?>
      <link rel="canonical" href="<?php echo $canonical ?>">
    <?php endif ?>

    <?php if (@$metaRobots): ?>
      <meta name="robots" content="<?php echo $metaRobots ?>">
    <?php endif ?>

    <?php if (@$hreflangs): ?>
      <?php foreach ($hreflangs as $locale => $url): ?>
        <link rel="alternate" hreflang="<?php echo $locale ?>" href="<?php echo $url ?>">
      <?php endforeach ?>
    <?php endif ?>

    <!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
      <script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
      <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->

  </head>
  <body class="<?php echo @$class ?>">

    <?php echo $content ?>

    <!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js"></script>
  	<!-- Latest compiled and minified JavaScript -->
  	<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/js/bootstrap.min.js"></script>

  </body>
</html>
<?php echo IS_DEV? ob_get_clean(): preg_replace("#\s{2,}#", "\n", ob_get_clean()); 