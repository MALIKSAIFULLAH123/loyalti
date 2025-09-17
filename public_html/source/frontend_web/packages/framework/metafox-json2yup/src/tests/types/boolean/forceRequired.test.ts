import to from 'await-to-js';
import { BooleanSchema, ValidationError } from 'yup';
import { toYup } from '../../../toYup';
import { BooleanTypeSchema } from '../../../types';

const errorMsg = 'Missing Required Value';
const schema: BooleanTypeSchema = {
  type: 'boolean',
  errors: {
    required: errorMsg
  }
};

const yupSchema = toYup(schema, true) as BooleanSchema;

test('force required expect pass', async () => {
  expect(yupSchema.isValidSync(true)).toBe(true);
  expect(yupSchema.isValidSync(false)).toBe(true);
});

test('force required expect fail', async () => {
  expect(yupSchema.isValidSync(undefined)).toBe(false);
});

test('force required expect error', async () => {
  const [error] = await to(yupSchema.validate(undefined));
  expect((error as ValidationError).message).toBe(errorMsg);
});
