import getImageSrc from './getImageSrc';

const images = {
  '50x50': 'c4ad8ba7e814_50x50.jpg',
  '120x120': 'c4ad8ba7e814_120x120.jpg',
  '200': 'c4ad8ba7e814_200.jpg',
  '200x200': 'c4ad8ba7e814_200x200.jpg',
  origin: 'c4ad8ba7e814_origin.jpg',
  '75': '10bfd0ef591a_75.jpg',
  '100': '10bfd0ef591a_100.jpg',
  '150': '10bfd0ef591a_150.jpg',
  '240': '10bfd0ef591a_240.jpg',
  '500': '10bfd0ef591a_500.jpg'
};

const images2 = {
  '200x200': 'c4ad8ba7e814_200x200.jpg',
  origin: 'c4ad8ba7e814_origin.jpg',
  '75': '10bfd0ef591a_75.jpg',
  '100': '10bfd0ef591a_100.jpg',
  '150': '10bfd0ef591a_150.jpg',
  '240': '10bfd0ef591a_240.jpg',
  '500': '10bfd0ef591a_500.jpg'
};

it('test image source', () => {
  expect(parseInt('120')).toEqual(120);
  expect(parseInt('120x120')).toEqual(120);
  expect(parseInt('250, 200')).toEqual(250);
  expect(getImageSrc(images, '200x200')).toEqual('c4ad8ba7e814_200x200.jpg');
  expect(getImageSrc(images, '250, 200')).toEqual('c4ad8ba7e814_200.jpg');
  expect(getImageSrc(images, '250')).toEqual('10bfd0ef591a_500.jpg');
  expect(getImageSrc(images2, '200')).toEqual('10bfd0ef591a_240.jpg');
  expect(getImageSrc(images, '240')).toEqual('10bfd0ef591a_240.jpg');
  expect(getImageSrc(images, '1024')).toEqual('c4ad8ba7e814_origin.jpg');

  expect(getImageSrc(images2, '120x120')).toEqual('c4ad8ba7e814_200x200.jpg');
  expect(getImageSrc(images2, '130x130')).toEqual('c4ad8ba7e814_200x200.jpg');
});
