<?php

namespace MetaFox\Yup;

/**
 * Class Yup
 * Handle Shape.
 * @link     https://dev-docs.metafoxapp.com/frontend/validation
 * @category framework
 */
final class Yup
{
    /**
     * @return ObjectShape
     */
    public static function object(): ObjectShape
    {
        return new ObjectShape();
    }

    /**
     * Add string type.
     *
     * @return StringShape
     */
    public static function string(): StringShape
    {
        return new StringShape();
    }

    public static function number(): NumberShape
    {
        return new NumberShape();
    }

    public static function date(): DateShape
    {
        return new DateShape();
    }

    public static function boolean(): BooleanShape
    {
        return new BooleanShape();
    }

    /**
     * @param string|string[] $field
     *
     * @return WhenShape
     */
    public static function when($field): WhenShape
    {
        return new WhenShape($field);
    }

    /**
     * @return ArrayShape
     */
    public static function array(): ArrayShape
    {
        return new ArrayShape();
    }
}
