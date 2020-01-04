<?php

define( 'MLAUTO_PLUGIN_URL', plugin_dir_path( __FILE__ ) );


require_once 'src/Model/PostInfoAggregator.php';
require_once 'src/Model/ClassificationModel.php';
require_once 'src/Model/TermModel.php';


require_once "src/Wrapper/Term.php";

require_once 'src/Analysis/Vectorizer.php';
require_once 'src/Analysis/Classifier.php';

require_once "mlauto-tag-ajax-hooks.php";

require_once __DIR__ . '/vendor/autoload.php';
