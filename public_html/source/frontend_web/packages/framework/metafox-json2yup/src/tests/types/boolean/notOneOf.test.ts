import to from 'await-to-js';
import { BooleanSchema, ValidationError } from 'yup';
import { toYup } from '../../../toYup';
import { BooleanTypeSchema } from '../../../types';

const errorMsg = 'Should not be false';

const schema: BooleanTypeSchema = {
  type: 'boolean',
  strict: true,
  notOneOf: [false],
  errors: {
    notOneOf: errorMsg
  }
};

const yupSchema = toYup(schema) as BooleanSchema;

test('notOneOf expect fail', async () => {
  expect(yupSchema.isValidSync(false)).toBe(false);
});

test('notOneOf expect pass', async () => {
  expect(yupSchema.isValidSync(true)).toBe(true);
});

test('notOneOf expect fail message', async () => {
  const [error] = await to(yupSchema.validate(false));
  expect((error as ValidationError).message).toBe(errorMsg);
});
