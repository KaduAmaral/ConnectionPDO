<?php
require_once __DIR__ . DIRECTORY_SEPARATOR . 'driverinterface.php';

/**
* 
*/
class SQLDriver implements DriverInterface {

   private $params;
   private $log = '';

   public function select($table, $where = NULL, $cols = '*', $limit = NULL){

      $this->clearParams();

      $_where = !is_null($where) ? $this->where($where) : '';

      $_limit = (!is_null($limit) ? 'LIMIT '.$limit : '' );

      $sql = "SELECT {$cols} FROM `{$table}`{$_where}{$_limit};";

      $this->log .= $sql . PHP_EOL;

      return $sql;
   }


   public function insert($table, $data){

      $this->clearParams();

      $sql = "INSERT INTO `{$table}` ";

      $colunms = Array();

      $values  = Array();

      foreach ($data as $col => $value) {
         $colunms[] = "`{$col}`";
         $values[]  = '?';

         $this->addParam($col, $value);
      }

      $sql .= '(' . implode(', ', $colunms) . ') VALUES (' . implode(', ', $values) . ');';

      $this->log .= $sql . PHP_EOL;

      return $sql;
   }

   public function update($table, $data, $where = NULL){

      $this->clearParams();

      $sql = "UPDATE `{$table}` SET ";

      $values = Array();

      foreach ($data as $col => $value) {
         $values[]  = "`{$col}` = ?";
         $this->addParam($col, $value);
      }


      $sql .= implode(', ', $values);

      if (!is_null($where))
         $sql .= $this->where($where);

      $sql = $sql . ';';

      $this->log .= $sql . PHP_EOL;

      return $sql;

   }

   public function delete($table, $where = NULL){

      $this->clearParams();

      $sql = "DELETE FROM `{$table}`" . $this->where($where) . ';';

      $this->log .= $sql . PHP_EOL;

      return $sql;
   }

   public function drop($table){
      $sql = "DROP TABLE `{$table}`;";

      $this->log .= $sql . PHP_EOL;

      return $sql;
   }

   public function create($table, $fields, $options = NULL){

      if (is_null($table) || !is_string($table) || (is_string($table) && $table == '')) 
         throw new Exception('Error: First parameter `table name` is invalid for method `Create`!');

      if (!is_array($fields)) 
         throw new Exception('Error: Second parameter `table fields` is invalid for method `Create`!');

      if (is_array($primaryKey)) {
         $pks = Array();
         foreach ($primaryKey as $key)
            $pks[] = "`{$key}`";
         $_pk = 'PRIMARY KEY ('.implode(',', $pks).')';
      } else if (is_string($primaryKey) && $primaryKey != ''){
         $_pk = "PRIMARY KEY (`{$primaryKey}`)";
      } else {
         $_pk = '';
      }

      $_fields = '';

      foreach ($fields as $field => $values) {
         $_fields .= "`{$field}` ";
         if (isset($values['type'])) $_fields .= $values['type'];
         if (isset($values['size'])) $_fields .= '('.$values['size'].') ';
         if (isset($values['pk']) && $values['pk']) $_fields .= 'PRIMARY KEY ';
         if (isset($values['auto']) && $values['auto']) $_fields .= 'AUTO_INCREMENT ';
         if (isset($values['null']) && $values['null']) $_fields .= 'NULL '; else $_fields .= 'NOT NULL ';
         if (isset($values['deafult'])) $_fields .= 'DEFAULT '.(is_string($values['deafult']) ? '\''.$values['deafult'].'\'' : $values['deafult']).' ';
         if (isset($values['comment'])) $_fields .= "COMMENT '{$values['comment']}' ";
         $_fields .= ', '.PHP_EOL;
      }
      if ($_pk === '')
         $_fields = substr($_fields, 0, strlen($_fields) -2);
      else 
         $_fields .= $_pk;
      $_enginne = '';
      if (is_string($engine) && $engine != '') {
         $_enginne = "ENGINE = {$engine} ";
      }
      $_charset = '';
      if (is_string($charset) && $charset != '' ){
         $_charset = "DEFAULT CHARACTER SET = {$charset} ";
      }
      if (is_string($collate) && $collate != '' ){
         $_collate = "COLLATE = {$collate} ";
      }
      if ($drop) {
         $_sql = "CREATE TABLE `{$table}` (";
      } else {
         $_sql = "CREATE TABLE IF NOT EXISTS `{$table}` (";
      }
      $_sql .= PHP_EOL.$_fields.PHP_EOL.") {$_enginne}{$_charset}{$_collate};";

      if ($drop) 
         $this->Drop($table);

      $this->log .= $_sql . PHP_EOL;

      return $_sql;
   }

