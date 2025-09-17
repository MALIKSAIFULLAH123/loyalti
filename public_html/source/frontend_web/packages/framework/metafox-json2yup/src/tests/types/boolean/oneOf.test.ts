import to from 'await-to-js';
import { BooleanSchema, ValidationError } from 'yup';
import { toYup } from '../../../toYup';
import { BooleanTypeSchema } from '../../../types';

const errorMsg = 'Must be true';

const schema: BooleanTypeSchema = {
  type: 'boolean',
  strict: true,
  oneOf: [true],
  errors: {
    oneOf: errorMsg
  }
};

const yupSchema = toYup(schema) as BooleanSchema;

test('oneOf expect fail', async () => {
  expect(yupSchema.isValidSync(false)).toBe(false);
});

test('oneOf expect pass', async () => {
  expect(yupSchema.isValidSync(true)).toBe(true);
});

test('oneOf expect fail message', async () => {
  const [error] = await to(yupSchema.validate(false));
  expect((error as ValidationError).message).toBe(errorMsg);
});
