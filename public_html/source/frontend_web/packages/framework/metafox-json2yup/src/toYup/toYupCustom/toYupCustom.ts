import { BuildCustomSchema, CustomTypeSchema } from '../../types';

const toYupCustom = (
  jsonSchema: CustomTypeSchema,
  builder: BuildCustomSchema,
  forceRequired?: boolean
) => {
  if (builder != null) {
    return builder(jsonSchema, forceRequired);
  }

  return null;
};

export default toYupCustom;
