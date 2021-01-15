<?php

$dbUsername = '';
$dbPassword = '';
$dbHost = '';
$dbName = 'errorfinder';

$rooturl = ""; // ex "dev.cisco.com"

$rootdir = "/errorfinder"; //ex "/url"

$osEnvironment= "WINDOWS"; //options are currently WINDOWS, LINUX

$EFPythonFile = "C:\\efpython\\main.py"; // the directory of the error finder python file

$EFPythonRuntime = "C:\\Program Files (x86)\\Python39-32\\python.exe"; // the python runtime, or command that runs before the python filename

/*
 * if you are on windows and have PATH set up correctly you can just use "py" for the pythonruntime string
 * NOTE that PHP servers often do not have permissions to run programs on windows
 * to fix this, you need to go into Windows Services, find the webserver, right click it and go to properties,
 * click Log On at the top, select "This Account", and enter a user's credentials
 *
 * on linux it is infinitely simpler
 *
 */

