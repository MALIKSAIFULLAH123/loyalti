import to from 'await-to-js';
import { subMonths } from 'date-fns';
import { DateSchema } from 'yup';
import { toYup } from '../../../toYup';
import { DateTypeSchema } from '../../../types';

const errorMsg = 'Max 12 months old';

const schema: DateTypeSchema = {
  type: 'date',
  strict: true,
  required: true,
  maxAgeMonths: 12,
  errors: {
    maxAgeMonths: errorMsg
  }
};

const yupSchema = toYup(schema) as DateSchema;

test('max age months expect fail', async () => {
  expect(yupSchema.isValidSync(subMonths(new Date(), 13))).toBe(false);
  expect(yupSchema.isValidSync(subMonths(new Date(), 20))).toBe(false);
  expect(yupSchema.isValidSync(subMonths(new Date(), 100))).toBe(false);
  expect(yupSchema.isValidSync(subMonths(new Date(), 500))).toBe(false);
});

test('max age months expect pass', async () => {
  expect(yupSchema.isValidSync(subMonths(new Date(), 5))).toBe(true);
  expect(yupSchema.isValidSync(subMonths(new Date(), 6))).toBe(true);
  expect(yupSchema.isValidSync(subMonths(new Date(), 12))).toBe(true);
  expect(yupSchema.isValidSync(subMonths(new Date(), 11))).toBe(true);
  expect(yupSchema.isValidSync(subMonths(new Date(), -1))).toBe(true);
});

test('max age months expect fail message', async () => {
  const [error] = await to(yupSchema.validate(subMonths(new Date(), 13)));
  expect(error.message).toBe(errorMsg);
});
