# PLUG User Group Members Management setup

This document assumes that a separate LDAP tree (stored in its own mdb
format database) is to be added for the plug.org.au domain.  This will
most often be the case when testing on your own workstation, for
example.

## System package installation

This should already have been done, as the current file was installed
from the package.

Get a copy of `plug-ugmm_0.6.2_all.deb` or latest version.  Then, run this command:

    sudo dpkg -i plug-ugmm_0.6.2_all.deb

## Database setup

Warning: see assumption above.

    # Change into your working directory for the repo
    cd ... ; source=extras
    # Or if the package has been installed,
    source=/usr/share/doc/plug-ugmm
    
    # Prepare the new database directory (if changing this, modify $source/examples/plug.ldif)
    sudo install -o openldap -g openldap -d /var/lib/ldap_plug
    # Then, use the OnLine Config mechanism to add an entry in /etc/ldap/slapd.d/cn=config
    sudo ldapadd -H ldapi:// -Y EXTERNAL -f $source/examples/plug.ldif
    # Add the schema (converted from plugpen.schema)
    sudo ldapadd -H ldapi:// -Y EXTERNAL -f $source/examples/plugpen.schema.ldif
    # Now, populate the special entries in the database
    sudo ldapadd -H ldapi:// -Y EXTERNAL -f $source/examples/plug_db.ldif
    sudo ldapadd -H ldapi:// -Y EXTERNAL -f $source/examples/groups.ldif
    sudo ldapadd -H ldapi:// -Y EXTERNAL -f $source/examples/specialgroups.ldif
    # And change the admin password for this database, if desired
    sudo ldappasswd -H ldapi:// -Y EXTERNAL -S cn=admin,dc=plug,dc=org,dc=au
    # Make sure $source/examples/memberof.ldif references the correct database and modify if not
    sudo ls /etc/ldap/slapd.d/cn=config
    sudo less '/etc/ldap/slapd.d/cn=config/olcDatabase={2}mdb.ldif'
    # Activate the "memberof" module to support automatic member/memberOf attributes for groups
    sudo ldapadd -H ldapi:// -Y EXTERNAL -f $source/examples/memberof.ldif
    # Add a test user
    sudo ldapadd -H ldapi:// -Y EXTERNAL -f $source/examples/bob.ldif

Lastly, you must go through and manually add or import the active members to the
cn=currentmembers,ou=Groups,dc=plug,dc=org,dc=au group.

## System package configuration

Copy /usr/share/plug-ugmm/www/PLUG/ldapconnection.inc.php.example to
/etc/private/ldapconnection.inc.php and modify it to suit your LDAP
database.

## Web server configuration

These two sections assume that your desired web server software is
already installed.  They also assume that your DNS and firewall settings
are configured appropriately.

### Setting up UGMM to run under Apache

First, create/edit an Apache virtual host config file (with .conf
extension) in /etc/apache2/sites-available/ .  Fill out the virtual host
details and HTTPS settings if not done already.  Then, copy-paste a
snippet from /usr/share/doc/plug-ugmm/examples/apache/plug-ugmm.conf
(or /usr/share/doc/plug-ugmm/examples/apache/plug-ugmm.section.conf if
running under the /ugmm URL path instead of on a dedicated Apache
virtual host) into the virtual host file to activate UGMM at a given
path under your site's URL.

If creating a new virtual host file, run `sudo a2ensite <sitename>`
where "\<sitename\>" is the basename of your file without the extension.

Lastly, run these commands:

    sudo a2enmod rewrite
    sudo apachectl -t
    # Do not proceed if the previous command reported errors
    sudo service apache2 restart

### Setting up UGMM to run under Nginx

First, create/edit an Nginx virtual host config file (with .conf
extension) in /etc/nginx/sites-available/ .  Fill out the virtual host
details and HTTPS settings if not done already.  Then, copy-paste a
snippet from /usr/share/doc/plug-ugmm/examples/nginx/plug-ugmm.conf
(or /usr/share/doc/plug-ugmm/examples/nginx/plug-ugmm.section.conf if
running under the /ugmm URL path instead of on a dedicated Nginx virtual
host) into the virtual host file to activate UGMM at a given path under
your site's URL.

If creating a new virtual host file, run
**`sudo ln -s /etc/nginx/sites-available/\<conffile\> /etc/nginx/sites-enabled/`**
where "\<conffile\>" is the basename of your file.

If necessary, modify the `fastcgi_pass` statement to use the correct FPM
socket path.

Lastly, run these commands:

    sudo nginx -t
    # Do not proceed if the previous command reported errors
    sudo service nginx reload
