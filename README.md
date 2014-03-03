HTMLprototyper
==============

Simple live HTML prototyping tool. You edit on Firebug or Chrome DevTools, this script will save it to disk. Ideal for live prototyping with clients.

Live demo
=

You can see HTMLprototyper in action here: http://medula.cl/HTMLprototyper/projects/543b277c8345a74a821e186a657a34acd0889841

Feel free to break it, find bugs or do what you want of it. The demo is restarted each hour.


Requirements
=

PHP 5.3 or higher

Installation
=

Just clone this repo to a folder or download it as ZIP and extract it.

By default we include a sample project inside, where you can create your new projects. Just browse to `<HTMLprototyper-folder>/projects/9aa6fb0dec2c55faa8f28c55d9c641851898f65f` and play around to get familiarized, it's very simple to use!.

The only important configuration to set is your email on `config.php` file.

How to use it
=

We already told you that it's really easy to use. But, if you are that person which read all the manuals, this is the HTMLprotyper bar:



Configuration
=

Configuration is located in `config.php` file, and it's a simple array with the following options:

+ **company**: name of your company or your personal name. It will be added aside of your project name as a title of the documents.
+ **foundation_version**: contains the Foundation version which your next projects will use. When you create a project, this version get linked to it, so every new file you create inside that project, it will use the same Foundation version. The version must match to a folder name inside `foundation/`. So if you set it to `5.0.3` the folder `foundation/5.0.3/` must exists.
+ **lang**: the language which HTMLprototyper will use. Languages are located in `lang/` folder. By default we include English and Spanish. Wanna create a new translation?: just create a new file inside `lang/` and edit this configuration with the file name of your new translation.
+ **timezone**: it's not crucial, but it's recommended to set your timezone according your location. By default is configurated to `America/Santiago` (we are from Chile, *po*!). Check the [list of supported timezones by PHP](http://cl1.php.net/timezones) and set your own.
+ **email**: list of emails separated by commas where HTMLprototyper will send the URL of new projects.
+ **default_template**: this is the template which HTMLprototyper will use whenever you create a new project. You can set your own default template. Just write a name of a template located at `templates/`.
+ **project_list**: `true` or `false`. For privacy reasons is configurated to `false` by default. This means that if you or others access the root folder of HTMLprototyper you will see an empty blank page. If you set it to `true` you and others will see a list of the projects and a little form where you can create new ones.

Install a new version of Foundation
=

We include Foundation 5.0.3, but if you need to install another version, you only need to download it, create a folder inside `foundation/` with the name of the version you need, and copy the files inside. Finally, you need to edit your config file and change the version of Foundation which HTMLprototyper will use when you create new projects.

Very simple, don't you think?


About templates
=

Every time you create a new file in your project, a modal window will pop-up with a list of available templates. There are 14 different choices, one is a simple blank page, and the remaining 13 are [templates borrowed from Foundation](http://foundation.zurb.com/templates.html).

Obviously you can create your own templates, or delete those you don't need. To create a new one, we recommend to take `empty.html` as a base template. Just copy it, rename it and edit it. If you wanna to create your own from scratch, you must add jQuery and HTMLprototyper JavaScript files to it:

```
<script src="../../assets/js/jquery-2.1.0.min.js"></script>
<script src="../../assets/js/HTMLprototyper.js"></script>
```
**HTMLprototyper only recognize `.html` file extension as templates.**
