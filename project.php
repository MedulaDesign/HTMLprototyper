<?php

// Todas las consultas deben venir vía AJAX
if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) AND strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
	require_once 'bootstrap.php';
	autoload('Medula\HTMLprototyper\HTMLprototyper');
	autoload('Medula\HTMLprototyper\Project');
	$projectsFolder = Medula\HTMLprototyper\HTMLprototyper::$projectsFolder;
	// Buscamos en el REFERER el hash del directorio del proyecto
	preg_match('/\b[0-9a-f]{40}\b/', $_SERVER['HTTP_REFERER'], $projectFolder);
	if (count($projectFolder) > 0) {
		$projectFolder = $projectFolder[0];
		// Revisamos que el proyecto exista
		if (is_dir($projectsFolder . '/' . $projectFolder)) {
			$project = new Medula\HTMLprototyper\Project($projectFolder);
		}
	}
	// El proyecto existe
	if (isset($project)) {
		// Datos para la barra
		if (isset($_GET['bar'])) {
			$data = array(
				'files' => $project->getFiles(),
				'lang' => $lang,
				'metadata' => $project->projectMetaData
			);
			echo json_encode($data);
		// Crear nuevo archivo dentro del proyecto
		} elseif (isset($_GET['newFile']) and isset($_GET['fileName'])) {
			echo json_encode($project->newFile($_GET['fileName'], $_GET['template']));
		// Listado de templates disponibles
		} elseif (isset($_GET['templates'])) {
			$HTMLprototyper = new Medula\HTMLprototyper\HTMLprototyper();
			$data = array(
				'templates' => $HTMLprototyper->listTemplates()
			);
			echo json_encode($data);
		}
	}
}
