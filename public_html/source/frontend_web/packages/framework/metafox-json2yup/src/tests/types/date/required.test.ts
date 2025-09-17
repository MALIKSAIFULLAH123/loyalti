import to from 'await-to-js';
import { DateSchema } from 'yup';
import { toYup } from '../../../toYup';
import { DateTypeSchema } from '../../../types';

const errorMsg = 'Date is required';

const schema: DateTypeSchema = {
  type: 'date',
  strict: true,
  required: true,
  nullable: false,
  errors: {
    required: errorMsg
  }
};

const yupSchema = toYup(schema) as DateSchema;

test('required expect fail', async () => {
  expect(yupSchema.isValidSync(undefined)).toBe(false);
  expect(yupSchema.isValidSync(null)).toBe(false);
  expect(yupSchema.isValidSync([new Date('2021-12-01')])).toBe(false);
});

test('required expect pass', async () => {
  expect(yupSchema.isValidSync(new Date('2019-12-01'))).toBe(true);
  expect(yupSchema.isValidSync(new Date('2020-01-01'))).toBe(true);
  expect(yupSchema.isValidSync(new Date())).toBe(true);
});

test('required expect fail message', async () => {
  const [error] = await to(yupSchema.validate(undefined));
  expect(error.message).toBe(errorMsg);
});
