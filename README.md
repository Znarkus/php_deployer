
php_deployer
============

## Example usage

	<?php
	require 'php_deployer/deployer.php';
	chdir(__DIR__);
	$config = require('config.php');
	$deploy = new Deploy();
	$deploy->git('git://github.com/Znarkus/share.git');
	$deploy->mysql('deploy/db/', $config['db']);
	$deploy->finalize();


## `git($url, $branch = 'master')`

With default options, will create a directory structure that looks something like this.

	/ snapshots
	--/ miaduc_master
	  / nfelfj_master
	  / orernq_master

Every sub-directory is a fresh `git clone`. The directory name has the format `[timestamp in base36]_[branch]`.


## `mysql($migrations_dir, $credentials)`

Migrates the database. First parameter is where in the project directory to look for .sql files.
The second parameter is a credentials array, that looks like `['user' => 'username', 'pass' => 'password', 'dbname' => 'database_name']`;


## `finalize()`

With default options, creates a symlink `current/` that points to the latest `git clone`.