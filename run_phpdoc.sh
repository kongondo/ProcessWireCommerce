#!/bin/bash
mv vendor vendor_temp
phpdoc -d . -t site/api --ignore "vendor_temp/,node_modules/,site/,docs/,tests/,test/,.git/,.github/" --template="default"
EXIT_CODE=$?
mv vendor_temp vendor
exit $EXIT_CODE
