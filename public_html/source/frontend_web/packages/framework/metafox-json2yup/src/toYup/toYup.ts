import toYupString from './toYupString';
import toYupNumber from './toYupNumber';
import toYupBoolean from './toYupBoolean';
import toYupDate from './toYupDate';
import toYupObject from './toYupObject';
import toYupArray from './toYupArray';
import toYupCustom from './toYupCustom';
import {
  ArrayTypeSchema,
  BooleanTypeSchema,
  BuildCustomSchema,
  CustomTypeSchema,
  DateTypeSchema,
  NumberTypeSchema,
  ObjectTypeSchema,
  StringTypeSchema,
  YupTypeSchema
} from '../types';

const toYup = (
  schema: YupTypeSchema,
  forceRequired: boolean = false,
  customBuilder?: BuildCustomSchema
) => {
  if (!schema.type && (schema as any).properties) {
    schema.type = 'object';
  }

  switch (schema.type) {
    case 'string':
      return toYupString(schema as StringTypeSchema, forceRequired);
    case 'number':
      return toYupNumber(schema as NumberTypeSchema, forceRequired);
    case 'boolean':
      return toYupBoolean(schema as BooleanTypeSchema, forceRequired);
    case 'date':
      return toYupDate(schema as DateTypeSchema, forceRequired);
    case 'object':
      return toYupObject(
        schema as ObjectTypeSchema,
        forceRequired,
        customBuilder
      );
    case 'array':
      return toYupArray(
        schema as ArrayTypeSchema,
        forceRequired,
        customBuilder
      );
    case 'custom':
      return toYupCustom(
        schema as CustomTypeSchema,
        customBuilder,
        forceRequired
      );
  }
};

export default toYup;
