<?php namespace Medula\HTMLprototyper;

class Project
{
	public $projectFolder;
	public $projectMetaData;

	public function __construct($projectFolder)
	{
		$this->projectFolder = $projectFolder;
		$this->projectMetaData();
	}

	/**
	 * Obtiene la lista de archivos dentro del proyecto
	 * @return array Lista de archivos
	 */
	public function getFiles()
	{
		$files = glob(HTMLprototyper::$projectsFolder . '/' . $this->projectFolder .'/*.html');
		foreach ($files as $key => $file) {
			$files[$key] = basename($file);
		}
		return $files;
	}

	/**
	 * Crea un nuevo archivo en el proyecto
	 * @param  string $fileName Nombre del archivo nuevo
	 * @return void
	 */
	public function newFile($fileName, $templateFile)
	{
		$HTMLprototyper = new HTMLprototyper();
		$error = false;
		$msg = '';
		// Si el nombre del archivo es valido y la plantilla existe
		// procedemos a crear el archivo
		if ($this->validateNewFile($fileName, $templateFile)) {
			$fileName .= '.html';
			// Creamos el archivo
			$HTMLprototyper->createFromTemplate($templateFile, $this->projectMetaData['projectName'], $this->projectFolder, $fileName, $this->projectMetaData['foundationVersion']);
			// Lo agregamos al meta.txt
			$HTMLprototyper->newFileMetaData($fileName, $this->projectFolder);
		} else {
			$error = true;
			$msg = $HTMLprototyper->lang['js_new_file_error'];
		}
		return array('error' => $error, 'msg' => $msg);
	}

	/**
	 * Copia un archivo
	 * @param  string $fileName Nombre del archivo a copiar
	 * @param  string $copyName Nombre del archivo a crear
	 * @return void
	 */
	public function copyFile($fileName, $copyName)
	{
		$HTMLprototyper = new HTMLprototyper();
	}

	/**
	 * Valida que el nombre de archivo sea correcto
	 * y que la plantilla exista
	 * @param  string $fileName Nombre del archivo
	 * @return boolean
	 */
	private function validateNewFile($fileName, $templateFile)
	{
		// No puede estar vacío y solo se permiten numeros y letras
		if (trim($fileName) !== '' and preg_match('/^[a-z0-9\-\_]+$/i', $fileName) > 0) {
			// Revisamos que el archivo no exista
			if (!is_file(HTMLprototyper::$projectsFolder . '/' . $this->projectFolder .'/'. $fileName .'.html')) {
				// Revisamos que la plantilla exista
				if (is_file(HTMLprototyper::$tempaltesFolder . '/' . $templateFile)) {
					return true;
				}
			}
		}
		return false;
	}

	/**
	 * Obtiene datos del archio meta.txt
	 * @return void
	 */
	private function projectMetaData()
	{
		$metaData = array();
        $fileMeta = new \SPLFileObject(HTMLprototyper::$projectsFolder . '/' . $this->projectFolder .'/meta.txt', 'a+');
        $lineNumber = 1; $fileCount = 0;
        foreach ($fileMeta as $line) {
            if (!$fileMeta->eof()) {
            	// Nombre del proyecto
            	if ($lineNumber === 1) {
            		$projectName = explode(':', $line);
            		$metaData['projectName'] = trim($projectName[1]);
            	// Versión de Foundation
            	} elseif ($lineNumber === 2) {
            		$foundationVersion = explode(':', $line);
            		$metaData['foundationVersion'] = trim($foundationVersion[1]);
            	// Archivos
            	} else {
            		// Si es multiplo de 4 significa que corresponde
            		// al nombre de un archivo de acuerdo a la estructura
            		// del archivo meta.txt
            		if ($lineNumber % 4 === 0) {
            			$metaData['files'][$fileCount][] = trim($line);
            		// Si no es multiplo de cuatro, corresponde a un separador
            		// de archivos (-) o fechas, mientras no sea un separador
            		// guardamos ambas fechas
            		} elseif (trim($line) !== '-') {
            			$line = trim(str_replace('Created:', '', str_replace('Modified:', '', $line)));
            			$metaData['files'][$fileCount][] = $line;
            		// Es un separador de archivos (-), aumentamos el contador
            		// de archivos
            		} else {
            			$fileCount++;
            		}
            	}
            	$lineNumber++;
            }
        }
        $this->projectMetaData = $metaData;
	}
}
