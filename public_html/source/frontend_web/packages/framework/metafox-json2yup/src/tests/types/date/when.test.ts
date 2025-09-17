import to from 'await-to-js';
import { ValidationError } from 'yup';
import { toUnix } from '../../../lib/date';
import { toYup } from '../../../toYup';
import { ObjectTypeSchema } from '../../../types';

const schema: ObjectTypeSchema = {
  type: 'object',
  strict: true,
  properties: {
    future: {
      type: 'boolean',
      strict: true,
      required: true
    },
    date: {
      type: 'date',
      strict: true,
      required: true,
      when: [
        {
          fields: 'future',
          is: true,
          then: {
            type: 'date',
            min: toUnix(new Date()),
            errors: {
              min: 'Date must be in future when future is true'
            }
          },
          otherwise: {
            type: 'date',
            max: toUnix(new Date()) - 1,
            errors: {
              max: 'Date must be in past when future is true'
            }
          }
        }
      ]
    }
  }
};

const yupSchema = toYup(schema) as any;

test('when date expect fail', async () => {
  expect(
    yupSchema.isValidSync({
      future: true,
      date: new Date(new Date().getTime() - 10000)
    })
  ).toBe(false);

  expect(
    yupSchema.isValidSync({
      future: false,
      date: new Date(new Date().getTime() + 10000)
    })
  ).toBe(false);
});

test('when date expect pass', async () => {
  expect(
    yupSchema.isValidSync({
      future: true,
      date: new Date(new Date().getTime() + 10000)
    })
  ).toBe(true);
  expect(
    yupSchema.isValidSync({
      future: false,
      date: new Date(new Date().getTime() - 10000)
    })
  ).toBe(true);
});

test('when date future true expect errors', async () => {
  const [error] = await to(
    yupSchema.validate({
      future: true,
      date: new Date(new Date().getTime() - 10000)
    })
  );
  const yupError = error as ValidationError;

  expect(yupError.errors).toContain(
    'Date must be in future when future is true'
  );
});

test('when date future false expect errors', async () => {
  const [error] = await to(
    yupSchema.validate({
      future: false,
      date: new Date(new Date().getTime() + 10000)
    })
  );
  const yupError = error as ValidationError;
  expect(yupError.errors).toContain('Date must be in past when future is true');
});
