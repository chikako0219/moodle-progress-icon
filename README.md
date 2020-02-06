Progress-icon
=====================

This plugin (block) enables Moodle dashboard to show progress bar on each courses.


![progress_icon](https://user-images.githubusercontent.com/12591403/73707402-d973a280-473e-11ea-82db-365732589f6c.png)

![0538bb24-1310-4488-a338-e300cda2e24d](https://user-images.githubusercontent.com/12591403/73949609-fa561680-493d-11ea-9e9c-f24eae74d7c9.png)


To use this plugin, you need to install the following theme and plugin beforehand.

Completion Progress (Block):
https://moodle.org/plugins/block_completion_progress

Documentation and Source:
You can check documentation to install this plugin and download source code on Github.

Github:Progress Bar on Dashboard
https://github.com/chikako0219/moodle-progress-icon


How to install
=====================

* Download this plugin and put it under the directory "block".

* Go to Settings > Site Administration > Development > XMLDB editor and modify the module's tables.

* Modify version.php and set the initial version of you module.

* Visit Settings > Site Administration > Notifications, you should find
the module's tables successfully created

* Go to Site Administration > Plugins > Blocks > Manage blocks
and you should find that this progress_icon has been added to the list of
installed modules.

* You may now proceed to run your own code in an attempt to develop
your module. You will probably want to modify block_newmodule.php
and edit_form.php as a first step. Check db/access.php to add
capabilities.

* This is a template for Moodle blocks.
* It is used by Moosh (http://moosh-online.com/) to generate new block plugins.
* This template assumes that the block is using a textual content type by default. If you want your block to display a list of items (using $this->content->items and $this->content->icons instead of $this->content->text), change the derived class of the block, from extends block_base to extends block_list. For more information: https://docs.moodle.org/dev/Blocks#Additional_Content_Types.


How to add pictures of courses
=====================

* You can setup 2 pictures for each courses; "Picture1: In Progress" and "Picture2: Finished".
* At first, please upload pictrues to the directory "block/progress_icon/pix" with the name "image001.gif". you can upload up to 30 pictures.
* After uploaded pictures, go to "Site Admininistration/Plugin/progress_icon" and choose pictures as follows.


![progress_icon2](https://user-images.githubusercontent.com/12591403/73707415-df698380-473e-11ea-8a0c-1f90d50b4dc6.png)
