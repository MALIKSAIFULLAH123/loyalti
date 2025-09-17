# Docker image for build frontend as a service
FROM node:20.15

# setup docker in docker
RUN apt-get -y update \
    && apt-get -y autoremove \
    && apt-get clean \
    && apt-get install -y curl \
    zip \
    unzip 

COPY . /app

RUN mkdir /app/dist

WORKDIR /app

RUN yarn install --network-timeout=100000
RUN yarn install --network-timeout=100000

ENV MFOX_WORKING_DIR=/app
