
<?php
require_once 'functions.php';

session_start();
session_destroy();

redirect('index.html');
?>