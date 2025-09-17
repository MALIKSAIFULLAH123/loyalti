import * as yup from 'yup';

describe('General validate function', () => {
  describe('string validate', () => {
    const validateSchema = yup
      .string()
      .min(5, 'This string is too short!')
      .max(10, 'This string is too long!')
      .required('This string is required')
      .nullable();

    const isValid = (message: string): Promise<Error> =>
      validateSchema.validate(message).catch(error => error);

    it('string: null', async () => {
      expect((await isValid(null)).message).toEqual('This string is required');

      expect((await isValid(undefined)).message).toEqual(
        'This string is required'
      );

      expect((await isValid('Hi')).message).toEqual(
        'This string is too short!'
      );

      expect((await isValid('Hello world')).message).toEqual(
        'This string is too long!'
      );
    });
  });
});
