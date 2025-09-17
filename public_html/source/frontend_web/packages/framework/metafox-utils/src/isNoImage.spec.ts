import isNoImage from './isNoImage';

describe('isNoImage', () => {
  it('no_imag.png', () => {
    expect(
      isNoImage('/assets/images/default-images/blog/no_image.png')
    ).toBeTruthy();
    expect(isNoImage('')).toBeTruthy();
    expect(isNoImage(undefined)).toBeTruthy();
    expect(isNoImage('/assets/images/default-images/abc.jpg')).toBeFalsy();
  });
});
