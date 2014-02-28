HTMLprototyper
==============

Simple live HTML prototyping tool. You edit on Firebug or Chrome DevTools, this script will save it to disk. Ideal for live prototyping with clients.

Live demo
=

You can see HTMLprototyper in action here: http://medula.cl/HTMLprototyper/projects/543b277c8345a74a821e186a657a34acd0889841

Feel free to break it, find bugs or do what you want of it.


Requirements
=

PHP 5.1 or higher

Installation
=

Just clone this repo to a folder or download it as ZIP and extract it.

By default we include a sample project inside, where you can create your new projects. Just browse to `<HTMLprototyper-folder>/projects/9aa6fb0dec2c55faa8f28c55d9c641851898f65f` and play around to get familiarized, it's very simple to use!.

The only important configuration to set is your email on `config.php` file.

How to use it
=

We already told you that it's really easy to use. But, if you are that person which read all the manuals, this is the HTMLprotyper bar:

![HTMLprotyper bar](http://medula.cl/HTMLprototyper/HTMLprototyper-bar.png "HTMLprotyper bar")

Configuration
=

Configuration is located in `config.php` file, and it's a simple array with the following options:

+ **company**: name of your company or your personal name. It will be added aside of your project name as a title of the documents.
+ **foundation_version**: contains the Foundation version which your next projects will use. When you create a project, this version get linked to it, so every new file you create inside that project, it will use the same Foundation version. The version must match to a folder name inside `foundation/`. So if you set it to `5.0.3` the folder `foundation/5.0.3/` must exists.
+ **lang**: the language which HTMLprototyper will use. Languages are located in `lang/` folder. By default we include English and Spanish. Wanna create a new translation?: just create a new file inside `lang/` and edit this configuration with the file name of your new translation.
+ **timezone**: it's not crucial, but it's recommended to set your timezone according your location. By default is configurated to `America/Santiago` (whe are from Chile, *po*!). Check the [list of supported timezones by PHP](http://cl1.php.net/timezones) and set your own.
+ **email**: list of emails separated with commas where HTMLprototyper will send the URL of new projects.
+ **default_template**: this is the template which HTMLprototyper will use whenever you create a new project. You can set your own default template. Just write a name of a template located at `templates/`.
+ **project_list**: `true` or `false`. For privacy reasons is configurated to `false` by default. This means that if you or others access the root folder of HTMLprototyper you will see an empty blank page. If you set it to `true` you and others will see a list of the projects and a little form where you can create new ones.

Install new version of Foundation
=

.....

About templates
=

.....
