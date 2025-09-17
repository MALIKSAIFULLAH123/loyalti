<?php

namespace MetaFox\Platform\Rules;

use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Facades\DB;

class CaseInsensitiveUnique implements Rule
{
    protected string $table;

    protected string $column;

    protected mixed $ignore = null;

    protected string $idColumn;

    /**
     * @param string     $table
     * @param string     $column
     * @param mixed|null $ignore
     * @param string     $idColumn
     */
    public function __construct(string $table, string $column, mixed $ignore = null, string $idColumn = 'id')
    {
        $this->table = $table;
        $this->column = $column;
        $this->ignore = $ignore;
        $this->idColumn = $idColumn;
    }

    public function passes($attribute, $value)
    {
        $table = $this->table;
        $column = $this->column;
        $idColumn = $this->idColumn;

        $result = DB::selectOne("select $idColumn from $table where lower($column)=lower(?) LIMIT 1", [$value]);

        if (empty($result)) {
            return true;
        }

        if (!$this->ignore) {
            return false;
        }

        if (is_array($result) && $result[$idColumn] == $this->ignore) {
            return true;
        }

        if ($result->{$idColumn} == $this->ignore) {
            return true;
        }

        return false;
    }

    public function message()
    {
        return __p('validation.unique');
    }
}
