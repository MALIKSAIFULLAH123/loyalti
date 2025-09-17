<?php

/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\Platform\Resource;

use Illuminate\Support\Facades\Route;

class Actions
{
    /** @var array<string,ActionItem> */
    protected array $actions = [];

    /**
     * @var string|null
     */
    protected ?string $appName;

    /**
     * @var string|null
     */
    protected ?string $resourceName;

    /**
     * @var bool
     */
    protected bool $isAdminCP = true;

    /**
     * @param ?string $appName
     * @param ?string $resourceName
     */
    public function __construct(string $appName = null, string $resourceName = null)
    {
        $this->appName      = $appName?? '';
        $this->resourceName = $resourceName??'';
    }

    /**
     * @param bool $isAdminCP
     * @return self
     */
    public function setIsAdminCP(bool $isAdminCP = true): self
    {
        $this->isAdminCP = $isAdminCP;

        return $this;
    }

    /**
     * Add new column by field.
     *
     * @param string $name
     *
     * @return ActionItem
     */
    public function add(string $name): ActionItem
    {
        $action = new ActionItem($name);

        $this->actions[$name] = $action;

        return $action;
    }

    public function addActions(array $only = []): void
    {
        $routeName = match ($this->isAdminCP) {
            true    => 'admin.%s.%s.%s',
            default => '%s.%s.%s',
        };

        foreach ($only as $action) {
            $resource = str_replace('-', '_', $this->resourceName);

            $route    = sprintf($routeName, $this->appName, $this->resourceName, $action);

            if (!Route::has($route)) {
                continue;
            }

            $this->add($action)->apiUrl(apiUrl($route, [$resource => ':id']));
        }
    }

    /**
     * Edit page url pattern.
     *
     * etc: $actions->addEditPageUrl('links.editItem');
     *
     * Note: in ItemResource add attributes 'links.editItem'
     *
     * @param  string $name
     * @return $this
     */
    public function addEditPageUrl(string $name = 'links.editItem')
    {
        $this->add('edit')
            ->asFormDialog(false)
            ->link($name);

        return $this;
    }

    /**
     * @return array<string,array<string,mixed>>
     */
    public function toArray(): array
    {
        $result = [];

        foreach ($this->actions as $name => $action) {
            $result[$name] = $action->toArray();
        }

        return $result;
    }
}
