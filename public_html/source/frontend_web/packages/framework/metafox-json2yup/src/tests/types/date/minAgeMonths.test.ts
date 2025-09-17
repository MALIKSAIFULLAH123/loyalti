import to from 'await-to-js';
import { subMonths } from 'date-fns';
import { DateSchema } from 'yup';
import { toYup } from '../../../toYup';
import { DateTypeSchema } from '../../../types';

const errorMsg = 'Min 5 months old';

const schema: DateTypeSchema = {
  type: 'date',
  strict: true,
  required: true,
  minAgeMonths: 5,
  errors: {
    minAgeMonths: errorMsg
  }
};

const yupSchema = toYup(schema) as DateSchema;

test('min age months expect fail', async () => {
  expect(yupSchema.isValidSync(new Date())).toBe(false);
  expect(yupSchema.isValidSync(subMonths(new Date(), 4))).toBe(false);
  expect(yupSchema.isValidSync(subMonths(new Date(), 0))).toBe(false);
  expect(yupSchema.isValidSync(subMonths(new Date(), 1))).toBe(false);
  expect(yupSchema.isValidSync(subMonths(new Date(), -1))).toBe(false);
});

test('min age months expect pass', async () => {
  expect(yupSchema.isValidSync(subMonths(new Date(), 5))).toBe(true);
  expect(yupSchema.isValidSync(subMonths(new Date(), 6))).toBe(true);
  expect(yupSchema.isValidSync(subMonths(new Date(), 100))).toBe(true);
});

test('min age months expect fail message', async () => {
  const [error] = await to(yupSchema.validate(subMonths(new Date(), 4)));
  expect(error.message).toBe(errorMsg);
});