   public function setParams(PDOStatement &$stmt){
      $params = $this->getParams();
      $this->log .=  'Setando Parâmetros: '.PHP_EOL;
      if (is_array($params) && !empty($params)){
         foreach ($params as $param => $value){
            $stmt->bindValue($param+1, $this->prepareParam($value), $this->getParamType($value));
            $this->log .=  $param+1 . ' => ' . $this->prepareParam($value) . PHP_EOL;
         }
      }
      $this->log .= PHP_EOL.'-----------------------------'.PHP_EOL.PHP_EOL;
   }

   private function prepareParam($value){
      if (is_numeric($value) && is_float($value))
         return "{$value}";
      else
         return $value;
   }

   private function getParamType($value){
      if (is_null($value))
         return PDO::PARAM_NULL;
      else if (is_bool($value))
         return PDO::PARAM_BOOL;
      else if (is_numeric($value) && is_integer($value + 0))
         return PDO::PARAM_INT;
      else 
         return PDO::PARAM_STR;


   }

   private function where($where) {

      if (is_null($where)) return '';

      $_where = ' WHERE ';

      if (is_string($where) && $where != '') return $_where.$where;

      foreach ($where as $col => $value) {

         if ($value === 'OR'){
            $_where = substr($_where, 0, strlen($_where)-5).' OR ';
            continue;
         }

         $_where .= "`{$col}`";

         if (is_string($value) || is_numeric($value)){
            $_where .= " = ?";
            $this->addParam('onwhere'.$col, $value);
         } elseif (is_null($value)){
            $_where .= " IS NULL";
         } 
         elseif (is_array($value)) {
            if (array_key_exists('NOT', $value))
               $_where .= $value['NOT'] === NULL ? ' IS NOT NULL' : $this->prepareInWhere($col, $value);
            else if (array_key_exists('BETWEEN', $value)){
               if (count($value['BETWEEN']) !== 2) 
                  throw new Exception('Error: `where` clause BETWEEN data size is invalid!');

               $this->addParam('onwherebetween0'.$col, $value['BETWEEN'][0]);
               $this->addParam('onwherebetween1'.$col, $value['BETWEEN'][1]);

               $_where .= " BETWEEN ? AND ?";
            } else if (array_key_exists('LIKE', $value)){
               $this->addParam('onwherelike'.$col, '%'.$value['LIKE'].'%');
               $_where .= " LIKE ?";
            } else { // IN
               $_where .= $this->prepareInWhere($col, $value);
            }
         }
         $_where = rtrim($_where).' AND ';
      }
      return substr($_where, 0, strlen($_where)-5);
   }

   private function prepareInWhere($col, $in){

      $where = '';
      $not = '';
      if (array_key_exists('NOT', $in)) {
         $in = $in['NOT'];
         $not = 'not';
         $where .= ' NOT';
      }

      $paramkey = "onwhere{$col}{$not}in";

      $where .= " IN (";

      $value = '';

      


      $counter = 0;
      if (in_array('>>>', $in)){
         for ($i=$in[0]; $i <= $in[2]; $i++){
            if (isset($in[3]) && in_array($i, $in[3])) 
               continue;
            $where .= '?, ';//':'.$paramkey.$counter.', ';
            $this->addParam($paramkey.$counter, $i);
            $counter++;
         }
      } else {
         foreach ($in as $vals){
            $where .= '?, '; //':'.$paramkey.$counter.', ';
            $this->addParam($paramkey.$counter, $vals);
            $counter++;
         }
      }

      $where = substr($where, 0, strlen($where) -2) . ')';

      return $where;
   }

   public function getParam($key){
      return $this->params[$key];
   }

   public function getParams(){
      return $this->params;
   }

   public function addParam($key, $value){
      //$this->params[':'.$key] = $value;
      $this->params[] = $value;
   }

   public function clearParams(){
      $this->params = Array();
   }

   public function flushLog(){
      $log = $this->log;
      $this->log = '';
      return $log;
   }



}