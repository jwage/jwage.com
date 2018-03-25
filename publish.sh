#!/bin/bash

./build.sh prod

git add --all .
git commit -m "New version of jwage.com"
git push origin master

./build.sh dev
