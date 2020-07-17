clean:
	@find . -iname \*~ -exec rm -rfv {} \;							# Remove unnecessary files
	@if [ ! -d web/session_files ]; then mkdir web/session_files; fi; 				# check if sesseion_files folder exists, and create it otherwise
	@chmod 777 web/session_files								# set web/session_files folder globally writeable 
	@if [ ! -d web/include ]; then mkdir web/include; fi; 						# check if web/include folder exists, and create it otherwise
	@if [ ! -f web/include/conf.inc ]; then touch web/include/conf.inc; fi;				# check if conf.inc file exists, and create it otherwise
	@chmod 666 web/include/conf.inc								# check if conf.inc folder exists, and create it otherwise
	@if [ ! -f web/include/conf.new.buffer.inc ]; then touch web/include/conf.new.buffer.inc; fi; 
	@chmod 666 web/include/conf.new.buffer.inc
	@if [ ! -d db ]; then mkdir db; fi; 
	@chmod 700 db
	@chmod 600 db/onde.backup-dev.sql*


