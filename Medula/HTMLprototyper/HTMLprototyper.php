<?php namespace Medula\HTMLprototyper;

class HTMLprototyper
{
    public static $projectsFolder = 'projects';
    public static $templatesFolder = 'templates';
    public $config;
    public $lang;

    public function __construct()
    {
        // Revisamos que el directorio de proyectos exista
        if (!is_dir($this::$projectsFolder)) {
            throw new \Exception("Projects folder (/$this::$projectsFolder) does not exists");
        }
        // Revisamos si el directorio de proyectos tiene permisos de escritura
        if (!is_writable($this::$projectsFolder)) {
            throw new \Exception("Projects folder (/$this::$projectsFolder) is not writeable");
        }
        // Obtenemos la configuración para que la clase pueda tener acceso a ésta
        global $config;
        $this->config = $config;
        // Lo mismo con el idioma
        global $lang;
        $this->lang = $lang;
        // Habria que cambiar lo de config y lang, queda muy acoplado a la clase
        // para más adelante :P
    }

    /**
     * Crea un nuevo proyecto
     * @param  string $projectName  Nombre del proyecto
     * @param  string $templateFile Nombre del archivo de plantilla
     * @return string Carpeta del proyecto
     */
    public function newProject($projectName, $templateFile = null)
    {
        // Si no se envia una plantilla a utilizar, ocupamos la por defecto
        if (is_null($templateFile)) {
            $templateFile = $this->config['default_template'];
        }
        // En primera instancia debemos revisar que el template enviado exista
        if (is_file($this::$templatesFolder. '/' . $templateFile)) {
            // Obtenemos el nombre del nuevo directorio
            $projectFolder = $this->folderName($projectName);
            // Creamos el directorio
            mkdir($this::$projectsFolder . '/' . $projectFolder);
            // Creamos un archivo meta-data dentro del proyecto
            $this->newProjectMetaData($projectFolder, $projectName);
            // Creamos al log.txt
            $this->newProjectLog($projectFolder, $projectName, $this->lang['log_project_created']);
            // Creamos el index.html en base al template definido
            $this->createFromTemplate($templateFile, $projectName, $projectFolder, 'index.html');
            // Enviamos el correo con los datos de acceso
            $this->sendEmail($projectName, $projectFolder);
        } else {
            throw new \Exception("Template file $templateFile does not exists");
        }
        return $projectFolder;
    }

    /**
     * Genera el nombre de la carpeta en donde se crea el proyecto
     * @param  string $projectName Nombre del proyecto
     * @return string              Carpeta del proyecto
     */
    private function folderName($projectName)
    {
        return sha1($projectName . microtime());
    }

    /**
     * Crea un proyecto desde una plantilla definida
     * @param  string $templateFile  Nombre de la plantilla
     * @param  string $projectName   Nombre del proyecto
     * @param  string $projectFolder Carpeta del proyecto
     * @param  string $fileName      Nombre del archivo a crear
     * @return void
     */
    public function createFromTemplate($templateFile, $projectName, $projectFolder, $fileName, $foundationVersion = null)
    {
        // Obtenemos el contenido de la plantilla
        $template = new \SPLFileObject($this::$templatesFolder . '/'. $templateFile, 'r');
        $content = '';
        while (!$template->eof()) {
            $content .= $template->fgets();
        }
        // Versión de Foundation a utilizar
        if (is_null($foundationVersion)) { // Si no se envia, utilizamos la de la config
            $foundationVersion = $this->config['foundation_version'];
        }
        // Remplazamos los placeholders
        $content = str_replace('{project}', $projectName . ' - ' . $this->config['company'], $content);
        $content = str_replace('{foundation_path}', '../../foundation/' . $foundationVersion, $content);
        $newFile = new \SPLFileObject($this::$projectsFolder . '/'. $projectFolder . '/' . $fileName, 'x');
        // Escribimos en el arhcivo el contenido
        $newFile->fwrite($content . PHP_EOL);
    }

