#!/bin/bash

mkdir ./public_html

unzip ./upload.zip -d ./public_html/

cp ./public_html/htaccess.example ./public_html/.htaccess

chown -R 1:1 ./public_html

if [ ! -f ./docker/server.env ]
then
  cp ./docker/server.env.sample ./docker/server.env
fi

docker-compose up -d
