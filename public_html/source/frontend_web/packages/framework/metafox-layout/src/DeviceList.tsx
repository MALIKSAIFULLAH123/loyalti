type DeviceItem = {
  label: string;
  value: string;
  size: string;
  note?: string;
  icon?: string;
};

const DeviceMap: DeviceItem[] = [
  {
    icon: 'ico-mobile-o',
    label: 'Pixel 2XL',
    size: '411x823',
    value: 'pixel.2xl'
  },
  {
    icon: 'ico-mobile-o',
    label: 'iPhone X',
    size: '375x812',
    value: 'iphone.x'
  },
  {
    icon: 'ico-mobile-o',
    label: 'iPad',
    size: '768x1024',
    value: 'ipad'
  },
  {
    icon: 'ico-mobile-o',
    label: 'iPad mini',
    size: '1024x768',
    value: 'ipad.mini'
  },
  {
    icon: 'ico-mobile-o',
    label: 'Laptop',
    size: '1280x900',
    value: 'laptop.1280'
  }
];

export default DeviceMap;
