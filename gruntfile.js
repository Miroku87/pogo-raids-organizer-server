const
	HOST_NAME       = 'localhost',
	SERVER_PORT     = 9000,
	LIVERELOAD_PORT = 35729,
	MYSQLD_BIN      = 'C:\\xampp\\mysql\\bin\\mysqld.exe';
  
module.exports = function (grunt)
{
	require("matchdep").filterDev("grunt-*").forEach( grunt.loadNpmTasks );
	
	grunt.initConfig(
	{
		pkg: grunt.file.readJSON('package.json'),
		exec: {
			start_mysql: {
				command: 'start ' + MYSQLD_BIN
				//C:\xampp\mysql\bin\mysqladmin.exe -u root -p shutdown
				//https://stackoverflow.com/questions/16198327/killing-spawned-processes-when-grunt-exits
				//https://gruntjs.com/api/grunt.util#grunt.util.spawn
			}
		},
		php: {
			private: {
				options: {
					hostname: HOST_NAME,
					port: SERVER_PORT,
					silent: true,
					base: './src',
					keepalive:true
				}
			},
			public: {
				options: {
					hostname: '0.0.0.0',
					port: SERVER_PORT,
					silent: true,
					base: './src'
				}
			}
		},
		watch: {
			options: {
				livereload: {
					host: HOST_NAME
				},
				nospawn: false
			},
			dev: {
				files : [ './app/**/**','!./app/**/**.styl' ],
				tasks: []
			},
			stylus: {
				files : [ './app/**/**.styl' ],
				tasks: ['stylus']
			}
		}
	});

	function killMySQL() 
	{
		grunt.log.writeln('killing mysql_process...');
		
		var p = require('child_process').spawn( 'cmd', [ '/C', 'taskkill', '/F', '/IM', 'mysqld.exe' ]);
		/*p.stdout.on('data', function(line){
			if(!/^\s+$/.test(line.toString()))
				console.log(line.toString());
		});*/
		p.on('exit', function(line){
			grunt.log.writeln('killed mysql_process');
			process.exit(0);
		});
		p.on('close', function(line){
			grunt.log.writeln('killed mysql_process');
			process.exit(0);
		});
	}
	
	grunt.registerTask('startMySQL', function() 
	{
		var mysql_process = require('child_process').spawn( 'cmd', [ '/K', 'start', MYSQLD_BIN ], { killSignal: 'SIGINT' } );
	
		var rl = require('readline').createInterface({
			input: mysql_process.stdin,
			output: mysql_process.stdout
		});
		
		rl.on('SIGINT', function() {
			process.emit('SIGINT');
		});
		
		process.on('exit', killMySQL);
		process.on('SIGINT', killMySQL);
		process.on('SIGHUP', killMySQL);
		process.on('SIGBREAK', killMySQL);
	});
	
	grunt.registerTask( 'default', [ 'startMySQL', 'php:private' ] );
};