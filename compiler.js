#!/usr/bin/node
var pegjs = require("pegjs");
var phppegjs = require("php-pegjs");
var fs = require('fs')

fs.readFile('grammar.pegjs', 'utf8', function (err, data) {
  if (err) {
    return console.log(err);
  }

  try {
    var parser = pegjs.buildParser(data, {
      phppegjs: {
        parserNamespace: 'ECL',
        parserClassName: 'InternalParser'
      },
      plugins: [phppegjs]
    });

    console.log(parser);
  } catch(e) {
    console.log(e);
  }
});
