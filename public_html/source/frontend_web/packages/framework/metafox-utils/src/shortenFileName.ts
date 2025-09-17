export default function shortenFileName(fileName, length): string {
  if (!fileName || !fileName.includes('.')) return null;

  const extension = fileName.split('.').pop().toLowerCase();

  const shortFileName = fileName.slice(0, -(extension.length + 1));

  if (shortFileName.length < length) return fileName;

  const result = `${shortFileName.slice(0, length)}...${extension}`;

  return result;
}
