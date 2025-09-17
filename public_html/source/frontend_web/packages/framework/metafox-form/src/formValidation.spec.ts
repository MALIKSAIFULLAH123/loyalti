import { toYup as buildYup } from '@metafox/json2yup';
import to from 'await-to-js';

describe('json-2-yup', () => {
  const schema = {
    type: 'object',
    properties: {
      question: {
        type: 'string',
        required: true,
        minLength: 3,
        maxLength: 255,
        title: 'Question'
      },
      answers: {
        type: 'array',
        field: 'Answers',
        min: 2,
        max: 5,
        required: true,
        of: {
          type: 'object',
          properties: {
            answer: {
              type: 'string',
              required: true,
              maxLength: 255,
              minLength: 3
            }
          }
        }
      }
    }
  };

  it('+pass', async () => {
    const yupSchema = buildYup(schema as any);
    // console.dir(schema)

    const valid = await yupSchema.isValid({
      question: 'what is best of metafox',
      answers: [{ answer: 'good performance' }, { answer: 'nice architect' }]
    });

    expect(valid).toEqual(true);
  });

  it('+pass - %', async () => {
    const yupSchema = buildYup(schema as any);
    // console.dir(schema)

    // required min
    const valid = await yupSchema.isValid({
      question: 'what is best of metafox',
      answers: [{ answer: 'good performance' }]
    });

    expect(valid).toEqual(false);
  });

  it('+pass - %', async () => {
    const yupSchema = buildYup(schema as any);
    // console.dir(schema)

    // required answers
    const valid = await yupSchema.isValid({
      question: 'what is best of metafox'
    });

    expect(valid).toEqual(false);
  });

  it('+pass - %', async () => {
    const yupSchema = buildYup(schema as any);
    // console.dir(schema)

    // required answers
    const [error] = await to(
      yupSchema.validate({
        question: 'what is best of metafox'
      })
    );

    expect(error.message).toEqual('answers is a required field');
  });
});
