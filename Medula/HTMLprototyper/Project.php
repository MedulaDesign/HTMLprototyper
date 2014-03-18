<?php namespace Medula\HTMLprototyper;

class Project
{
    public $projectFolder;
    public $projectMetaData;
    private $newFileRegex = '/^[a-z0-9\-\_]+$/i';

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
     * @return array
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
     * @return array
     */
    public function copyFile($fileName, $newFileName)
    {
        $HTMLprototyper = new HTMLprototyper();
        $error = false;
        $msg = '';
        if ($this->validateCopyFile($fileName, $newFileName)) {
            $newFileName .= '.html';
            $projectFolder = $HTMLprototyper::$projectsFolder . '/' . $this->projectFolder . '/';
            copy($projectFolder . $fileName, $projectFolder . $newFileName);
            // Lo agregamos al meta.txt
            $HTMLprototyper->newFileMetaData($newFileName, $this->projectFolder);
        } else {
            $error = true;
            $msg = $HTMLprototyper->lang['js_copy_file_error'];
        }
        return array('error' => $error, 'msg' => $msg);
    }

    /**
     * Guarda el contenido del archivo
     * @param  string $fileName Nombre del archivo
     * @param  string $html     Contenido del archivo
     * @return array
     */
    public function saveFile($fileName, $html)
    {
        /**
         * Si 'magic_quotes_gpc' esta activo, agrega backslashes para
         * escapar comillas y backslashes, hay que removarlos
         */
        if (function_exists('get_magic_quotes_gpc') && get_magic_quotes_gpc()) {
            $html = stripslashes($html);
        }
        $file = new \SPLFileObject(HTMLprototyper::$projectsFolder . '/' . $this->projectFolder . '/' . $fileName, 'w');
        $file->fwrite($html . PHP_EOL);
        // Actualizamos el meta-data
        $metaData = array('Modified' => date('Y-m-d h:i'));
        $this->updateFileMetaData($fileName, $metaData);
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
        if (trim($fileName) !== '' and preg_match($this->newFileRegex, $fileName) > 0) {
            // Revisamos que el archivo no exista
            if (!is_file(HTMLprototyper::$projectsFolder . '/' . $this->projectFolder .'/'. $fileName . '.html')) {
                // Revisamos que la plantilla exista
                if (is_file(HTMLprototyper::$templatesFolder . '/' . $templateFile)) {
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * Valida que el archivo a copiar exista, que el nombre
     * del nuevo archivo sea valido y que no exista
     * @param  string $fileName Nombre del archivo a copiar
     * @param  string $newFileName Nombre del nuevo archivo
     * @return boolean
     */
    private function validateCopyFile($fileName, $newFileName)
    {
        // Revisamos que el archivo a copiar exista
        if (is_file(HTMLprototyper::$projectsFolder . '/' . $this->projectFolder .'/'. $fileName)) {
            if (trim($newFileName) !== '' and preg_match($this->newFileRegex, $newFileName) > 0) {
                // Revisamos que el archivo no exista
                if (!is_file(HTMLprototyper::$projectsFolder . '/' . $this->projectFolder .'/'. $newFileName . '.html')) {
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
        $fileMeta = new \SPLFileObject(HTMLprototyper::$projectsFolder . '/' . $this->projectFolder . '/meta.txt', 'a+');
        $lineNumber = 1;
        $fileCount = 0;
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

    /**
     * Actualiza el meta-data de un archivo
     * @param  string $fileName Nombre del archivo
     * @param  array $metaData  Meta-data del archivo a modificar
     * @return void
     */
    private function updateFileMetaData($fileName, $metaData)
    {
        $fileMeta = new \SPLFileObject(HTMLprototyper::$projectsFolder . '/' . $this->projectFolder . '/meta.txt', 'a+');
        $fileMetaTemp = new \SplTempFileObject();
        $lineNumber = 1;
        $isFile = false;
        foreach ($fileMeta as $line) {
            if (!$fileMeta->eof()) {
                // Marcamos un flag cuando identificamos al archivo
                if ($lineNumber % 4 === 0 and trim($line) === $fileName) {
                    $isFile = true;
                // Estamos dentro del archivo mientras la linea no contenga '-'
                } elseif ($isFile and trim($line) !== '-'){
                    $data = explode(':', $line);
                    $metaKey = trim($data[0]);
                    if (array_key_exists($metaKey, $metaData)) {
                        $line = $metaKey . ':' . $metaData[$metaKey] . PHP_EOL;
                    }
                // Estamos fuera del archivo
                } else {
                  $isFile = false;
              }
              $lineNumber++;
          }
          $fileMetaTemp->fwrite($line);
      }
        // Re-escribirmos meta.txt con los cambios que tenemos en temp
      $fileMeta->seek(0);
      $fileMeta->ftruncate(0);
      foreach ($fileMetaTemp as $line) {
          $fileMeta->fwrite($line);
      }
  }
}
