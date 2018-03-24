#!/bin/bash

vendor/bin/sculpin generate --env=$1
if [ $? -ne 0 ]; then echo "Could not generate the site"; exit 1; fi

mv docs/CNAME output_$1/CNAME
rm -rf docs
mv output_$1 docs
