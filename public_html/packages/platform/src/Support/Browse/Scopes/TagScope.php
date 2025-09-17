<?php

namespace MetaFox\Platform\Support\Browse\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Builder as QueryBuilder;
use MetaFox\Hashtag\Repositories\TagRepositoryInterface;

/**
 * Class TagScope.
 */
class TagScope extends BaseScope
{
    /**
     * @var string|null
     */
    private ?string $tag;

    /**
     * @var string|null
     */
    private ?string $pivotTable;

    /**
     * @var string|null
     */
    private ?string $identifier;

    /**
     * TagScope constructor.
     *
     * @param string|null $tag        - tag name to search etc: "nice"
     * @param string|null $pivotTable - tag data table to search, "blog_tag_data"
     * @param string|null $identifier - identifier of join table, "blog.id", "stream.feed_id"
     */
    public function __construct(?string $tag, ?string $pivotTable = null, ?string $identifier = null)
    {
        $this->tag = $tag;

        $this->pivotTable = $pivotTable;
        $this->identifier = $identifier;
    }

    public function apply(Builder $builder, Model $model)
    {
        if (!$this->tag) {
            return;
        }

        if (!method_exists($model, 'tagData')) {
            return;
        }

        $identifier = $this->identifier ?? "{$model->getTable()}.id";

        $pivotTable = $this->pivotTable ?? $model->tagData()->getTable();

        $tagId = resolve(TagRepositoryInterface::class)->getTagId($this->tag);

        $builder->join($pivotTable . ' as tag_data', 'tag_data.item_id', '=', $identifier)
            ->where('tag_data.tag_id', '=', (int) $tagId);
    }

    public function applyQueryBuilder(QueryBuilder $builder): void
    {
        if (!$this->tag) {
            return;
        }

        $tagId = resolve(TagRepositoryInterface::class)->getTagId($this->tag);

        $builder->join($this->pivotTable . ' as tag_data', 'tag_data.item_id', '=', $this->identifier)
            ->where('tag_data.tag_id', '=', (int) $tagId);
    }
}
