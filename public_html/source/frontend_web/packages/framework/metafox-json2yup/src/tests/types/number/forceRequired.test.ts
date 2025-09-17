import to from 'await-to-js';
import { NumberSchema, ValidationError } from 'yup';
import { toYup } from '../../../toYup';
import { NumberTypeSchema } from '../../../types';

const errorMsg = 'Missing Required Value';
const schema: NumberTypeSchema = {
  type: 'number',
  errors: {
    required: errorMsg
  }
};

const yupSchema = toYup(schema, true) as NumberSchema;

test('force required expect pass', async () => {
  expect(yupSchema.isValidSync(1)).toBe(true);
});

test('force required expect fail', async () => {
  expect(yupSchema.isValidSync(undefined)).toBe(false);
});

test('force required expect error', async () => {
  const [error] = await to(yupSchema.validate(undefined));
  expect((error as ValidationError).message).toBe(errorMsg);
});
