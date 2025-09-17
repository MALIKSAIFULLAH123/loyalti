/**
 * @type: service
 * name: useLimitFileSize
 */
import useGlobal from './useGlobal';

export default function useLimitFileSize() {
  const { getSetting } = useGlobal();
  const maxFileSize = getSetting('storage.filesystems.max_upload_filesize');

  return maxFileSize || {};
}
