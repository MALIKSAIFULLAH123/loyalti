import compactUrl from './compactUrl';

describe('compactUrl', () => {
  it('compactUrl', () => {
    expect(compactUrl('/blog/:id', { id: 2 })).toEqual('/blog/2');
  });
  it('compactUrl', () => {
    expect(compactUrl('/blog/?category_id=:id', { id: 2 })).toEqual(
      '/blog/?category_id=2'
    );
  });
});
