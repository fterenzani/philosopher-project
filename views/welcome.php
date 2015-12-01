<?php
$title = 'Welcome'; 
?>

<div class="container">

	<h1><?php echo 'Welcome' ?></h1>

	<?php echo _url() ?><br>
	<?php echo url('home') ?><br>

	<?php echo _path() ?><br>
	<?php echo path('home') ?><br>


</div>

<?php require 'views/base.php' ?>