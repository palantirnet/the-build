#!/bin/bash

# Setup tests that read output before testing.
test='the-build'
vendor/bin/phing -logfile "tests/$test.txt" $test
vendor/bin/phing run-tests
