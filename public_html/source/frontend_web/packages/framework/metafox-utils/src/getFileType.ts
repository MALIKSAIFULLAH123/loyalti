const isHeicFile = (file: File) => {
  if (file?.type) return false;

  const fileName = file.name.toLowerCase();
  const fileExtension = fileName.split('.').pop();

  return fileExtension === 'heic' || fileExtension === 'heif';
};

const getFileType = (file: File) => {
  if (!file) return '';

  if (isHeicFile(file)) {
    return 'image/heic';
  }

  return file?.type;
};
export default getFileType;
