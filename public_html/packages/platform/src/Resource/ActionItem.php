<?php
/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\Platform\Resource;

use Illuminate\Support\Arr;

class ActionItem
{
    /**
     * @var array <string,mixed|array>
     */
    protected array $attributes = [];

    /**
     * @var string
     */
    protected string $name;

    /**
     * ActionItem constructor.
     *
     * @param string $name
     */
    public function __construct(string $name)
    {
        $this->name = $name;
    }

    /**
     * add api url.
     *
     * @param string $apiUrl
     *
     * @return $this
     */
    public function apiUrl(string $apiUrl): static
    {
        $this->attributes['apiUrl'] = $apiUrl;

        return $this;
    }

    /**
     * Set page url.
     *
     * @param string $pageUrl
     *
     * @return $this
     */
    public function pageUrl(string $pageUrl): static
    {
        $this->attributes['pageUrl'] = '/' . $pageUrl;

        return $this;
    }

    /**
     * Put link into item.
     *
     * @param  string $link
     * @return $this
     */
    public function link(string $link): static
    {
        $this->attributes['link'] = $link;

        return $this;
    }

    /**
     * Set page as a download action.
     *
     * @return $this
     */
    public function asDownload(): static
    {
        $this->attributes['download'] = true;

        return $this;
    }

    /**
     *  Add api params.
     *
     * @param array $params
     *
     * @return $this
     */
    public function apiParams(array $params): static
    {
        $this->attributes['apiParams'] = $params;

        return $this;
    }

    /**
     *  Add api params.
     *
     * @param array $params
     *
     * @return $this
     */
    public function urlParams(array $params): static
    {
        $this->attributes['urlParams'] = $params;

        return $this;
    }

    /**
     *  Add api rules.
     *
     * @param array $rules
     *
     * @return $this
     */
    public function apiRules(array $rules): static
    {
        $this->attributes['apiRules'] = $rules;

        return $this;
    }

    /**
     * Add api method.
     *
     * @param string $apiMethod
     *
     * @return $this
     */
    public function apiMethod(string $apiMethod): static
    {
        $this->attributes['apiMethod'] = $apiMethod;

        return $this;
    }

    public function asPost()
    {
        return $this->apiMethod('POST');
    }

    public function asPut()
    {
        return $this->apiMethod('PUT');
    }

    public function asGet()
    {
        return $this->apiMethod('GET');
    }

    public function asDelete()
    {
        return $this->apiMethod('DELETE');
    }

    public function asPatch()
    {
        return $this->apiMethod('PATCH');
    }

    /**
     * Get action name.
     *
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Add Confirm.
     * @param  array|null $confirm
     * @return $this
     */
    public function confirm(?array $confirm = null): static
    {
        if (!$confirm) {
            $confirm = ['message' => __p('core::phrase.are_you_absolutely_sure_this_operation_cannot_be_undone')];
        }

        $this->attributes['confirm'] = $confirm;

        return $this;
    }

    /**
     * Add alert.
     * @param  array $alert
     * @return $this
     */
    public function alert(array $alert): static
    {
        $this->attributes['alert'] = $alert;

        return $this;
    }

    /**
     * @return array<string,mixed>
     */
    public function toArray(): array
    {
        return $this->attributes;
    }

    /**
     * @param $name
     * @param $arguments
     *
     * @return $this
     * @ignore
     */
    public function __call($name, $arguments): static
    {
        $this->attributes[$name] = $arguments[0];

        return $this;
    }

    /**
     * Set placeholder.
     *
     * @param  string $string
     * @return $this
     */
    public function placeholder(string $string): static
    {
        $this->attributes['placeholder'] = $string;

        return $this;
    }

    public function asFormDialog(bool $asDialog): static
    {
        $this->attributes['asFormDialog'] = $asDialog;

        return $this;
    }

    public function downloadUrl(string $downloadUrl): static
    {
        $this->asFormDialog(false);
        $this->asDownload(true);
        $this->attributes['pageUrl'] = $downloadUrl;

        return $this;
    }

    /**
     * @param  array $params
     * @return $this
     */
    public function pageParams(array $params): static
    {
        Arr::set($this->attributes, 'pageParams', $params);

        return $this;
    }
}
