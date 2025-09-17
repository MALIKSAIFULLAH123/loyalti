const isPhotoType = (mime: string) => mime && /image\/*/i.test(mime);

export default isPhotoType;
