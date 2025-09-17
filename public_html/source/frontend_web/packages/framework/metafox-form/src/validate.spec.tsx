import * as yup from 'yup';

describe('Create yup', () => {
  it('Create by string', () => {
    const shape = yup.string();
    expect(shape).toBeDefined();
  });
});
