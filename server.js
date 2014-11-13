var http = require('http');
var fs = require('fs');
var path = require('path');
var url = require('url');
var config = require('./config.json');

var server = http.createServer(function(req, res){
	 var  dir = path.join(__dirname, 'www')
        ,uri = url.parse(req.url).pathname
        ,filename = path.join(dir, unescape(uri))
        ,indexFilename = path.join(dir, unescape('index.html'))
        ,stats;
      
      try
      {
        stats = fs.lstatSync(filename);
      }
      catch (e)
      {
        stats = false;
      }

      if (stats && stats.isFile())
      {
        // path exists, is a file
        var mimeType = config.server.mimetypes[path.extname(filename)
          .split(".")[1]];
        res.writeHead(200,
          {
            'Content-Type': mimeType
          });

        var fileStream =
          fs.createReadStream(filename)
          .pipe(res);
      }
      else if (stats && stats.isDirectory())
      {
        // path exists, is a directory
        res.writeHead(200,
          {
            'Content-Type': "text/html"
          });
        var fileStream =
          fs.createReadStream(indexFilename)
          .pipe(res);
      }
}).listen(config.server.port, function() {
  console.log('\tServer running at: http://localhost:'+config.server.port+' ...');
});

module.exports = server;