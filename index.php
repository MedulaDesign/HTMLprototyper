<?php

require_once 'bootstrap.php';
autoload('Medula\HTMLprototyper\HTMLprototyper');

$HTMLprototyper = new Medula\HTMLprototyper\HTMLprototyper();

if (isset($_POST['projectName']))
{
	$projectName = $_POST['projectName'];
	$HTMLprototyper->newProject($projectName, 'template.html');
}

?>
<!doctype html>
<html lang="es">
<head>
	<meta charset="utf-8">
	<title>HTMLprototyper - <?=$config['company']?></title>
	<meta http-equiv="x-ua-compatible" content="ie=edge, chrome=1" />
</head>
<body>
<form method="post">
	<input type="text" name="projectName" placeholder="Nombre proyecto">
	<input type="submit">
</form>
<div>
	<ul>

	</ul>
</div>
</body>
</html>