# NetteModel
BaseModel for Nette application

## Usage
In `App\Models` directory, create your own models descendants of `Spaceboy\NetteModel\BaseModel` class:

```
<?php
namespace App\Models;

use Spaceboy\NetteModel;

class MyModel extends NetteModel\BaseModel
{
    ...
}
```

## Functions

### insertUpdate(string $tableName, ArrayHash $data, string $idColumn = 'id'): mixed
Stores **data** into database.
When offset named **idColumn** exists in **data** and has not empty/null value, updates table **tableName**, otherwise inserts into table **tableName**.
Returns ID of inserted/updated row or **null** when error occures.

Example:
```
    public function onFormSubmit(ArrayHash $data)
    {
        ...
        $id = $myModel->insertUpdate('users', $data);
    }
```

### switchRowPosition(string $tableName, object $row1, object $row2, ?string $orderColumn = 'order'): bool
Switches position of two rows in ordered DB tables.

Example:
```
    // move $row in table `users` one position up:
    $this->switchRowPosition($row, $prevousRow);
    // move $row in table `users` one position down:
    $this->switchRowPosition($row, $nextRow);
```

### if(mixed $condition): QueryCondition
Example:
```
    $this->db->query(
        'SELECT ',

        $this->if($order === ORDER_NORMAL)
            ->then('CONCAT(name, ?, surname) AS fullname', ' ')
            ->else('CONCAT(surname, ?, name) AS fullname', ', ')
            ->getLiteral(),

        ' ORDER BY fullname FROM users'
    )->fetchAll();
```

## QueryCondition functions

### then(...$args): QueryCondition
Set SQL fraction which is generated when `condition` (sent in `BaseModel->if()`) is true.
SQL is generated same way as in `Connection->query`.

### else(...$args): QueryCondition
Set SQL fraction which is generated when `condition` (sent in `BaseModel->if()`) is false.
SQL is generated same way as in `Connection->query`.

### getQuery(): string
Returns SQL fraction as `string`.

### getLiteral(): SqlLiteral
Returns SQL fraction as `SqlLiteral`.
