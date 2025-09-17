const isVideoType = (mime: string) => mime && /video\/*/i.test(mime);

export default isVideoType;
