<?php 
error_reporting(E_ALL);
ini_set('display_errors', TRUE);
ini_set('display_startup_errors', TRUE);

$stmt = NULL;

function __autoload($class){
   if (class_exists($class)) return true;

   $dir = __DIR__.DIRECTORY_SEPARATOR;
   $ext = '.php';
   if (file_exists($dir.$class.$ext)) require_once $dir.$class.$ext;
   else exit('Coul\'d open '.$class.'!');
}


$settings = Array(
   'driver'    => 'mysql',
   'host'      => 'localhost',
   'port'      => '3306',
   'schema'    => 'test',
   'username'  => 'root',
   'password'  => ''
);

$dns = $settings['driver'] . ':host=' . $settings['host'] . 
                             ';port=' . $settings['port'] . 
                             ';dbname=' . $settings['schema'];

$con = new ConnectionPDO($dns, $settings['username'], $settings['password']);

?><!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="utf-8">
   <title>ConnectionMSi Test</title>
   <style>
      body {
         margin: 0;
         padding: 0;
         background-color: #757575;
      }
      main {
         display: block;
         margin: 0 auto;
         width: 620px;
      }
      label {
         display: block;
         padding: 10px 20px;
         margin: 50px auto 0;
         background-color: #F0F0F0;
         color: #06C;
         font-family: Verdana, Arial, sans-serif;
         font-size: 24px;
         box-shadow: 2px 3px 7px rgba(0,0,0,0.7);
      }
      pre {
         display: block;
         margin: 0 auto 0;
         padding: 20px;
         border-top: 1px solid #FFF;
         max-width: 100%;
         background-color: #F8F8F8;
         color:#333;
         white-space: pre-wrap;       /* css-3 */
         white-space: -moz-pre-wrap;  /* Mozilla, since 1999 */
         white-space: -pre-wrap;      /* Opera 4-6 */
         white-space: -o-pre-wrap;    /* Opera 7 */
         word-wrap: break-word;       /* Internet Explorer 5.5+ */
         box-shadow: 2px 3px 7px rgba(0,0,0,0.7);
      }
      table {
         border-collapse: collapse;
         width: 100%;
      }
      th{
         font-weight: bold;
      }
      table, th, td {
          padding: 4px;
          border-color: #999;
      }
   </style>
</head>
<body>
<main>
   <label>Drop Statement</label>
   <?php 
      $con->drop('tab_teste')
   ?>
   <pre><?=$con->lastSQL()?></pre>

   <?php

   $fields = Array(
      'id' => Array(
         'type' => 'int',
         'size' => '4',
         'comment' => 'first key',
         'auto' => true
      ),
      'name' => Array(
         'type' => 'varchar',
         'size' => '60',
         'comment' => 'test name'
      ),
      'col3' => Array(
         'type' => 'varchar',
         'size' => '60',
         'default' => NULL,
         'comment' => 'test name'
      )
   );
   ?>
   <label>Create Statement</label>
   <?php
      $con->query(
         "CREATE TABLE `tab_teste` (
            id INT(4) NOT NULL AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(60),
            col3 VARCHAR(60) DEFAULT NULL
         );"
      );
   ?>
   <pre>Not Implemented<!--?=$con->Create('tab_teste',$fields,'id','InnoDB',false)?--></pre>

   <label>Insert Statement</label>
      <?php
         $sql = '';
         $con->insert('tab_teste',Array('id'=>1,'name' => 'First Record', 'col3' => 'test '))->execute();
         $sql .= $con->lastSQL() . PHP_EOL;
         $con->insert('tab_teste',Array('id'=>2,'name' => 'Second Record', 'col3' => 'test '))->execute();
         $sql .= $con->lastSQL() . PHP_EOL;
         $con->insert('tab_teste',Array('id'=>3,'name' => 'Third Record', 'col3' => 'test '))->execute();
         $sql .= $con->lastSQL() . PHP_EOL;
         $con->insert('tab_teste',Array('name' => 'Quarto', 'col3' => '4 '))->execute();
         $sql .= $con->lastSQL() . PHP_EOL;
         $con->insert('tab_teste',Array('name' => 'Quinto', 'col3' => '5 '))->execute();
         $sql .= $con->lastSQL() . PHP_EOL;
         $con->insert('tab_teste',Array('name' => 'Sexto', 'col3' => '6 '))->execute();
         $sql .= $con->lastSQL() . PHP_EOL;
      ?>
   <pre><?=$sql?></pre>

   <label>Delete Statement</label>
   <?php $con->delete('tab_teste', Array('id'=>1)); ?>
   <pre><?=$con->lastSQL()?></pre>

   <label>Update Statement</label>
   <?php $con->Update('tab_teste',Array('name' => 'Now this is the first record', 'col3' => 'First record '), Array('id'=>2)); ?>
   <pre><?=$con->lastSQL()?></pre>

   <label>Insert Statement</label>
   <?php
      $where = Array(
         'id' => array('NOT' => array(1,'>>>',6,array(3,5))),
         'OR',
         'col3' => array('LIKE' => 'recor')
      );
      echo '<pre>';
      $con->select('tab_teste',$where)->execute();
      echo '</pre>';
   ?>
   <pre><?=$con->lastSQL()?></pre>
   
   <label>Select Statement</label>
   <?php
   $res = $con->select('tab_teste',$where)->execute();
   $tab = $res->fetch_all(MYSQLI_ASSOC);
   if (is_array($tab)){
      $_cols = Array();
      foreach ($tab as $key => $row) {
         foreach ($row as $col => $val) {
            if (count($_cols) != count($row)) $_cols[] = $col;
            else break;
         }
         break;
      }
      echo '<pre><table cellpadding="0" cellspacing="0" border="1"><thead><tr>';
      foreach ($_cols as $colun) {
         echo "<th>{$colun}</th>";
      }
      echo '</tr></thead><tbody>';
      foreach ($tab as $key => $row) {
         echo '<tr>';
         foreach ($row as $col => $val) {
            echo "<td>{$val}</td>";
         }
         echo '</tr>';
      }
      echo '</tbody></table></pre>';
   } else {
      echo '<pre>'.$con->_lastSql.'</pre>';
   }
   ?>
</main>
</body>
</html>