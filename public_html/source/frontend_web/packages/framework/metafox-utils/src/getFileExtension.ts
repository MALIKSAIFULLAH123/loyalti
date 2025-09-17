export default function getFileExtension(fileName): string {
  if (!fileName || !fileName.includes('.')) return null;

  const extension = fileName.split('.').pop().toLowerCase();

  return extension;
}
