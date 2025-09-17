import { isEmpty } from 'lodash';

const checkFileAccept = (typeFile: any, accept: any) => {
  let result = false;

  if (isEmpty(typeFile)) return true;

  if (isEmpty(accept)) return true;

  const acceptData = accept.split(',');

  if (acceptData.some(item => typeFile.match(item))) {
    result = true;
  }

  return result;
};
export default checkFileAccept;
