<?php

// Todas las consultas deben venir vía AJAX
if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) AND strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
	require_once 'bootstrap.php';
	autoload('Medula\HTMLprototyper\HTMLprototyper');
	autoload('Medula\HTMLprototyper\Project');
	$projectsFolder = Medula\HTMLprototyper\HTMLprototyper::$projectsFolder;
	// Datos para la barra
	if (isset($_GET['bar'])) {
		// Buscamos en el REFERER el hash del directorio del proyecto
		preg_match('/\b[0-9a-f]{40}\b/', $_SERVER['HTTP_REFERER'], $projectFolder);
		if (count($projectFolder) > 0) {
			$projectFolder = $projectFolder[0];
			// Revisamos que el proyecto exista
			if (is_dir($projectsFolder . '/' . $projectFolder)) {
				$project = new Medula\HTMLprototyper\Project($projectFolder);
				$data = array(
					'files' => $project->getFiles(),
					'lang' => $lang
				);
				echo json_encode($data);
			}
		}
	}
}
