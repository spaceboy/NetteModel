<?php

declare(strict_types=1);

namespace Spaceboy\NetteModel;

use Nette\Database\Connection;
use Nette\Database\SqlLiteral;

class QueryCondition
{
    private Connection $db;

    private bool $condition;

    private array $thenArgs = [' '];

    private array $elseArgs = [' '];

    /**
     * Class constructor.
     * @param Connection $db
     * @param bool $condition
     */
    public function __construct(Connection $db, bool $condition)
    {
        $this->db = $db;
        $this->condition = $condition;
    }

    /**
     * Set SQL fraction generated when condition is true.
     */
    public function then(...$args): QueryCondition
    {
        $this->thenArgs = $args;
        return $this;
    }

    /**
     * Set SQL fraction generated when condition is false.
     */
    public function else(...$args): QueryCondition
    {
        $this->elseArgs = $args;
        return $this;
    }

    /**
     * Return SQL fraction as string.
     * @return string
     */
    public function getQuery(): string
    {
        return \call_user_func_array(
            [$this->db, 'preprocess'],
            ($this->condition ? $this->thenArgs : $this->elseArgs)
        )[0];
    }

    /**
     * Return SQL fraction as SqlLiteral.
     * @return SqlLiteral
     */
    public function getLiteral(): SqlLiteral
    {
        return new SqlLiteral($this->getQuery());
    }
}
