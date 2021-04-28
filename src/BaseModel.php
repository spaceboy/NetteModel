<?php

declare(strict_types=1);

namespace Spaceboy\NetteModel;

use Nette\Database\Connection;
use Nette\Utils\ArrayHash;

abstract class BaseModel
{
    protected Connection $db;

    public function __construct(
        Connection $db
    ) {
        $this->db = $db;
    }

    /**
     * Insert or update datarow.
     * @param string $tableName
     * @param ArrayHash $data
     * @param string $idColumn
     * @return mixed|null ID of updated/inserted row, null on error
     */
    protected function insertUpdate(string $tableName, ArrayHash $data, string $idColumn = 'id')
    {
        if ($data->offsetExists($idColumn)) {
            $id = $data->offsetGet($idColumn);
            $data->offsetUnset($idColumn);
        } else {
            $id = false;
        }

        try {
            if ($id) {
                $this->db->query('UPDATE ?name', $tableName, ' SET ? ', $data, 'WHERE ?name', $idColumn, ' = ?', (int)$id);
                return $id;
            } else {
                $this->db->query('INSERT INTO ?name', $tableName, ' ?' , $data);
                return $this->db->getInsertId();
            }
        } catch (\Exception $ex) {
            return null;
        }
    }

    protected function switchObjectsOrder(string $tableName, $objectA, $objectB, ?string $orderColumn = 'order'): bool
    {
        $this->db->beginTransaction();
        try {
            $this->db->query('UPDATE ?name', $tableName, ' SET ?name', $orderColumn, ' = ? ', 0, 'WHERE id = ? ', $objectB->id);
            $this->db->query('UPDATE ?name', $tableName, ' SET ?name', $orderColumn, ' = ? ', $objectB->order, 'WHERE id = ? ', $objectA->id);
            $this->db->query('UPDATE ?name', $tableName, ' SET ?name', $orderColumn, ' = ? ', $objectA->order, 'WHERE id = ? ', $objectB->id);
        } catch (\Exception $ex) {
            $this->db->rollback();
            return false;
        }
        $this->db->commit();
        return true;
    }

    protected function condition($condition): QueryCondition
    {
        return new QueryCondition($this->db, (bool)$condition);
    }
}
