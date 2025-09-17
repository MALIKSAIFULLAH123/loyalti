import { isString } from 'lodash';

export function capitalizeWord(s) {
  if (s && isString(s)) return s.substr(0, 1).toUpperCase() + s.substr(1);
}

const readFile = (file: File) => {
  return new Promise(resolve => {
    const reader = new FileReader();
    reader.addEventListener('load', () => resolve(reader.result), false);
    reader.readAsDataURL(file);
  });
};

export const handleConvertBase64 = async (fileData: File, cb) => {
  if (fileData) {
    const base64 = await readFile(fileData);
    cb(base64);
  }
};
