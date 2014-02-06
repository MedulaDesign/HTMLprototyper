<?php namespace Medula\HTMLprototyper;

use Medula\HTMLprototyper\HTMLprototyper as HTMLprototyper;

class Project
{
	public $projectFolder;

	public function __construct($projectFolder)
	{
		$this->projectFolder = $projectFolder;
	}

	public function getFiles()
	{
		$files = glob(HTMLprototyper::$projectsFolder . '/' . $this->projectFolder .'/*.html');
		foreach ($files as $key => $file) {
			$files[$key] = basename($file);
		}
		return $files;
	}
}
