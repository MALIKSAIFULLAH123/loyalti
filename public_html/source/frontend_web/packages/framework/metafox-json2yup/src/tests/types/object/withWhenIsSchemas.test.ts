import { toYup } from '../../../toYup';
import { ObjectTypeSchema } from '../../../types';

const schema: ObjectTypeSchema = {
  type: 'object',
  properties: {
    loanAmount: {
      type: 'number',
      max: 35000
    },
    things: {
      type: 'string',
      oneOf: ['2'],
      when: [
        {
          fields: 'loanAmount',
          is: { type: 'number', lessThan: 15001 },
          then: {
            type: 'string',
            oneOf: ['1']
          }
        }
      ]
    }
  }
};

const yupSchema = toYup(schema) as any;

test('when number expect fail', async () => {
  expect(
    yupSchema.isValidSync({
      loanAmount: 20000,
      things: '1'
    })
  ).toBe(false);
  expect(
    yupSchema.isValidSync({
      loanAmount: 10000,
      things: '2'
    })
  ).toBe(false);
});

test('when number expect pass', async () => {
  expect(
    yupSchema.isValidSync({
      loanAmount: 20000,
      things: '2'
    })
  ).toBe(true);
  expect(
    yupSchema.isValidSync({
      loanAmount: 10000,
      things: '1'
    })
  ).toBe(true);
});
