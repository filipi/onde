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

Become root user. You will be prompted your user's password.

    sudo su -

First install the data base server, on ubuntu 20.04 it can be like this

    apt install postgresql-12
    
PS: The minimum supported Ubuntu version by ONDE is 16.03. As such, the
minimum version of Postgresql should be 9.5. In that case, the command 
would look like this:

    apt install postgresql-9.5

Now, use the **``su``** command to become the postgres user.
The postgres user is usually setup without a password, that's
why we became root first.

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

You may use a .pg_pass on your home folder to store postgres passwords
(not much safe, but makes things easy on a developer environment).

    ~/.pg_pass
    localhost:5432:onde:change_this_password_on_prodution_site

To enable onde database user to authenticate from command line and from PHP postgres library,
you have to grant access at the postgres host based authentication configuration file.
Depending on your local postgres installation it may have different locations but is always named
pg_hba.conf .
In Ubuntu distributions it is at

   /etc/postgresql/12/main/pg_hba.conf

For easy of setup you can change the following line (change peer for trust)

    # "local" is for Unix domain socket connections only                                                                      
    #local   all             all                                     peer                                                     
    local   all             all                                     trust

Don't forget to restart the database server deamon, as root

      sudo service postgresql stop

      sudo service postgresql start

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


Installing webserver

Onde is compatible with Apache and Nginx

     apt install nginx

Edit /etc/nginx/sites-avaliable and set document root to [onde root]/web


