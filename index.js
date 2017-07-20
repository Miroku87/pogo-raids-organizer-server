var p = require('child_process').spawn( 'cmd', [ '/K', 'tasklist']);
var rl = require('readline').createInterface({
	input: p.stdin,
	output: p.stdout
});
p.stdout.on('data', function(line){
	console.log(">>",line.toString());
});