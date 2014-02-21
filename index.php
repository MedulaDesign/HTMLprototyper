<?php

require_once 'bootstrap.php';
autoload('Medula\HTMLprototyper\HTMLprototyper');

$HTMLprototyper = new Medula\HTMLprototyper\HTMLprototyper();

// Creación de nuevo proyecto
if (isset($_POST['projectName']))
{
	$projectName = $_POST['projectName'];
	$HTMLprototyper->newProject($projectName, $config['default_template']);
}

// Listado de proyectos
$projectsList = $HTMLprototyper->listProjects();

?>
<!doctype html>
<html lang="es">
<head>
	<meta charset="utf-8">
	<title>HTMLprototyper - <?=$config['company']?></title>
	<meta http-equiv="x-ua-compatible" content="ie=edge, chrome=1">
</head>
<body>
<form method="post">
	<input type="text" name="projectName" placeholder="Nombre proyecto">
	<input type="submit">
</form>
<h1><?=$lang['project_list']?></h1>
<div>
	<ul>
		<?php
			foreach ($projectsList as $project) {
				echo '<li><a href="' . $HTMLprototyper::$projectsFolder . '/' . $project['1'] . '">' . $project['0'] . '</a></li>';
			}
		?>
	</ul>
</div>
</body>
</html>
