#! /bin/env sh
# This script is used to create directory strcutures and sample files 
# for a new project.
# author: Yuanjian Yi <yiyuanjian@gmail.com>
# 

if [ $# -ne 1 ]; then
    echo "Usage: $0 <path_projectname>"
    exit
fi

project=$1;

if [ -f "$project" ]; then
    echo "$project is already exist";
    exit
fi

mkdir -p ${project}/{app/{controllers,models,views,library,scripts,templates},cache,bin,public/{images,css,js},logs/{bin,app,server},archive};

# create index.php to public/
file_index=${project}/public/index.php
if [ ! -f "$file_index" ]; then
cat << EOF > $file_index
<?php
ini_set('display_errors', 'Off');
set_include_path(".:/data/website/public/");

define("APP_ROOT", realpath(dirname(__FILE__)."/../app"));

require 'Asf/WebApp.php';

\$app = new Asf_WebApp(include APP_ROOT."/config.php");
\$app->bootstrap()->run();
EOF
else 
    echo "$file_index is already exist!"
fi

# create main.php to bin/
file_index=${project}/bin/main.php
if [ ! -f "$file_index" ]; then
cat << EOF > $file_index
<?php
ini_set('display_errors', 'On');
set_include_path(".:/data/website/public/");

define("APP_ROOT", realpath(dirname(__FILE__)."/../app"));

require 'Asf/CliApp.php';

\$bin = new Asf_CliApp(include APP_ROOT."/config.php");
\$bin->bootstrap()->run();
EOF
else 
    echo "$file_index is already exist!"
fi

# create config.php to app/
file_config=${project}/app/config.php
if [ ! -f "$file_config" ]; then
cat << EOF > $file_config
<?php
return array(
    "_host" => array(
        "_default" => "localhost",
        "localhost" => array(
            "db" => "mysql://root:@127.0.0.1:3306/test:utf8",
        )
    )
);
EOF
else 
    echo "file $file_config is already exist";
fi

# create IndexScript to app/scripts
file_indexscript=${project}/app/scripts/IndexScript.php
if [ ! -f "$file_indexscript" ]; then
cat << EOF > $file_indexscript
<?php
class IndexScript extends Asf_ScriptAction {
    public function indexAction() {
        echo "It works\n";
    }
}
EOF
else
    echo "file $file_indexscript is already exist";
fi

# create IndexController to app/controllers/ and IndexView to app/views/
file_indexcontroller=${project}/app/controllers/IndexController.php
if [ ! -f "$file_indexcontroller" ]; then
cat << EOF > $file_indexcontroller
<?php
class IndexController extends Asf_ControllerAction {
    public function indexAction() {
        echo "It works";
    }
}
EOF
else
    echo "file $file_indexcontroller is already exist";
fi

file_indexview=${project}/app/views/IndexView.php
if [ ! -f "$file_indexview" ]; then
cat << EOF > $file_indexview
<?php
class IndexView extends Asf_ViewAction {
    public static function showIndex(\$data) {
        return self::output("index", \$data);
    }
}
EOF
else
    echo "file $file_indexview is already exist";
fi
