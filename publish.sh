#!/bin/bash

vendor/bin/sculpin generate --env=prod
if [ $? -ne 0 ]; then echo "Could not generate the site"; exit 1; fi

mv docs/CNAME output_prod/CNAME
rm -rf docs
mv output_prod docs
git add --all .
git commit -m "New version of jwage.com"
git push origin master
