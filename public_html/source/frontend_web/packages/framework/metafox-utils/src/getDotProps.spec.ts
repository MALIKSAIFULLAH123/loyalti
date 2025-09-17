import getDotProps from './getDotProps';

describe('getDotProps', () => {
  it('Simple object', () => {
    const dots = getDotProps({ x: 1, y: 'name' });
    expect(dots.includes('x')).toBeTruthy();
    expect(dots.includes('y')).toBeTruthy();
    expect(dots.includes('z')).toBeFalsy();
  });
  it('Nested object', () => {
    const dots = getDotProps({ x: 1, y: { c1: 'a', c2: 'b' } });
    expect(dots.includes('x')).toBeTruthy();
    expect(dots.includes('y')).toBeFalsy();
    expect(dots.includes('y.c1')).toBeTruthy();
    expect(dots.includes('y.c2')).toBeTruthy();
  });
});
