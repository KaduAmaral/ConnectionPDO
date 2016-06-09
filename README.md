# ConnectionPDO

ConnectionPDO é uma classe PHP para realizar a gestão do banco de dados de uma forma mais prática.

##Version

1.1.0

## Methods

  - [Drop](#drop)
  - [Create](#create)
  - [Insert](#insert)
  - [Update](#update)
  - [Delete](#delete)
  - [Select](#select)
  - ExecuteSQL (generic query)
  - [Transaction](#insert) (Begin, Rollback, Commit)


## Exemplo

### Conexão

```php
$host = 'localhost';
$user = 'root';
$pass = '';
$base = 'test';

$con = new ConnectionPDO($host,$user,$pass,$base);
```


### DROP

```php
$con->drop('tab_teste');
```

### CREATE

```php
$fields = Array(
      'id' => Array(
         'type' => 'int',
         'size' => '4',
         'comment' => 'first key'
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
   $con->create('tab_teste',$fields,'id','InnoDB',false);
```

O quinto parâmetro é para eliminar a tabela, se existir.


### INSERT

```php
$data = Array('id'=>1,'name' => 'First Record', 'col3' => 'test ');
$con->insert('tab_teste',$data);

$data = Array('id'=>2,'name' => 'Second Record', 'col3' => 'test ');
$con->insert('tab_teste',$data);

$data = Array('id'=>3,'name' => 'Third Record', 'col3' => 'test ');
$con->insert('tab_teste',$data);
```

### DELETE

```php
$where = Array('id'=>1);
$con->delete('tab_teste', $where);
```


### UPDATE

```php
$data = Array(
   'name' => 'Now this is the first record', 
   'col3' => 'First record'
);
$where = Array('id'=>2);
$con->update('tab_teste',$data, $where);
```


### SELECT

```php
$where = Array('id' => Array('BETWEEN'=>Array(2,3)));
$res = $con->select('tab_teste',$where);
$tab = $res->fetch(PDO::FETCH_ASSOC);
```

**Resultado do select:**

```
------------------------------------------------------
| id | name                           | col3         |
------------------------------------------------------
| 2  | Now this is the first record   | First record |
------------------------------------------------------
| 3  | Third Record                   | test         |
------------------------------------------------------
```

### WHERE

Alguns exemplos do parâmetro `$where`:

```php
$where = 'id = 1'; 
// Resultado: id = 1

$where = Array('id' => 1); 
// Resultado: id = 1

$where = Array('id' => 1,'$OR1'=>'OR','col3' => 'test'); 
// Resultado: id = 1 OR col3 = 'test'

$where - Array('col3' => array('LIKE' => 'recor'))
// Resultado: col3 LIKE '%recor%'

$where = Array('id' => Array(1,'>>>',10, Array(3,6,8))); 
// Resultado: id IN (1,2,4,5,7,9,10)

$where = Array('id' => Array('BETWEEN' => Array(1,10)));
// Resultado:  id BETWEEN 1 AND 10

$where = Array('id' => Array('NOT' => Array(1,2,3,12,45)));
// Resultado: id NOT IN (1,2,3,12,45)

$where = Array('id' => Array('NOT' => Array(1,'>>>',10, Array(3,6,8)))); 
// Resultado: id NOT IN (1,2,4,5,7,9,10)


$where = Array(
  'id' => array('NOT' => array(1,'>>>',6,array(3,5))),
  '$OR'=>'OR',
  'col3' => array('LIKE' => 'recor')
);
// Resultado: id NOT IN (1, 2, 4, 6) OR col3 LIKE '%recor%'

```

Para inserir algo entre colunas basta passar um parâmetro iniciando com `$` seguido de qualquer _string_:

```php

$where = Array('id'=>3, '$a'=>'OR', 'name'=>Array('LIKE'=>'first'))
```

Também pode passar operadores como `>`, `>=`, `<=`, `<`, para coluna com valores numéricos. Basta dar um espaço do nome da coluna `'coluna >' => 'valor'`:

```php
$where = Array('id >'=>1)
// Resultado: id > 1
```

Outro exemplo:

```php
$where = Array(
   'id >' => 5,
   '$1'=>'OR',
   '$2'=>'(',
   'col3' => array('LIKE' => 'recor'),
   '$3'=>'OR',
   'name' => array('LIKE' => 'Recor'),
   '$4'=>')'
);
// Resultado: `id` > 5 OR  ( `col3` LIKE '%recor%' OR `name` LIKE '%Recor%' )
```

## Licença

Distribuição sob licença MIT


## Projeto

[Author: Kaduamaral](http://linkedin.com/in/kaduamaral)

[Author Blog: Devcia](http://devcia.com/)