import useFirebaseInit from './useFirebaseInit';

export default function useFirebaseFireStore(): any {
  const [active, firebaseBackend] = useFirebaseInit();

  if (!active) {
    return false;
  }

  const db = firebaseBackend.getFirestore();

  return db;
}
