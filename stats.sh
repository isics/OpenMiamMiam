#!/bin/sh

php_files=`find src/ -name "*.php"`
php_lines=`cat $php_files | wc -l`

twig_files=`find src/ -name "*.twig"`
twig_lines=`cat $twig_files | wc -l`

less_files=`find src/ -name "*.less"`
less_lines=`cat $less_files | wc -l`

js_files=`find src/ -name "*.js"`
js_lines=`cat $js_files | wc -l`

echo "$php_lines lines of PHP"
echo "$twig_lines lines of Twig"
echo "$less_lines lines of Less"
echo "$js_lines lines of Javascript"
