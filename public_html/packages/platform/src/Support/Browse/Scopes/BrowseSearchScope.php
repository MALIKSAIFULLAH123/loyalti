<?php
namespace MetaFox\Platform\Support\Browse\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class BrowseSearchScope extends BaseScope
{

    public function __construct(protected string $text, protected array $plainTextFields = [], protected array $htmlFields = [])
    {
    }

    public function apply(Builder $builder, Model $model)
    {
        if ('' === $this->text || (!count($this->plainTextFields) && !count($this->htmlFields))) {
            return;
        }

        $table = $model->getTable();

        $builder->where(function (Builder $builder) use ($table) {
            foreach ($this->plainTextFields as $field => $fieldValues) {
                if (!str_contains($field, '.')) {
                    $field = $this->alias($table, $field);
                }

                $required = Arr::get($fieldValues, 'required');

                if (!is_bool($required)) {
                    continue;
                }

                match ($required) {
                    true     => $builder->orWhere($field, $this->likeOperator(), "%{$this->text}%"),
                    default  => $builder->orWhere(function (Builder $builder) use ($field) {
                        $builder->whereNotNull($field)
                            ->where($field, $this->likeOperator(), "%{$this->text}%");
                    }),
                };
            }

            foreach ($this->htmlFields as $field => $fieldValues) {
                if (!str_contains($field, '.')) {
                    $field = $this->alias($table, $field);
                }

                $required = Arr::get($fieldValues, 'required');

                if (!is_bool($required)) {
                    continue;
                }

                $this->buildHtmlSearchCondition($builder, $field, $required);
            }
        });
    }

    protected function buildHtmlSearchCondition(Builder $builder, string $searchField, bool $required): void
    {
        if ($required) {
            match (database_driver()) {
                'pgsql' => $builder->orWhereRaw("regexp_replace({$searchField}, '<[^>]*>', '', 'g') {$this->likeOperator()} ?", ['%' . $this->text . '%']),
                default => $builder->orWhereRaw("REGEXP_REPLACE({$searchField}, '<[^>]+>', '') {$this->likeOperator()} ?", ['%' . $this->text . '%']),
            };

            return;
        }

        match (database_driver()) {
            'pgsql' => $builder->orWhere(function (Builder $builder) use ($searchField) {
                $builder->whereNotNull($searchField)
                    ->whereRaw("regexp_replace({$searchField}, '<[^>]*>', '', 'g') {$this->likeOperator()} ?", ['%' . $this->text . '%']);
            }),
            default => $builder->orWhere(function (Builder $builder) use ($searchField) {
                $builder->whereNotNull($searchField)
                    ->whereRaw("REGEXP_REPLACE({$searchField}, '<[^>]+>', '') {$this->likeOperator()} ?", ['%' . $this->text . '%']);
            }),
        };
    }
}
