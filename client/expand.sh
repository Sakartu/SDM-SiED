#!/bin/sh

# Iteratively replaces tabs in .php files with 4 spaces.
find . -name "*.php" |while read line
do
expand --tabs=4 $line > $line.new
mv $line.new $line
done