    /**
     * Crea el archivo con meta-data sobre el proyecto
     * Este archivo contiene información sobre fechas de
     * creación y edición, además de la versión de Foundation
     * utilizada
     * @param  string $projectFolder Carpeta del proyecto
     * @return void
     */
    private function newProjectMetaData($projectFolder, $projectName)
    {
        $metaData = new \SPLFileObject($this::$projectsFolder . '/'. $projectFolder . '/meta.txt', 'a');
        $metaData->fwrite('Project: ' . $projectName . PHP_EOL);
        $metaData->fwrite('Foundation: ' . $this->config['foundation_version'] . PHP_EOL);
        $metaData->fwrite('-' . PHP_EOL);
        $metaData->fwrite('index.html' . PHP_EOL);
        $metaData->fwrite('Created:' . date('Y-m-d H:i'). PHP_EOL);
        $metaData->fwrite('Modified:' . date('Y-m-d H:i'). PHP_EOL);
    }

    /**
     * Agrega un nuevo archivo al meta-data
     * @param  string $fileName      Nombre del nuevo archivo
     * @param  string $projectFolder Directorio del proyecto
     * @return void
     */
    public function newFileMetaData($fileName, $projectFolder)
    {
        $metaData = new \SPLFileObject($this::$projectsFolder . '/'. $projectFolder . '/meta.txt', 'a');
        $metaData->fwrite('-' . PHP_EOL);
        $metaData->fwrite($fileName . PHP_EOL);
        $metaData->fwrite('Created:' . date('Y-m-d H:i'). PHP_EOL);
        $metaData->fwrite('Modified:' . date('Y-m-d H:i'). PHP_EOL);
    }

    /**
     * Agrega un registro de acción al proyecto
     * @param  string $projectFolder Directorio del proyecto
     * @param  string $projectName   Nombre del proyecto
     * @param  string $msg           Mensaje
     * @return void
     */
    public function newProjectLog($projectFolder, $projectName, $msg, $placeholders = array())
    {
        $metaData = new \SPLFileObject($this::$projectsFolder . '/'. $projectFolder . '/log.txt', 'a');
        // Remplazamos los placeholders si existen
        foreach ($placeholders as $key => $placeholder) {
            $msg = str_replace('{' . $key . '}', $placeholder, $msg);
        }
        $metaData->fwrite(date('Y-m-d H:i') . ' ['.$_SERVER['REMOTE_ADDR'].'] - ' . $msg . PHP_EOL);
    }

    /**
     * Lista los proyectos activos, recorriendo las carpetas
     * y obteniendo los nombres desde el meta.txt
     * @return array Lista de proyectos
     */
    public function listProjects()
    {
        $projects = array();
        $files = glob($this::$projectsFolder . '/*');
        foreach ($files as $key => $folder) {
            if (is_dir($folder)) {
                $folder = explode('/', $folder);
                $project = new Project($folder[1]);
                $projects[] = array($project->projectMetaData['projectName'], $folder[1]);
            }
        }
        return $projects;
    }

    /**
     * Lista las plantillas disponibles
     * @return array Lista de plantillas
     */
    public function listTemplates()
    {
        $templates = array();
        $files = glob($this::$templatesFolder . '/*.html');
        // Recorremos todas las plantillas
        foreach ($files as $key => $file) {
            $file = basename($file);
            if ($file !== 'index.html') { // No queremos al index, lo odiamos
                // Revisamos si tiene una imagen de muestra
                list($file, $type) = explode('.', $file);
                $image = 'no-image';
                if (is_file($this::$templatesFolder . '/' . $file .'.png')) {
                    $image = $file;
                }
                $templates[] = array('template' => $file . '.html', 'image' => '../../' . $this::$templatesFolder . '/' . $image . '.png');
            }
        }
        return $templates;
    }

    /**
     * Envia el correo notificado la creación del nuevo proyecto
     * y la URL de accesso
     * @param  string $projectName   Nombre del proyecto
     * @param  string $projectFolder Carpeta del proyecto
     * @return void
     */
    private function sendEmail($projectName, $projectFolder)
    {
        $subject = str_replace('{project}', $projectName, $this->lang['email_subjet']);
        $projectURL = 'http://' . $_SERVER['SERVER_NAME'] . $this->config['base_path'] . '/' . $this::$projectsFolder . '/' . $projectFolder;
        $body = str_replace('{project_url}', $projectURL, $this->lang['email_body']);
        $body = str_replace('{project}', $projectName, $body);
        mail($this->config['email'], $subject, $body);
    }
}
