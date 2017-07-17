var
	HOST_NAME       = 'localhost',
	SERVER_PORT     = 9000,
	LIVERELOAD_PORT = 35729;
  
module.exports = function (grunt)
{
	require("matchdep").filterDev("grunt-*").forEach( grunt.loadNpmTasks );
	
	grunt.initConfig(
	{
		pkg: grunt.file.readJSON('package.json'),
		exec: {
			start_mysql: {
				command: 'start C:\\xampp\\mysql\\bin\\mysqld.exe'
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
	
	grunt.registerTask( 'default', [ 'exec:start_mysql', 'php:private' ] );
};