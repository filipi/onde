## Installing the framework

Apart from the fact that ONDE doens't look like a framework, it was
conceived to be used as one. So, you should have your application
database to use with the framework.

Inside the **``db``** folder, there is the skeleton database, with all
the tables required for the framework to operate, automaticaly
generating web forms for inclusion, update and deleting data
from your application tables. 

Also in the **``db``** folder there is a script to reset the framework database.
This script will check the database backup file integrity, unpack it,
drop the onde db current running, create it again empty and
populate it with the database backup file.

## Quick and dirt - Demo application

### Linux (Debian / Ubuntu)

Became root user. You will be prompted your user's password.

    sudo su -

## Installing PostgreSQL Database Server


    apt install apt install postgresql-12


Now, use the **``su``** command to became the postgres user.
The postgres user is usually setup without a password, that's
why we became root first

    su - postgres
    
Now, the postgres user can create a database user, which can 
create our **onde** database and populate it with our default data.

    createuser -s -P onde

This command line will create a database user. The "-s" switch
stands for super user and the "-P" switch means you will be prompted
to inform this user password.

The default **``include/conf.inc``** comes with a default password
(``change_this_password_on_prodution_site``), which you have to change.
The password you put in **``include/conf.inc``** is the same you've
informed to **``createuser``**

Now you can finish the postgres login and the root login.
You can go back to your login shell and run the developer
database backup recovery script (restauraDev). As this is the
first time you are running this command, the dropdb line will
issue a failure message. This is the correct output:

    youruser@yourhost:~/onde/db$ ./restauraDev.sh 
    8 processors detected.
    Verificando se ./onde.backup-dev.sql existe...........[FALHOU]
    Verificando se ./onde.backup-dev.sql.bz2 existe.......[  OK  ]
    Descompactando backup do schema:......................[  OK  ]
    Removendo banco.......................................dropdb: database removal failed: ERROR:  database "onde" does not exist
    [FALHOU]
    Criando o banco novamente.............................[  OK  ]
    Populando tabelas(onde.backup-dev.sql)................[  OK  ]
        
    youruser@yourhost:~/onde/db$ 

