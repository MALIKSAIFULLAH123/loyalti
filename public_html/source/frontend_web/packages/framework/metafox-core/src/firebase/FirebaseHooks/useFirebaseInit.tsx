import React from 'react';
import { useGlobal } from '@metafox/framework';
import FirebaseBackend from '../Firebase';

const firebaseBackend = new FirebaseBackend();
let inited = false;
let activeGlobal = false;

export default function useFirebaseInit(): any {
  const manager = useGlobal();
  const [active, setActive] = React.useState(false);

  if (!active && !inited) {
    inited = true;
    firebaseBackend.bootstrap(manager, () => {
      activeGlobal = true;

      setActive(true);
    });
  }

  return [active || activeGlobal, firebaseBackend];
}
