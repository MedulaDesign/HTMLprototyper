<?php namespace Medula\HTMLprototyper;

class HTMLprototyper
{
    public static $projectsFolder = 'projects';
    private $projectsList = 'projects.txt';
    private $tempaltesFolder = 'templates';
    private $config;

    public function __construct()
    {
        // Revisamos que el directorio de proyectos exista
        if (!is_dir($this->projectsFolder)) {
            throw new \Exception("Projects folder (/$this->projectsFolder) does not exists");
        }
        // Revisamos si el directorio de proyectos tiene permisos de escritura
        if (!is_writable($this->projectsFolder)) {
            throw new \Exception("Projects folder (/$this->projectsFolder) is not writeable");
        }
        // Obtenemos la configuración para que la clase pueda tener acceso a ésta
        global $config;
        $this->config = $config;
    }

    public function newProject($projectName, $templateFile)
    {
        // En primera instancia debemos revisar que el template enviado exista
        if (is_file($this->tempaltesFolder. '/' . $templateFile)) {
            // Obtenemos el nombre del nuevo directorio
            $projectFolder = $this->folderName($projectName);
            // Creamos el directorio
            mkdir($this->projectsFolder . '/' . $projectFolder);
            // Guardamos la relacion del nombre del proyecto con el directorio
            $this->addProjecToList($projectName, $projectFolder);
            // Creamos un archivo meta-data dentro del proyecto
            $this->newProjectMetaData($projectFolder);
            // Creamos el index.html en base al template definido
            $this->createFromTemplate($templateFile, $projectName, $projectFolder, 'index.html');
        } else {
            throw new \Exception("Template file $templateFile does not exists");
        }
    }

    private function folderName($projectName)
    {
        return sha1($projectName . microtime());
    }

    private function createFromTemplate($templateFile, $projectName, $projectFolder, $fileName)
    {
        $template = new \SPLFileObject($this->tempaltesFolder . '/'. $templateFile, 'r');
        $content = '';
        while (!$template->eof()) {
            $content .= $template->fgets();
        }
        $content = str_replace('{project}', $projectName . ' - ' . $this->config['company'], $content);
        $content = str_replace('{foundation_path}', '../../foundation/' . $this->config['foundation_version'], $content);
        $newFile = new \SPLFileObject($this->projectsFolder . '/'. $projectFolder . '/' . $fileName, 'x');
        $newFile->fwrite($content . PHP_EOL);
    }

    private function newProjectMetaData($projectFolder)
    {
        $metaData = new \SPLFileObject($this->projectsFolder . '/'. $projectFolder . '/meta.txt', 'a');
        $metaData->fwrite('index.html' . PHP_EOL);
        $metaData->fwrite('Created:' . date('Y-m-d h:i'). PHP_EOL);
        $metaData->fwrite('Modified:' . date('Y-m-d h:i'). PHP_EOL);
        $metaData->fwrite('-' . PHP_EOL);
    }

    private function addProjecToList($projectName, $projectFolder)
    {
        $projectsList = new \SPLFileObject($this->projectsFolder . '/' . $this->projectsList, 'a');
        $projectsList->fwrite($projectName . '::' . $projectFolder . PHP_EOL);
    }

    public function listProjects()
    {
        $projects = array();
        $projectsList = new \SPLFileObject($this->projectsFolder . '/' . $this->projectsList, 'a+');
        foreach ($projectsList as $line) {
            if (!$projectsList->eof()) {
                $projects[] = explode('::', trim($line));
            }
        }
        return $projects;
    }
}
