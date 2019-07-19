clean:
	@find . -iname \*~ -exec rm -rfv {} \;
	@if [ ! -d session_files ]; then mkdir session_files; fi; 
	@chmod 777 session_files
	@chmod 666 include/conf.inc
	@chmod 700 db
	@chmod 600 db/onde.backup-dev.sql*


