/* eslint-disable react-hooks/rules-of-hooks */
import useScript from './useScript';
import { CORE_GOOGLE_GOOGLE_MAP_API_KEY } from '@metafox/framework';

export default function useGoogleMap() {
  return useScript(
    `https://maps.googleapis.com/maps/api/js?key=${CORE_GOOGLE_GOOGLE_MAP_API_KEY}&libraries=maps,places`
  );
}
