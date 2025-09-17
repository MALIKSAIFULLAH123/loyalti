import nextId from './nextId';

describe('nextId()', () => {
  it('nextId()', () => {
    const a = nextId();
    const b = nextId();
    expect(a !== b).toBeTruthy();
  });
});
