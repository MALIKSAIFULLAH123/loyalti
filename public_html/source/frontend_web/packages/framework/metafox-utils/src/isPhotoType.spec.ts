import { isPhotoType } from './index';

describe('isPhotoType.ts', () => {
  it('isPhotoType ', () => {
    expect(isPhotoType('image/png')).toBeTruthy();
  });

  it('isPhotoType ', () => {
    expect(isPhotoType('image/gif')).toBeTruthy();
  });

  it('isPhotoType ', () => {
    expect(isPhotoType('image/png')).toBeTruthy();
  });

  it('isPhotoType ', () => {
    expect(isPhotoType('video/*')).toBeFalsy();
  });
});
