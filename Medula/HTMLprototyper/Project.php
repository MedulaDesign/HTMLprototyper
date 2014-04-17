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
            // Lo agregamos al log.txt
            $HTMLprototyper->newProjectLog($this->projectFolder, $this->projectMetaData['projectName'], $HTMLprototyper->lang['log_new_file'], array('file_name' => $fileName));
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
            // Lo agregamos al log.txt
            $HTMLprototyper->newProjectLog($this->projectFolder, $this->projectMetaData['projectName'], $HTMLprototyper->lang['log_copy_file'], array('file_name' => $newFileName, 'copy_name' => $fileName));
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
        $HTMLprototyper = new HTMLprototyper();
        /**
         * Si 'magic_quotes_gpc' esta activo (que agrega backslashes para
         * escapar comillas y backslashes) hay que removarlos
         */
        if (function_exists('get_magic_quotes_gpc') && get_magic_quotes_gpc()) {
            $html = stripslashes($html);
        }
        $file = new \SPLFileObject(HTMLprototyper::$projectsFolder . '/' . $this->projectFolder . '/' . $fileName, 'w');
        $file->fwrite($html . PHP_EOL);
        // Actualizamos el meta-data
        $modifiedDate = date('Y-m-d h:i');
        $metaData = array('Modified' => $modifiedDate);
        $this->updateFileMetaData($fileName, $metaData);
        // Lo agregamos al log.txt
        $HTMLprototyper->newProjectLog($this->projectFolder, $this->projectMetaData['projectName'], $HTMLprototyper->lang['log_save_file'], array('file_name' => $fileName));
        // Retornamos la nueva fecha de modificación
        $modifiedDate = new \DateTime($modifiedDate);
        return $modifiedDate->format($HTMLprototyper->config['date_format']);
    }

    /**
     * Elimina un archivo del proyecto
     * @param  string $fileName Nombre del archivo
     * @return array
     */
    public function deleteFile($fileName)
    {
        $HTMLprototyper = new HTMLprototyper();
        $error = false;
        $msg = '';
        // Si el es distinto al index.html (no se puede borrar) y existe
        if ($fileName !== 'index.html' and is_file(HTMLprototyper::$projectsFolder . '/' . $this->projectFolder .'/'. $fileName)) {
            // Eliminamos el archivo
            unlink(HTMLprototyper::$projectsFolder . '/' . $this->projectFolder .'/'. $fileName);
            // Lo eliminamos del meta.txt
            $this->deleteFileMetaData($fileName);
            // Lo agregamos al log.txt
            $HTMLprototyper->newProjectLog($this->projectFolder, $this->projectMetaData['projectName'], $HTMLprototyper->lang['log_delete_file'], array('file_name' => $fileName));
        } else {
            $error = true;
            $msg = $HTMLprototyper->lang['js_delete_file_error'];;
        }
        return array('error' => $error, 'msg' => $msg);
    }

    /**
     * Elimina el proyecto por completo, incluyendo todas las carpetas
     * que pueden estar dentro
     * @return void
     */
    public function deleteProject($dir = null)
    {
        // Si el directorio está nulo, ocupamos la raíz del proyecto
        if (is_null($dir)) {
            $dir = HTMLprototyper::$projectsFolder . '/' . $this->projectFolder;
        }
        // Recorremos el directorio eliminando todos los archivos para luego
        // poder eliminarlo
        $files = glob($dir .'/*');
        foreach ($files as $key => $file) {
            // Si es un directorio, lo recorremos recursivamente
            if (is_dir($file)) {
                $this->deleteProject($file);
            } else {
                // Eliminamos el archivo
                unlink($file);
            }
        }
        rmdir($dir);
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
        $HTMLprototyper = new HTMLprototyper();
        $metaData = array();
        $fileMeta = new \SPLFileObject(HTMLprototyper::$projectsFolder . '/' . $this->projectFolder . '/meta.txt', 'a+');
        $lineNumber = 1;
        $fileCount = 0;
        foreach ($fileMeta as $line) {
            if (!$fileMeta->eof()) {
                $line = trim($line);
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
                        // Damos formato a las fechas
                        if (strpos($line, 'Created:') !== false) {
                            $date = str_replace('Created:', '', $line);
                        } else {
                            $date = str_replace('Modified:', '', $line);
                        }
                        // Por si la fecha no tiene un formato correcto y fue
                        // modificada manualmente en el archivo meta.txt
                        try {
                            $date = new \DateTime($date);
                        } catch(Exception $e) {
                            $date = new \DateTime('00-00-00 00:00');
                        }
                        $line = $date->format($HTMLprototyper->config['date_format']);
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
     * Actualiza el meta-data de un archivo, ya sea cambiando los datos
     * o modificando el meta-data enviado.
     * Por ejemplo, si deseo modificar la fecha de modificación del
     * archivo index.html, $metaData debe contener la key correspondiende
     * y el nuevo valor:
     *     array('Modified' => '2014-03-03 03:03:03')
     * @param  string $fileName Nombre del archivo
     * @param  array $metaData  Meta-data del archivo a modificar
     * @return void
     */
    private function updateFileMetaData($fileName, $metaData)
    {
        $fileMeta = new \SPLFileObject(HTMLprototyper::$projectsFolder . '/' . $this->projectFolder . '/meta.txt', 'a+');
        $fileMetaTemp = new \SplTempFileObject(); // Nuevo archivo meta.txt
        $lineNumber = 1;
        $isFile = false;
        foreach ($fileMeta as $line) {
            if (!$fileMeta->eof()) {
                $line_ = trim($line);
                // Marcamos un flag cuando identificamos al archivo
                if ($lineNumber % 4 === 0 and $line_ === $fileName) {
                    $isFile = true;
                // Estamos dentro del archivo mientras la linea no contenga '-'
                } elseif ($isFile and $line_ !== '-') {
                    $data = explode(':', $line_);
                    $metaKey = trim($data[0]);
                    if (array_key_exists($metaKey, $metaData)) {
                        $line_ = $metaKey . ':' . $metaData[$metaKey] . PHP_EOL;
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

    /**
     * Elimina del meta-data el archivo enviado
     * @param  string $fileName Nombre del archivo
     * @return void
     */
    private function deleteFileMetaData($fileName)
    {
        $fileMeta = new \SPLFileObject(HTMLprototyper::$projectsFolder . '/' . $this->projectFolder . '/meta.txt', 'a+');
        $fileMetaTemp = new \SplTempFileObject(); // Nuevo archivo meta.txt
        $lineNumber = 0; // Posición donde parte el archivo
        // Buscamos la posición del archivo
        foreach ($fileMeta as $line) {
            if (!$fileMeta->eof()) {
                $line = trim($line);
                // Identificamos la línea donde parte los meta-datos del archivo
                // Debe ser la línea - 1 debido a que tenemos que remover también
                // el caracater '-' que separa los archivos
                if ($line === $fileName) {
                    $lineNumber = $fileMeta->key() - 1;
                    break;
                }
            }
        }
        // Recorremos nuevamenta el meta.txt y no tomaremos en cuenta
        // los datos del archivo a remover. Todo siempre y cuando
        // se haya encontrado el archivo anteriormente
        if ($lineNumber > 0) {
            $fileMeta->seek(0); // Partimos en la 1era linea
            $isFile = false;
            foreach ($fileMeta as $line) {
                if (!$fileMeta->eof()) {
                    $line_ = trim($line);
                    // Identificamos donde parte el archivo
                    if ($fileMeta->key() === $lineNumber) {
                        $isFile = true;
                    // Si la linea no contiene '-' o no está vacia (fin del documento)
                    // significa que son datos que deben ser agregados el nuevo meta.txt
                    // y significa que ya salimos de los meta-datos del archivo a eliminar
                    // por lo tanto dejamos $isFile en false
                    } elseif (!($isFile and $line_ !== '-' and $line_ !== '')) {
                        $isFile = false;
                        $fileMetaTemp->fwrite($line);
                    }
                }
            }
        }
        // Re-escribirmos meta.txt con los cambios que tenemos en temp
        $fileMeta->seek(0);
        $fileMeta->ftruncate(0);
        foreach ($fileMetaTemp as $line) {
            $fileMeta->fwrite($line);
        }
    }
}
