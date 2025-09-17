<?php

namespace MetaFox\Core;

class Constants
{
    /**
     * Define alias of "*"  locale namespace.
     */
    public const LOCALE_NAMESPACE_ROOT = '_root';

    /**
     * Define alias of "*"  group.
     */
    public const LOCALE_GROUP_ROOT = '_root';

    /**
     * Define data grid value in the `core_drivers` schema.
     */
    public const DRIVER_TYPE_DATA_GRID = 'data-grid';

    /**
     * Define site setting form value in the `core_drivers` schema.
     */
    public const DRIVER_TYPE_FORM_SETTINGS = 'form-settings';

    /**
     * Define JSON resource value in the `core_drivers` schema.
     */
    public const DRIVER_TYPE_JSON_RESOURCE = 'json-resource';

    /**
     * Define mobile resource settings value in the `core_drivers` schema.
     */
    public const DRIVER_TYPE_RESOURCE_ACTIONS = 'resource-mobile';

    /**
     * Define mobile resource settings value in the `core_drivers` schema.
     */
    public const DRIVER_TYPE_RESOURCE_WEB = 'resource-web';

    /**
     * Define package settings.
     */
    public const DRIVER_TYPE_PACKAGE_SETTING = 'package-setting';

    /**
     * Define JSON collection value in the `core_drivers` schema.
     */
    public const DRIVER_TYPE_JSON_COLLECTION = 'json-collection';

    /**
     * Define authorization policy handler in the `core_drivers` schema.
     */
    public const DRIVER_TYPE_POLICY_RESOURCE = 'policy-resource';

    /**
     * Define authorization policy handler in the `core_drivers` schema.
     */
    public const DRIVER_TYPE_POLICY_RULE = 'policy-rule';

    /**
     * Define authorization policy handler in the `core_drivers` schema.
     */
    public const DRIVER_TYPE_MAIL = 'mail';

    /**
     * Define authorization policy handler in the `core_drivers` schema.
     */
    public const DRIVER_TYPE_NOTIFICATION = 'notification';

    /**
     * Define authorization policy handler in the `core_drivers` schema.
     */
    public const DRIVER_TYPE_JOB = 'job';

    /**
     * Define authorization policy handler in the `core_drivers` schema.
     */
    public const DRIVER_TYPE_EVENT = 'event';

    /**
     * Set Driver type form.
     * @see \MetaFox\Core\Models\Driver
     */
    public const DRIVER_TYPE_FORM = 'form';

    /**
     * Set Driver type form.
     * @see \MetaFox\Core\Models\Driver
     */
    public const DRIVER_TYPE_FORM_FIELD = 'form-field';

    /**
     * Set Driver type entity.
     * @see \MetaFox\Core\Models\Driver
     */
    public const DRIVER_TYPE_ENTITY = 'entity';

    /**
     * Set Driver type entity.
     * @see \MetaFox\Core\Models\Driver
     */
    public const DRIVER_TYPE_ENTITY_CONTENT = 'entity-content';

    /**
     * Set Driver type entity.
     * @see \MetaFox\Core\Models\Driver
     */
    public const DRIVER_TYPE_ENTITY_USER = 'entity-user';

    public const DRIVER_TYPE_USER_GATEWAY_FORM = 'form-user-gateway';

    /**
     * Define site setting form value in the `file-converter` schema.
     */
    public const DRIVER_TYPE_FILE_CONVERTER = 'file-converter';

    /**
     * Set Driver type entity.
     * @see \MetaFox\Core\Models\Driver
     */
    public const DRIVER_TYPE_CUSTOM_FIELD_VALIDATOR = 'custom-field-validator';

    public const AVAILABLE_DRIVER_TYPES = [
        'Entity'                 => self::DRIVER_TYPE_ENTITY,
        'Content Type'           => self::DRIVER_TYPE_ENTITY_USER,
        'Data Grid'              => self::DRIVER_TYPE_DATA_GRID,
        'Json Resource'          => self::DRIVER_TYPE_JSON_RESOURCE,
        'Resource Web'           => self::DRIVER_TYPE_RESOURCE_WEB,
        'Resource Mobile'        => self::DRIVER_TYPE_RESOURCE_ACTIONS,
        'Policy'                 => self::DRIVER_TYPE_POLICY_RESOURCE,
        'Policy Rule'            => self::DRIVER_TYPE_POLICY_RULE,
        'Form'                   => self::DRIVER_TYPE_FORM,
        'Form Field'             => self::DRIVER_TYPE_FORM_FIELD,
        'Form Settings'          => self::DRIVER_TYPE_FORM_SETTINGS,
        'Notification'           => self::DRIVER_TYPE_NOTIFICATION,
        'Mail'                   => self::DRIVER_TYPE_MAIL,
        'Job'                    => self::DRIVER_TYPE_JOB,
        'File Converter'         => self::DRIVER_TYPE_FILE_CONVERTER,
        'Custom Field Validator' => self::DRIVER_TYPE_CUSTOM_FIELD_VALIDATOR,
    ];

    public const FORCE_DISPLAY_DARK_MODE = 'dark';
    public const FORCE_DISPLAY_LIGHT_MODE = 'light';
}
