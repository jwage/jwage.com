#!/bin/bash

./build.sh prod

git add --all .
git commit -m "New version of jwage.com"
git push origin master

# Put the dev version back after deploying prod
./build.sh dev
