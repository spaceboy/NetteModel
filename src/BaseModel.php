<?php

declare(strict_types=1);

namespace Spaceboy\NetteModel;

use Nette\Database\Connection;
use Nette\Database\Explorer;
use Nette\Utils\ArrayHash;

abstract class BaseModel
{
    protected Connection $db;

    protected Explorer $explorer;

    public function __construct(
        Connection $db,
        Explorer $explorer
    ) {
        $this->db = $db;
        $this->explorer = $explorer;
    }

    /**
     * Insert or update datarow.
     * @param string $tableName
     * @param ArrayHash $data
     * @param string $idColumn
     * @return mixed|null ID of updated/inserted row, null on error
     */
    public function insertUpdate(string $tableName, ArrayHash $data, string $idColumn = 'id')
    {
        $id = (
            $data->offsetExists($idColumn)
            ? $data->offsetGet($idColumn)
            : null
        );
        $data->offsetUnset($idColumn);

        try {
            if ($id) {
                $this->explorer->table($tableName)
                    ->where($idColumn, $id)
                    ->update($data);
                return $id;
            } else {
                $this->explorer->table($tableName)
                    ->insert($data);
                return $this->db->getInsertId();
            }
        } catch (\Exception $ex) {
            return null;
        }
    }

    public function switchRowPosition(string $tableName, $row1, $row2, ?string $orderColumn = 'order', ?string $idColumn = 'id'): bool
    {
        $this->db->beginTransaction();
        try {
            $this->explorer->table($tableName)
                ->where($idColumn, $row2->id)
                ->update([$orderColumn => 0]);
            $this->explorer->table($tableName)
                ->where($idColumn, $row1->id)
                ->update([$orderColumn => $row2->order]);
            $this->explorer->table($tableName)
                ->where($idColumn, $row2->id)
                ->update([$orderColumn => $row1->order]);
        } catch (\Exception $ex) {
            $this->db->rollback();
            return false;
        }
        $this->db->commit();
        return true;
    }

    protected function if($condition): QueryCondition
    {
        return new QueryCondition($this->db, (bool)$condition);
    }
}
