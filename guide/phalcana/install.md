## Requirements

 - PHP 5.3+
 - [Phalcon](http://phalconphp.com/en/download)


## Installation

If you are cloning the repository it is recommended that you use `git clone --recursive` to ensure all submodules and their submodules are initialized and updated.

### Using the installer

The framework comes with a task runner that can be used to configure the framework. To run the installer you must have PHP in your path or use the raw PHP command below with a absolute path to your PHP binary.

	php index.php install  

Or  

	./phalcana install


### Manual installation

 - Give write permissions to the `app/logs/` and `app/cache/` folders.
 - Copy `example.htaccees` to `.htaccess` and configure the rewrite base to point to site directory relative to the web root.
 - Copy `app/config/example.setup.php` to `app/config/setup.php` and configure the `base_url` and `static_base_url`.


## Getting started

You can check that your installation has been successful by visiting configured path in your browser. If the installation completed successfully you should see the welcome page.

Now that you have installed the basic setup you may start building your project. For more information on using the framework please visit the [Phalcana documentation](http://phalcana.com/guide). Phalcana is built on top of [Phalcon](http://phalconphp.com/en/download) so you may also need to refer to the documentation for the [Phalcon framework](http://docs.phalconphp.com/).

### Installing a template.

The Phalcana framework comes with a template system to help you get started on your project. The templates are installed via the CLI task runner.

	php index.php install template  

Or

	./phalcana install template

You may also manually install the templates. The templates are located in the `system/templates` directory and can be installed by merging the contents into the route directory and reading the `README.md` file.