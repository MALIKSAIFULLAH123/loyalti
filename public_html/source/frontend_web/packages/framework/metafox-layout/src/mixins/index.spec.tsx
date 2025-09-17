import pxToRem from './pxToRem';
import truncateText from './truncateText';

describe('mixins', () => {
  it('+pixelToRem', () => {
    expect(pxToRem()(16)).toEqual('1rem');
  });
  it('+truncateText', () => {
    expect(truncateText({})('16', 21, 3, true)).toMatchObject({
      WebkitBoxOrient: 'vertical',
      WebkitLineClamp: 3,
      display: '-webkit-box',
      fontSize: '16',
      height: 'calc(21 * 16 * 3)',
      lineHeight: 21,
      maxWidth: '100%',
      overflow: 'hidden',
      padding: '0',
      textOverflow: 'ellipsis',
      whiteSpace: 'normal'
    });
  });
});
