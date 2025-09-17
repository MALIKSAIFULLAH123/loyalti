<?php

namespace MetaFox\Platform\Contracts;

interface HasSubCategory
{
    /**
     * @return string
     */
    public function toSubCategoriesLink(): string;

    /**
     * @return string
     */
    public function toSubCategoriesUrl(): string;
}
