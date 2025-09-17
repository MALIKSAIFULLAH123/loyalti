import to from 'await-to-js';
import * as yup from 'yup';
import { toYup } from '../../../toYup';
import {
  ArrayTypeSchema,
  BuildCustomSchema,
  CustomTypeSchema
} from '../../../types';

const errorMsg = 'custom min 5';

const schema: ArrayTypeSchema = {
  type: 'array',
  strict: true,
  required: true,
  of: {
    type: 'custom'
  }
};

const buildCustom: BuildCustomSchema = (
  schema: CustomTypeSchema,
  forceRequired
) => {
  return yup.number().min(5, errorMsg);
};

const yupSchema = toYup(schema, false, buildCustom);

test('of custom expect fail', async () => {
  expect(yupSchema.isValidSync([0, 1, -1, 4])).toBe(false);
});

test('of custom expect pass', async () => {
  expect(yupSchema.isValidSync([5, 6, 100])).toBe(true);
});

test('of custom expect fail message', async () => {
  const [error] = await to(yupSchema.validate([4]));
  expect(error.message).toBe(errorMsg);
});
