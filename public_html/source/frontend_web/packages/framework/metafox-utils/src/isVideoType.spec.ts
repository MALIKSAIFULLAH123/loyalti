import { isVideoType } from './index';

describe('isVideoType.ts', () => {
  it('isVideoType image/png ', () => {
    expect(isVideoType('image/png')).toBeFalsy();
  });

  it('isVideoType image/gif', () => {
    expect(isVideoType('image/gif')).toBeFalsy();
  });

  it('isVideoType image/png', () => {
    expect(isVideoType('image/png')).toBeFalsy();
  });

  it('isVideoType video/mp4', () => {
    expect(isVideoType('video/mp4')).toBeTruthy();
  });

  it('isVideoType null', () => {
    expect(isVideoType(null)).toBeFalsy();
  });

  it('isVideoType undefined', () => {
    expect(isVideoType(null)).toBeFalsy();
  });
});
