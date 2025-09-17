<?php
/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\Platform\Traits\Eloquent\Model;

use Exception;
use Illuminate\Database\Eloquent\Concerns\HasRelationships;
use Illuminate\Database\Eloquent\MassAssignmentException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Trait HasNestedAttributes.
 *
 * @mixin Model
 * @mixin HasRelationships
 * @property string[] $nestedAttributes
 * @SuppressWarnings(PHPMD.CyclomaticComplexity)
 */
trait HasNestedAttributes
{
    /**
     * Defined nested attributes.
     *
     * @var array<mixed>
     */
    protected $nestedAttributesFor = [];

    /**
     * Get accept nested attributes.
     *
     * @return array<mixed>
     */
    public function getNestedAttributesFor(): array
    {
        return $this->nestedAttributesFor;
    }

    /**
     * Fill the model with an array of attributes.
     *
     * @param array<mixed> $attributes
     *
     * @return static
     *
     * @throws MassAssignmentException
     */
    public function fill(array $attributes)
    {
        if (!empty($this->nestedAttributes)) {
            $this->nestedAttributesFor = [];

            foreach ($this->nestedAttributes as $key => $attr) {
                if (is_string($attr)) {
                    if (is_string($key)) {
                        $this->nestedAttributesFor[$key] = [];
                    } elseif (isset($attributes[$attr])) {
                        $this->nestedAttributesFor[$attr] = $attributes[$attr];
                        unset($attributes[$attr]);
                    }
                } elseif (is_array($attr)) {
                    $packed = [];
                    foreach ($attr as $name) {
                        if (array_key_exists($name, $attributes)) {
                            $packed[$name] = $attributes[$name];
                            unset($attributes[$name]);
                        }
                    }
                    if (!empty($packed)) {
                        $this->nestedAttributesFor[$key] = $packed;
                    }
                }
            }
        }

        return parent::fill($attributes);
    }

    /**
     * Save the model to the database.
     *
     * @param array<mixed> $options
     *
     * @return bool
     * @throws Exception
     */
    public function save(array $options = []): bool
    {
        $useTransaction      = false;
        $nestedAttributesFor = $this->getNestedAttributesFor();
        $hasAttributes       = !empty($nestedAttributesFor);

        try {
            if ($useTransaction) {
                DB::beginTransaction();
            }

            if (!parent::save($options)) {
                return false;
            }

            if ($hasAttributes) {
                foreach ($nestedAttributesFor as $attribute => $params) {
                    if (!is_string($attribute)) {
                        continue;
                    }
                    $methodName = Str::camel("save_nested_attribute_{$attribute}");
                    if (method_exists($this, $methodName)) {
                        $this->$methodName($params);
                        continue;
                    }

                    if (method_exists($this, $attribute)) {
                        $relation = $this->{$attribute}();
                        if ($relation instanceof HasOne) {
                            $this->syncHasOneRelationAttribute($relation, $params);
                        } elseif ($relation instanceof BelongsToMany) {
                            $relation->sync($params);
                        } elseif ($relation instanceof HasMany) {
                            $this->syncHasManyRelationAttribute($relation, $params);
                        }
                        continue;
                    }

                    throw new Exception('Required at least "' . $attribute . '():Relation  or method ' . $methodName . '($params)" does not exists.');
                }
            }

            if ($useTransaction) {
                $useTransaction = false;
                DB::commit();
            }
        } catch (Exception $exception) {
            if ($useTransaction) {
                Db::rollBack();
            }
            throw $exception;
        }

        return true;
    }

    /**
     * Save the hasMany nested relation attributes to the database.
     *
     * @param HasMany      $relation
     * @param array<mixed> $params
     *
     * @return bool
     */
    public function syncHasManyRelationAttribute(HasMany $relation, array $params): bool
    {
        if ($this->exists && !empty($params)) {
            foreach ($params as $param) {
                if (isset($params['id'])) {
                    $model = $relation->findOrFail($param['id']);
                    if ($this->allowDestroyNestedAttributes($param)) {
                        return $model->delete() ? true : false;
                    }
                    $model->update($param);
                    continue;
                }

                $relation->create($param);
            }

            return true;
        }

        $relation->create($params);

        return true;
    }

    /**
     * @param HasOne       $relation
     * @param array<mixed> $params
     *
     * @return bool
     */
    public function syncHasOneRelationAttribute(HasOne $relation, array $params): bool
    {
        $model = $relation->first();
        if ($this->exists && $model != null) {
            $model->update($params);

            return true;
        }

        $relation->create($params);

        return true;
    }

    /**
     * Check can we delete nested data.
     *
     * @param array<mixed> $params
     *
     * @return bool
     */
    protected function allowDestroyNestedAttributes(array $params): bool
    {
        return isset($params['_destroy']) && (bool) $params['_destroy'] == true;
    }
}
