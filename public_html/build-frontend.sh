#!/bin/bash

docker-compose exec frontend-build yarn bundle:release

if [[ -d ./source/frontend_web/app/dist ]] 
then
    rm -rf ./public_html/storage/app/web
    cp -rf ./source/frontend_web/app/dist ./public_html/storage/app/web
fi