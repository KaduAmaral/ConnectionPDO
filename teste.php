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
   else exit('Couldn\'t open class '.$class.'!');
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
            background-color: #3477B9;
            font-family: Verdana, Arial, sans-serif;
         }
         #main {
            display: block;
            margin: 0 auto;
            padding-bottom: 50px;
            width: 860px;
         }
         h1{
            color:#F5F5F5;
            text-align: center;
            text-shadow: 2px 2px 5px rgba(0,0,0,.6);
         }
         label {
            display: block;
            padding: 10px 20px;
            margin: 50px auto 0;
            background-color: #F0F0F0;
            color: #06C;
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
      <div id="main">

         <h1>ConnectionPDO</h1>

         <label>Drop</label>
         <?php 
            $r = $con->drop('tab_teste');
         ?>
         <pre><?=$con->flushLog()?></pre>
         <pre><?=($r ? 'Success' : 'Fail')?></pre>

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
         <label>Create</label>
         <?php
            $sql =  'CREATE TABLE `tab_teste` (' . PHP_EOL .
                     '   id INT(4) NOT NULL AUTO_INCREMENT PRIMARY KEY,' . PHP_EOL .
                     '   name VARCHAR(60),' . PHP_EOL .
                     '   col3 VARCHAR(60) DEFAULT NULL' . PHP_EOL .
                     ');';
            $r = $con->query($sql);
         ?>
         <!--?=$con->Create('tab_teste',$fields,'id','InnoDB',false)?-->
         <pre>Not Implemented - Created manually</pre>
         <pre><?=$sql?></pre>
         <pre><?=($r ? 'Success' : 'Fail')?></pre>

         <label>Insert</label>
            <?php
               $log = '';


               echo '<pre>';
               var_dump( $con->getTables() );
               echo '</pre>';

               $r = $con->insert('tab_teste',Array('id'=>1,'name' => 'First Record', 'col3' => 'test '))->execute();
               $log .= ($r ? 'Success' : 'Fail') . PHP_EOL;

               echo '<pre>';
               echo $con->lastSQL() . PHP_EOL;
               echo '</pre>';

               echo '<pre>';
               var_dump( $con->getTables() );
               echo '</pre>';



               $r = $con->insert('tab_teste',Array('id'=>2,'name' => 'Second Record', 'col3' => 'test '))->execute();
               $log .= ($r ? 'Success' : 'Fail') . PHP_EOL;

               $r = $con->insert('tab_teste',Array('id'=>3,'name' => 'Third Record', 'col3' => 'test '))->execute();
               $log .= ($r ? 'Success' : 'Fail') . PHP_EOL;

               $r = $con->insert('tab_teste',Array('name' => 'Quarto', 'col3' => '4 '))->execute();
               $log .= ($r ? 'Success' : 'Fail') . PHP_EOL;

               $r = $con->insert('tab_teste',Array('name' => 'Quinto', 'col3' => '5 '))->execute();
               $log .= ($r ? 'Success' : 'Fail') . PHP_EOL;

               $r = $con->insert('tab_teste',Array('name' => 'Sexto', 'col3' => '6 '))->execute();
               $log .= ($r ? 'Success' : 'Fail') . PHP_EOL;

            ?>
         <pre><?=$con->flushLog() . PHP_EOL . PHP_EOL . $log?></pre>

         <label>Tabelas</label>
         <pre><?php var_dump( $con->getTables() );?></pre>

         <?php //$r = $con->delete('tab_teste', Array('id'=>1)); ?>
         <label>Delete</label>
         <pre><?=$con->flushLog()?></pre>
         <pre><?=($r ? 'Success' : 'Fail')?></pre>

         <label>Update</label>
         <?php $r = $con->update('tab_teste',Array('name' => 'Now this is the first record', 'col3' => 'First record '), Array('id'=>2)); ?>
         <pre><?=$con->flushLog()?></pre>
         <pre><?=($r ? 'Success' : 'Fail')?></pre>

         <label>Select</label>
         <?php
            $where = Array(
               'id' => array('NOT' => array(1,'>>>',6,array(3,5))),
               'OR',
               'col3' => array('LIKE' => 'recor')
            );

            $where = Array(
               'col3' => array('LIKE' => 'recor')
            );

            $res = $con->select('tab_teste', $where)->execute();
            echo '<pre>' . $con->flushLog() . PHP_EOL;
            var_dump($res);
            echo '</pre>';

            if ($res){
               $tab = $res->fetchAll(PDO::FETCH_ASSOC);
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
            }

         ?>
      </div>
   </body>
</html>