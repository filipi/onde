#!/bin/bash

databasename=onde
databaseuser=onde
databasehostname=localhost

# to use $databasehostname add "-h $databasehostname" in each database command

nofprocs=`cat /proc/cpuinfo  | grep processor | wc -l`
if [[ -f '/usr/bin/pbzip2' ]]; then packager="pbzip2"; else packager="bzip2"; fi

if [[ $nofprocs > "1" ]]; then
    echo $nofprocs "processors detected.";
else
    echo "Only 1 processor detected.";
fi

function echoOk {
    echo -en '\E[37;40m'"\033[1m[\033[0m"
    echo -en '\E[32;40m'"\033[1m  OK  \033[0m"
    echo -e  '\E[37;40m'"\033[1m]\033[0m"
}

function echoFalhou {
    echo -en '\E[37;40m'"\033[1m[\033[0m"
    echo -en '\E[31;40m'"\033[1mFALHOU\033[0m"
    echo -e  '\E[37;40m'"\033[1m]\033[0m"
}


#-----------------------------------------------------------------#
echo -n "Verificando se ./$databasename.backup-dev.sql existe..........."
if [ ! -e ./$databasename.backup-dev.sql ]; then
    echoFalhou
    echo -n "Verificando se ./$databasename.backup-dev.sql.bz2 existe......."
    if [ ! -e ./$databasename.backup-dev.sql.bz2 ]; then
        echoFalhou	
	echo "Nao encontrado o backup (desistindo)"
	exit 1
    else
        echoOk
	backup="./$databasename.backup-dev.sql.bz2"
    fi
else
    echoOk
    backup="./$databasename.backup-dev.sql"
fi

if [ ${backup} == "./$databasename.backup-dev.sql.bz2" ]; then    
    echo -n "Descompactando backup do schema:......................"

    if [ $packager == 'bzip2' ]; then	
	#bzip2 $backupfilename;
	if ( bunzip2 -q $databasename.backup-dev.sql.bz2 ); then
	    echoOk      #------------------------------------------------------------#
	    echo -n     "Removendo banco......................................."
	    if ( dropdb $databasename -U $databaseuser ); then
		echoOk
	    else
		echoFalhou
	    fi          #------------------------------------------------------------#
	    echo -n     "Criando o banco novamente............................."
	    if ( createdb $databasename -U $databaseuser ); then
		echoOk  #---------------------------------------------------------------#
		echo -n "Populando tabelas($databasename.backup-dev.sql)................"
		if ( psql $databasename -U $databaseuser -q -f $databasename.backup-dev.sql -o reseta_banco.log); then
		    echoOk
		else
		    echoFalhou
		fi
	    else
		echoFalhou
	    fi
	else
	    echo ""
	    echo -n "............................................................"	
	    echoFalhou
	    echo "Erro ao descompactar $databasename.backup-dev.sql.bz2 (desistindo)"
	    exit 1 	
	fi
    else	
	#pbzip2 -n$nofprocs $backupfilename;
	if ( pbzip2 -p$nofprocs -q -d $databasename.backup-dev.sql.bz2 ); then
	    echoOk      #------------------------------------------------------------#
	    echo -n     "Removendo banco......................................."
	    if ( dropdb $databasename -U $databaseuser ); then
		echoOk
	    else
		echoFalhou
	    fi          #------------------------------------------------------------#
	    echo -n     "Criando o banco novamente............................."
	    if ( createdb $databasename -U $databaseuser ); then
		echoOk  #---------------------------------------------------------------#
		echo -n "Populando tabelas($databasename.backup-dev.sql)................"
		if ( psql $databasename -U $databaseuser -q -f $databasename.backup-dev.sql -o reseta_banco.log); then
		    echoOk
		else
		    echoFalhou
		fi
	    else
		echoFalhou
	    fi
	else
	    echo ""
	    echo -n "............................................................"	
	    echoFalhou
	    echo "Erro ao descompactar $databasename.backup-dev.sql.bz2 (desistindo)"
	    exit 1 	
	fi	
    fi  
else
    if [ ${backup} == "./$databasename.backup-dev.sql" ]; then
	echo -n     "Removendo banco......................................."
	if ( dropdb $databasename -U $databaseuser ); then
	    echoOk
	else
	    echoFalhou
	fi          #------------------------------------------------------------#
	echo -n     "Criando o banco novamente............................."
	if ( createdb $databasename -U $databaseuser ); then
	    echoOk  #---------------------------------------------------------------#
	    echo -n "Populando tabelas($databasename.backup-dev.sql)................"
	    if ( psql $databasename -U $databaseuser -q -f $databasename.backup-dev.sql -o reseta_banco.log); then
		echoOk
	    else
		echoFalhou
	    fi
	else
	    echoFalhou
	fi
    else
	echoFalhou
	echo "$databasename.backup-dev.sql não encontrado"	    
    fi    
fi
