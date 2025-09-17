<?php
/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\Platform\Resource;

/**
 * Class GridColumns.
 */
class GridColumns
{
    /**
     * @var array<string,GridColumn>
     */
    protected array $columns = [];

    /**
     * Add new column by field.
     *
     * @param string $field
     *
     * @return GridColumn
     */
    public function add(string $field): GridColumn
    {
        $column = new GridColumn($field);

        $this->columns[$field] = $column;

        return $column;
    }

    public function toArray(): array
    {
        $result = [];
        foreach ($this->columns as $column) {
            $result[] = $column->toArray();
        }

        return $result;
    }
}
