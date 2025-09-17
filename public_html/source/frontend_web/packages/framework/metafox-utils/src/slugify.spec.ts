import slugify from './slugify';

describe('slugify', () => {
  it('slugify("Untitled Title")', () => {
    expect(slugify('Untitled Title')).toEqual('untitled-title');
  });
  it('slugify multiple languages', () => {
    expect(slugify('Tôi thích framework này')).toEqual(
      'tôi-thích-framework-này'
    );
    expect(slugify('わたしは、あなたを愛しています')).toEqual(
      'わたしは、あなたを愛しています'
    );
    expect(slugify('أحبك')).toEqual('أحبك');
    expect(slugify('Family & Home')).toEqual('family-&-home');
  });
});
